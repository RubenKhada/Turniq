<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
$db=getDB();$id=intval($_GET['id']??0);
$sale=$db->query("SELECT * FROM sales WHERE id=$id")->fetch_assoc();
if(!$sale){echo "Venta no encontrada";exit;}
$items=$db->query("SELECT si.*,COALESCE(p.name,sv.name,si.item_name,'Servicio') as nombre FROM sale_items si LEFT JOIN products p ON si.product_id=p.id LEFT JOIN services sv ON si.service_id=sv.id WHERE si.sale_id=$id");
$db->close();
$settings_db=getDB();
$phone=$settings_db->query("SELECT setting_val FROM settings WHERE setting_key='business_phone' LIMIT 1")->fetch_assoc();
$address=$settings_db->query("SELECT setting_val FROM settings WHERE setting_key='business_address' LIMIT 1")->fetch_assoc();
$settings_db->close();
$methods=['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?= str_pad($id,4,'0',STR_PAD_LEFT) ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Courier New',monospace;font-size:13px;color:#000;background:#fff;width:80mm;margin:0 auto;padding:8px}
        .center{text-align:center}.divider{border-top:1px dashed #000;margin:8px 0}.spacer{margin:4px 0}
        .biz{font-size:17px;font-weight:bold;text-align:center;margin-bottom:2px}
        table{width:100%;border-collapse:collapse}th{font-size:11px;text-align:left;border-bottom:1px solid #000;padding-bottom:4px}
        td{padding:3px 0;font-size:12px;vertical-align:top}.td-qty{width:30px;text-align:center}.td-price{width:65px;text-align:right}
        .total-row{display:flex;justify-content:space-between;font-size:12px;margin:2px 0}
        .total-final{display:flex;justify-content:space-between;font-size:16px;font-weight:bold;margin-top:6px;padding-top:6px;border-top:1px solid #000}
        .footer{text-align:center;font-size:11px;color:#555;margin-top:12px}
        @media print{.no-print{display:none}body{width:80mm}}
    </style>
</head>
<body>
    <div class="biz"><?= htmlspecialchars(BUSINESS_NAME) ?></div>
    <?php if($phone['setting_val']??''): ?><div class="center" style="font-size:11px">📱 <?= htmlspecialchars($phone['setting_val']) ?></div><?php endif; ?>
    <?php if($address['setting_val']??''): ?><div class="center" style="font-size:10px;color:#555"><?= htmlspecialchars($address['setting_val']) ?></div><?php endif; ?>
    <div class="divider"></div>
    <div class="spacer">Folio: #<?= str_pad($sale['id'],6,'0',STR_PAD_LEFT) ?></div>
    <div class="spacer">Fecha: <?= date('d/m/Y H:i',strtotime($sale['created_at'])) ?></div>
    <div class="spacer">Cliente: <?= htmlspecialchars($sale['client_name']) ?></div>
    <div class="divider"></div>
    <table>
        <thead><tr><th>Descripción</th><th class="td-qty">Qty</th><th class="td-price">Total</th></tr></thead>
        <tbody>
            <?php while($item=$items->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($item['nombre']) ?><br><span style="font-size:11px;color:#555">$<?= number_format($item['unit_price'],2) ?> c/u</span></td><td class="td-qty"><?= $item['quantity'] ?></td><td class="td-price">$<?= number_format($item['subtotal'],2) ?></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div class="divider"></div>
    <div class="total-row"><span>Subtotal</span><span>$<?= number_format($sale['subtotal'],2) ?></span></div>
    <?php if($sale['discount']>0): ?><div class="total-row"><span>Descuento</span><span>-$<?= number_format($sale['discount'],2) ?></span></div><?php endif; ?>
    <div class="total-final"><span>TOTAL</span><span>$<?= number_format($sale['total'],2) ?></span></div>
    <div class="total-row" style="margin-top:6px"><span>Forma de pago</span><span><?= $methods[$sale['payment_method']]??$sale['payment_method'] ?></span></div>
    <div class="divider"></div>
    <div class="footer"><p>¡Gracias por su preferencia!</p><p>Powered by Turniq v<?= APP_VERSION ?></p></div>
    <div class="no-print" style="margin-top:20px;text-align:center">
        <button onclick="window.print()" style="padding:10px 24px;background:var(--primary,#7B2FBE);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px">🖨️ Imprimir</button>
    </div>
    <script>window.onload=function(){window.print();}</script>
</body>
</html>