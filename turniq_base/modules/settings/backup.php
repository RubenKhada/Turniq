<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
if($_SESSION['user_role']!=='admin')die('No autorizado');
$db=getDB();
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="turniq_backup_'.date('Y-m-d_H-i-s').'.sql"');
header('Pragma: no-cache');
echo "-- TURNIQ BASE — Backup ".date('Y-m-d H:i:s')."\n-- Negocio: ".BUSINESS_NAME."\nSET FOREIGN_KEY_CHECKS=0;\n\n";
$tables=$db->query("SHOW TABLES");
while($t=$tables->fetch_row()){$tbl=$t[0];echo "DROP TABLE IF EXISTS `$tbl`;\n";$cr=$db->query("SHOW CREATE TABLE `$tbl`")->fetch_row();echo $cr[1].";\n\n";$rows=$db->query("SELECT * FROM `$tbl`");while($r=$rows->fetch_row()){$vals=array_map(fn($v)=>$v===null?'NULL':"'".$db->real_escape_string($v)."'",$r);echo "INSERT INTO `$tbl` VALUES(".implode(',',$vals).");\n";}echo "\n";}
echo "SET FOREIGN_KEY_CHECKS=1;\n";
$db->close();