<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('reports');

$db    = getDB();
$hoy   = date('Y-m-d');
$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? $hoy;

$totales = $db->query("
    SELECT COUNT(*) as ventas, COALESCE(SUM(total),0) as ingresos,
    COALESCE(SUM(CASE WHEN payment_method='cash'     THEN total ELSE 0 END),0) as cash,
    COALESCE(SUM(CASE WHEN payment_method='card'     THEN total ELSE 0 END),0) as card,
    COALESCE(SUM(CASE WHEN payment_method='transfer' THEN total ELSE 0 END),0) as transfer
    FROM sales WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta'
")->fetch_assoc();

$citas = $db->query("
    SELECT COUNT(*) as total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completadas,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as canceladas
    FROM appointments WHERE date BETWEEN '$desde' AND '$hasta'
")->fetch_assoc();

$top_services = $db->query("
    SELECT si.item_name as name, SUM(si.quantity) as qty, SUM(si.subtotal) as monto
    FROM sale_items si JOIN sales s ON si.sale_id=s.id
    WHERE DATE(s.created_at) BETWEEN '$desde' AND '$hasta'
    GROUP BY si.item_name ORDER BY monto DESC LIMIT 5
");

$ventas_list = $db->query("
    SELECT id,client_name,total,payment_method,created_at
    FROM sales WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta'
    ORDER BY created_at DESC
");

$db->close();
$methods=['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <h1 class="page-title">Reportes</h1>
        <p class="page-subtitle">Resumen del período seleccionado</p>

        <!-- Filtros -->
        <div class="section-card" style="padding:14px;margin-bottom:16px">
            <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <label style="font-size:13px;color:var(--text-muted)">Desde:</label>
                <input type="date" name="desde" value="<?= $desde ?>" style="padding:7px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none">
                <label style="font-size:13px;color:var(--text-muted)">Hasta:</label>
                <input type="date" name="hasta" value="<?= $hasta ?>" style="padding:7px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none">
                <button type="submit" class="btn-primary btn-sm">Filtrar</button>
                <a href="?desde=<?= date('Y-m-01') ?>&hasta=<?= $hoy ?>" class="btn-secondary btn-sm">Este mes</a>
                <a href="?desde=<?= $hoy ?>&hasta=<?= $hoy ?>" class="btn-secondary btn-sm">Hoy</a>
                <a href="print.php?desde=<?= $desde ?>&hasta=<?= $hasta ?>" target="_blank" class="btn-secondary btn-sm">🖨️ Imprimir</a>
            </form>
        </div>

        <!-- Tarjetas -->
        <div class="cards-grid">
            <div class="card"><div class="card-icon" style="background:var(--primary-light)">💰</div><div><span class="card-label">Ingresos</span><span class="card-value"><?= formatMoney($totales['ingresos']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--info-bg)">🧾</div><div><span class="card-label">Ventas</span><span class="card-value"><?= $totales['ventas'] ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--success-bg)">💵</div><div><span class="card-label">Efectivo</span><span class="card-value" style="color:var(--success)"><?= formatMoney($totales['cash']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--info-bg)">💳</div><div><span class="card-label">Tarjeta</span><span class="card-value" style="color:var(--info)"><?= formatMoney($totales['card']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--primary-light)">📅</div><div><span class="card-label">Citas totales</span><span class="card-value"><?= $citas['total'] ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--success-bg)">✅</div><div><span class="card-label">Completadas</span><span class="card-value" style="color:var(--success)"><?= $citas['completadas'] ?></span></div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <!-- Top servicios/productos -->
            <div class="section-card" style="margin:0">
                <div class="section-header"><h2>Top ventas</h2></div>
                <?php if ($top_services->num_rows > 0): ?>
                <table class="data-table">
                    <thead><tr><th>#</th><th>Producto/Servicio</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php $i=1; while($p=$top_services->fetch_assoc()): ?>
                        <tr>
                            <td style="color:var(--text-dim)"><?= $i++ ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $p['qty'] ?></td>
                            <td style="color:var(--accent);font-weight:600"><?= formatMoney($p['monto']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?><div class="empty-state" style="padding:20px"><p>Sin datos en este período.</p></div><?php endif; ?>
            </div>

            <!-- Desglose por método -->
            <div class="section-card" style="margin:0">
                <div class="section-header"><h2>Por método de pago</h2></div>
                <?php foreach([['💵 Efectivo',$totales['cash'],'var(--success)'],['💳 Tarjeta',$totales['card'],'var(--info)'],['🏦 Transferencia',$totales['transfer'],'var(--warning)']] as [$label,$val,$color]):
                    $pct=$totales['ingresos']>0?round($val/$totales['ingresos']*100,1):0;
                ?>
                <div style="margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                        <span style="font-size:13px;color:var(--text-muted)"><?= $label ?></span>
                        <span style="font-size:13px;font-weight:600;color:<?= $color ?>"><?= formatMoney($val) ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detalle -->
        <div class="section-card">
            <div class="section-header"><h2>Detalle de ventas</h2></div>
            <?php if ($ventas_list->num_rows > 0): ?>
            <table class="data-table">
                <thead><tr><th>#</th><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Método</th><th>Total</th></tr></thead>
                <tbody>
                    <?php while($v=$ventas_list->fetch_assoc()): ?>
                    <tr>
                        <td style="color:var(--text-dim)">#<?= str_pad($v['id'],4,'0',STR_PAD_LEFT) ?></td>
                        <td><?= date('d/m/Y',strtotime($v['created_at'])) ?></td>
                        <td><?= date('H:i',strtotime($v['created_at'])) ?></td>
                        <td><?= htmlspecialchars($v['client_name']) ?></td>
                        <td><span class="badge badge-purple"><?= $methods[$v['payment_method']]??$v['payment_method'] ?></span></td>
                        <td style="font-weight:600;color:var(--success)"><?= formatMoney($v['total']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty-state"><span class="empty-icon">📈</span><p>Sin ventas en este período.</p></div><?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>