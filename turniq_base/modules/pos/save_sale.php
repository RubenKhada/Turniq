<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
header('Content-Type: application/json');
if(!isLoggedIn()){echo json_encode(['success'=>false,'message'=>'No autorizado']);exit;}
$data=json_decode(file_get_contents('php://input'),true);
if(!$data||empty($data['items'])){echo json_encode(['success'=>false,'message'=>'Datos inválidos']);exit;}
$items=$data['items'];$total=floatval($data['total']);$method=$data['paymentMethod']??'cash';$client=trim($data['clientName'])?:'Público general';$uid=$_SESSION['user_id'];
$db=getDB();$db->begin_transaction();
try{
    $stmt=$db->prepare("INSERT INTO sales (user_id,client_name,subtotal,total,payment_method,payment_status) VALUES (?,?,?,?,?,'paid')");
    $stmt->bind_param("isdds",$uid,$client,$total,$total,$method);$stmt->execute();$saleId=$db->insert_id;
    foreach($items as $item){
        $qty=intval($item['qty']);$price=floatval($item['price']);$sub=$price*$qty;$itemId=$item['id'];$iname=$db->real_escape_string($item['name']);
        if(strpos($itemId,'prod_')===0){
            $pid=intval(str_replace('prod_','',$itemId));
            $p=$db->query("SELECT stock FROM products WHERE id=$pid FOR UPDATE")->fetch_assoc();
            if(!$p||$p['stock']<$qty)throw new Exception("Stock insuficiente: ".$item['name']);
            $s=$db->prepare("INSERT INTO sale_items(sale_id,product_id,item_name,quantity,unit_price,subtotal) VALUES(?,?,?,?,?,?)");
            $s->bind_param("iisidd",$saleId,$pid,$iname,$qty,$price,$sub);$s->execute();
            $db->query("UPDATE products SET stock=stock-$qty WHERE id=$pid");
        }elseif(strpos($itemId,'serv_')===0){
            $sid=intval(str_replace('serv_','',$itemId));
            $s=$db->prepare("INSERT INTO sale_items(sale_id,service_id,item_name,quantity,unit_price,subtotal) VALUES(?,?,?,?,?,?)");
            $s->bind_param("iisidd",$saleId,$sid,$iname,$qty,$price,$sub);$s->execute();
        }else{
            $s=$db->prepare("INSERT INTO sale_items(sale_id,item_name,quantity,unit_price,subtotal) VALUES(?,?,?,?,?)");
            $s->bind_param("isidd",$saleId,$iname,$qty,$price,$sub);$s->execute();
        }
    }
    $db->commit();echo json_encode(['success'=>true,'sale_id'=>$saleId]);
}catch(Exception $e){$db->rollback();echo json_encode(['success'=>false,'message'=>$e->getMessage()]);}
$db->close();