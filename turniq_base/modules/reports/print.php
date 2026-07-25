<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
$db=getDB();$desde=$_GET['desde']??date('Y-m-d');$hasta=$_GET['hasta']??date('Y-m-d');
$totales=$db->query("SELECT COUNT(*) as ventas,COALESCE(SUM(total),0) as ingresos,COALESCE(SUM(CASE WHEN payment_method='cash' THEN total ELSE 0 END),0) as cash,COALESCE(SUM(CASE WHEN payment_method='card' THEN total ELSE 0 END),0) as card,COALESCE(SUM(CASE WHEN payment_method='transfer' THEN total ELSE 0 END),0) as transfer FROM sales WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta'")->fetch_assoc();
$ventas=$db->query("SELECT id,client_name,total,payment_method,created_at FROM sales WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta' ORDER BY created_at DESC");
$db->close();
$methods=['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'];
$periodo=$desde===$hasta?date('d/m/Y',strtotime($desde)):date('d/m/Y',strtotime($desde)).' al '.date('d/m/Y',strtotime($hasta));
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Reporte <?= $periodo ?></title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Segoe UI',Arial,sans-serif;font-size:13px;color:#111;background:#fff;padding:32px;max-width:800px;margin:0 auto}.header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #111}.biz{font-size:20px;font-weight:700}.subtitle{font-size:14px;color:#666;margin-top:4px}.summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}.card{border:1px solid #e0e0e0;border-radius:8px;padding:12px}.card-label{font-size:11px;color:#666;text-transform:uppercase;letter-spacing:0.5px}.card-value{font-size:20px;font-weight:700;margin-top:4px}.sec-title{font-size:14px;font-weight:600;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #e0e0e0}table{width:100%;border-collapse:collapse;margin-bottom:24px}th{text-align:left;font-size:11px;color:#666;text-transform:uppercase;padding:8px;border-bottom:2px solid #111;background:#fafafa}td{padding:8px;font-size:12px;border-bottom:1px solid #e0e0e0}.footer{margin-top:32px;padding-top:12px;border-top:1px solid #e0e0e0;font-size:11px;color:#999;display:flex;justify-content:space-between}.print-btn{position:fixed;top:20px;right:20px;padding:10px 20px;background:#7B2FBE;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600}@media print{.print-btn{display:none}body{padding:16px}}</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨️ Imprimir / PDF</button>
<div class="header"><div><div class="biz"><?= htmlspecialchars(BUSINESS_NAME) ?></div><div class="subtitle">Reporte de ventas — <?= $periodo ?></div></div><div style="font-size:12px;color:#666;text-align:right">Generado el <?= date('d/m/Y H:i') ?><br>por <?= htmlspecialchars($_SESSION['user_name']) ?></div></div>
<div class="summary">
    <div class="card"><div class="card-label">Ventas</div><div class="card-value"><?= $totales['ventas'] ?></div></div>
    <div class="card"><div class="card-label">Ingresos</div><div class="card-value" style="color:#16A34A"><?= formatMoney($totales['ingresos']) ?></div></div>
    <div class="card"><div class="card-label">Efectivo</div><div class="card-value"><?= formatMoney($totales['cash']) ?></div></div>
    <div class="card"><div class="card-label">Tarjeta</div><div class="card-value"><?= formatMoney($totales['card']) ?></div></div>
</div>
<div class="sec-title">Detalle de ventas</div>
<table><thead><tr><th>#</th><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Método</th><th style="text-align:right">Total</th></tr></thead><tbody>
<?php while($v=$ventas->fetch_assoc()): ?><tr><td style="color:#999">#<?= str_pad($v['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= date('d/m/Y',strtotime($v['created_at'])) ?></td><td><?= date('H:i',strtotime($v['created_at'])) ?></td><td><?= htmlspecialchars($v['client_name']) ?></td><td><?= $methods[$v['payment_method']]??$v['payment_method'] ?></td><td style="text-align:right;font-weight:600"><?= formatMoney($v['total']) ?></td></tr><?php endwhile; ?>
</tbody></table>
<div class="footer"><span>Turniq v<?= APP_VERSION ?> — <?= htmlspecialchars(BUSINESS_NAME) ?></span><span><?= date('d/m/Y H:i') ?></span></div>
<script>window.onload=function(){window.print();}</script>
</body></html>