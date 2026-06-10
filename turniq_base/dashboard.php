<?php
require_once 'config/app.php';
require_once 'config/database.php';
requireLogin();

$db  = getDB();
$hoy = date('Y-m-d');
$uid = $_SESSION['user_id'];

// Citas de hoy
$citas_hoy = $db->query("
    SELECT COUNT(*) as total,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completadas,
    SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as canceladas
    FROM appointments WHERE date = '$hoy'
")->fetch_assoc();

// Ventas del día
$ventas_hoy = $db->query("
    SELECT COUNT(*) as total, COALESCE(SUM(total),0) as ingresos
    FROM sales WHERE DATE(created_at) = '$hoy'
")->fetch_assoc();

// Clientes nuevos este mes
$clientes_nuevos = $db->query("
    SELECT COUNT(*) as total FROM clients
    WHERE DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')
")->fetch_assoc();

// Alertas sin leer
$alertas = $db->query("
    SELECT COUNT(*) as total FROM alerts
    WHERE (user_id = $uid OR user_id IS NULL) AND is_read = 0
")->fetch_assoc();

// Próximas citas del día
$proximas = $db->query("
    SELECT a.*, c.name as client_name, e.name as employee_name, s.name as service_name
    FROM appointments a
    LEFT JOIN clients  c ON a.client_id   = c.id
    LEFT JOIN employees e ON a.employee_id = e.id
    LEFT JOIN services  s ON a.service_id  = s.id
    WHERE a.date = '$hoy' AND a.status != 'cancelled'
    ORDER BY a.time ASC LIMIT 8
");

// Últimas 5 ventas
$ultimas_ventas = $db->query("
    SELECT id, client_name, total, payment_method, created_at
    FROM sales WHERE DATE(created_at) = '$hoy'
    ORDER BY created_at DESC LIMIT 5
");

// Alertas recientes
$alertas_recientes = $db->query("
    SELECT * FROM alerts
    WHERE (user_id = $uid OR user_id IS NULL) AND is_read = 0
    ORDER BY created_at DESC LIMIT 4
");

$db->close();

$status_labels = [
    'pending'   => ['Pendiente', 'badge-yellow'],
    'confirmed' => ['Confirmada','badge-blue'],
    'completed' => ['Completada','badge-green'],
    'cancelled' => ['Cancelada', 'badge-red'],
];
$pay_labels = ['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — <?= BUSINESS_NAME ?></title>
    <?php include 'ui/layout/head.php'; ?>
</head>
<body>
<?php include 'ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include 'ui/layout/topbar.php'; ?>
    <div class="page-body">

        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle"><?= date('l d \d\e F \d\e Y') ?></p>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_permiso'): ?>
        <div class="alert alert-warning">⚠️ No tienes permiso para acceder a ese módulo.</div>
        <?php endif; ?>

        <!-- Tarjetas de métricas -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon" style="background:var(--primary-light)">📅</div>
                <div>
                    <span class="card-label">Citas hoy</span>
                    <span class="card-value"><?= $citas_hoy['total'] ?></span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon" style="background:var(--success-bg)">✅</div>
                <div>
                    <span class="card-label">Completadas</span>
                    <span class="card-value" style="color:var(--success)"><?= $citas_hoy['completadas'] ?></span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon" style="background:var(--success-bg)">💰</div>
                <div>
                    <span class="card-label">Ingresos hoy</span>
                    <span class="card-value"><?= formatMoney($ventas_hoy['ingresos']) ?></span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon" style="background:var(--info-bg)">🧾</div>
                <div>
                    <span class="card-label">Ventas hoy</span>
                    <span class="card-value"><?= $ventas_hoy['total'] ?></span>
                </div>
            </div>
            <div class="card">
                <div class="card-icon" style="background:var(--primary-light)">👥</div>
                <div>
                    <span class="card-label">Clientes nuevos</span>
                    <span class="card-value"><?= $clientes_nuevos['total'] ?></span>
                </div>
            </div>
            <?php if ($alertas['total'] > 0): ?>
            <div class="card" style="border-color:var(--warning)">
                <div class="card-icon" style="background:var(--warning-bg)">🔔</div>
                <div>
                    <span class="card-label">Alertas</span>
                    <span class="card-value" style="color:var(--warning)"><?= $alertas['total'] ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            <!-- Citas del día -->
            <div class="section-card">
                <div class="section-header">
                    <h2>📅 Citas de hoy</h2>
                    <a href="modules/appointments/index.php" class="btn-primary btn-sm">+ Nueva</a>
                </div>
                <?php if ($proximas->num_rows > 0): ?>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <?php while($a = $proximas->fetch_assoc()):
                        $st = $status_labels[$a['status']] ?? ['?','badge-gray'];
                    ?>
                    <div style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-md)">
                        <div style="font-size:20px;font-weight:300;color:var(--accent);min-width:48px;text-align:center">
                            <?= date('H:i', strtotime($a['time'])) ?>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:500;color:var(--text)"><?= htmlspecialchars($a['client_name'] ?? '—') ?></div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                <?= htmlspecialchars($a['service_name'] ?? 'Sin servicio') ?>
                                <?php if ($a['employee_name']): ?> · <?= htmlspecialchars($a['employee_name']) ?><?php endif; ?>
                            </div>
                        </div>
                        <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span class="empty-icon">📅</span>
                    <p>No hay citas para hoy.</p>
                    <a href="modules/appointments/index.php" class="btn-primary btn-sm">Agendar cita</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel derecho -->
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- Últimas ventas -->
                <div class="section-card" style="margin:0">
                    <div class="section-header">
                        <h2>💰 Últimas ventas</h2>
                        <a href="modules/pos/index.php" class="btn-primary btn-sm">+ Venta</a>
                    </div>
                    <?php if ($ultimas_ventas->num_rows > 0): ?>
                    <table class="data-table">
                        <thead><tr><th>Cliente</th><th>Método</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php while($v = $ultimas_ventas->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['client_name']) ?></td>
                                <td><span class="badge badge-purple"><?= $pay_labels[$v['payment_method']] ?? $v['payment_method'] ?></span></td>
                                <td style="color:var(--success);font-weight:600"><?= formatMoney($v['total']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state" style="padding:20px">
                        <p>Sin ventas hoy.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Alertas recientes -->
                <?php if ($alertas_recientes->num_rows > 0): ?>
                <div class="section-card" style="margin:0">
                    <div class="section-header">
                        <h2>🔔 Alertas</h2>
                        <a href="modules/alerts/index.php" class="btn-secondary btn-sm">Ver todas</a>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php while($al = $alertas_recientes->fetch_assoc()): ?>
                        <div style="padding:10px;background:var(--warning-bg);border:1px solid var(--warning-border);border-radius:var(--radius-md)">
                            <div style="font-size:13px;font-weight:500;color:var(--warning)"><?= htmlspecialchars($al['title']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= htmlspecialchars($al['message']) ?></div>
                            <div style="font-size:11px;color:var(--text-dim);margin-top:4px"><?= timeAgo($al['created_at']) ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="section-card" style="margin-top:20px">
            <div class="section-header"><h2>⚡ Accesos rápidos</h2></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <?php if(isModuleActive('appointments')): ?>
                <a href="modules/appointments/index.php" class="btn-primary">📅 Nueva cita</a>
                <?php endif; ?>
                <?php if(isModuleActive('pos')): ?>
                <a href="modules/pos/index.php" class="btn-secondary">🛒 Punto de venta</a>
                <?php endif; ?>
                <?php if(isModuleActive('clients')): ?>
                <a href="modules/clients/index.php" class="btn-secondary">👥 Clientes</a>
                <?php endif; ?>
                <?php if(isModuleActive('payments')): ?>
                <a href="modules/payments/index.php" class="btn-secondary">💳 Pagos</a>
                <?php endif; ?>
                <?php if(isModuleActive('reports')): ?>
                <a href="modules/reports/index.php" class="btn-secondary">📈 Reportes</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</body>
</html>