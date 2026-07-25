<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('payments');

$db  = getDB();
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $appointment_id = intval($_POST['appointment_id']) ?: null;
    $amount  = floatval($_POST['amount']);
    $method  = $_POST['method'] ?? 'cash';
    $notes   = trim($_POST['notes'] ?? '');
    $uid     = $_SESSION['user_id'];

    if ($amount > 0) {
        $stmt = $db->prepare("INSERT INTO payments (appointment_id, amount, method, notes, created_by) VALUES (?,?,?,?,?)");
        $stmt->bind_param("idssi", $appointment_id, $amount, $method, $notes, $uid);
        $stmt->execute();
        if ($appointment_id) {
            $db->query("UPDATE appointments SET status='completed' WHERE id=$appointment_id");
        }
        $msg = '✅ Pago registrado correctamente.';
        $msg_type = 'success';
    }
}

$hoy   = date('Y-m-d');
$desde = $_GET['desde'] ?? $hoy;
$hasta = $_GET['hasta'] ?? $hoy;

$payments = $db->query("
    SELECT p.*, a.date as appt_date, a.time as appt_time,
           c.name as client_name, u.name as user_name
    FROM payments p
    LEFT JOIN appointments a ON p.appointment_id = a.id
    LEFT JOIN clients c ON a.client_id = c.id
    LEFT JOIN users u ON p.created_by = u.id
    WHERE DATE(p.created_at) BETWEEN '$desde' AND '$hasta'
    ORDER BY p.created_at DESC
");

$totales = $db->query("
    SELECT COALESCE(SUM(amount),0) as total,
           COALESCE(SUM(CASE WHEN method='cash'     THEN amount ELSE 0 END),0) as cash,
           COALESCE(SUM(CASE WHEN method='card'     THEN amount ELSE 0 END),0) as card,
           COALESCE(SUM(CASE WHEN method='transfer' THEN amount ELSE 0 END),0) as transfer
    FROM payments WHERE DATE(created_at) BETWEEN '$desde' AND '$hasta'
")->fetch_assoc();

// Citas completadas sin pago registrado
$pending_apts = $db->query("
    SELECT a.id, a.date, a.time, c.name as client_name, s.name as service_name, s.price
    FROM appointments a
    LEFT JOIN clients  c ON a.client_id  = c.id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE a.status = 'completed'
    AND a.id NOT IN (SELECT appointment_id FROM payments WHERE appointment_id IS NOT NULL)
    ORDER BY a.date DESC LIMIT 10
");

$db->close();
$methods = ['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <div class="section-header" style="margin-bottom:8px">
            <div>
                <h1 class="page-title">Pagos</h1>
                <p class="page-subtitle">Registro de cobros</p>
            </div>
            <button class="btn-primary" onclick="document.getElementById('modalNuevo').classList.add('show')">+ Registrar pago</button>
        </div>

        <?php if ($msg): ?><div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div><?php endif; ?>

        <!-- Tarjetas resumen -->
        <div class="cards-grid">
            <div class="card"><div class="card-icon" style="background:var(--primary-light)">💰</div><div><span class="card-label">Total cobrado</span><span class="card-value"><?= formatMoney($totales['total']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--success-bg)">💵</div><div><span class="card-label">Efectivo</span><span class="card-value" style="color:var(--success)"><?= formatMoney($totales['cash']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--info-bg)">💳</div><div><span class="card-label">Tarjeta</span><span class="card-value" style="color:var(--info)"><?= formatMoney($totales['card']) ?></span></div></div>
            <div class="card"><div class="card-icon" style="background:var(--warning-bg)">🏦</div><div><span class="card-label">Transferencia</span><span class="card-value" style="color:var(--warning)"><?= formatMoney($totales['transfer']) ?></span></div></div>
        </div>

        <!-- Filtro fechas -->
        <div class="section-card" style="padding:14px;margin-bottom:16px">
            <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <label style="font-size:13px;color:var(--text-muted)">Desde:</label>
                <input type="date" name="desde" value="<?= $desde ?>" style="padding:7px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none">
                <label style="font-size:13px;color:var(--text-muted)">Hasta:</label>
                <input type="date" name="hasta" value="<?= $hasta ?>" style="padding:7px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none">
                <button type="submit" class="btn-primary btn-sm">Filtrar</button>
                <a href="?desde=<?= date('Y-m-01') ?>&hasta=<?= $hoy ?>" class="btn-secondary btn-sm">Este mes</a>
                <a href="?desde=<?= $hoy ?>&hasta=<?= $hoy ?>" class="btn-secondary btn-sm">Hoy</a>
            </form>
        </div>

        <?php if ($pending_apts->num_rows > 0): ?>
        <div class="alert alert-warning">
            ⚠️ Hay <?= $pending_apts->num_rows ?> cita(s) completada(s) sin cobro registrado.
            <a href="#pendientes" style="color:var(--warning);text-decoration:underline;margin-left:8px">Ver →</a>
        </div>
        <?php endif; ?>

        <!-- Historial de pagos -->
        <div class="section-card">
            <div class="section-header"><h2>Historial de pagos</h2></div>
            <?php if ($payments->num_rows > 0): ?>
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Cliente</th><th>Servicio</th><th>Método</th><th>Monto</th><th>Registrado por</th></tr></thead>
                <tbody>
                    <?php while($p = $payments->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                        <td><?= htmlspecialchars($p['client_name'] ?? 'Directo') ?></td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($p['notes'] ?? '—') ?></td>
                        <td><span class="badge badge-purple"><?= $methods[$p['method']] ?></span></td>
                        <td style="color:var(--success);font-weight:600"><?= formatMoney($p['amount']) ?></td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($p['user_name']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state"><span class="empty-icon">💳</span><p>No hay pagos en este período.</p></div>
            <?php endif; ?>
        </div>

        <!-- Citas pendientes de cobro -->
        <?php if ($pending_apts->num_rows > 0): ?>
        <div class="section-card" id="pendientes">
            <div class="section-header"><h2>⚠️ Citas sin cobro</h2></div>
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Cliente</th><th>Servicio</th><th>Precio</th><th></th></tr></thead>
                <tbody>
                    <?php while($a = $pending_apts->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($a['date'])) ?> <?= date('H:i', strtotime($a['time'])) ?></td>
                        <td><?= htmlspecialchars($a['client_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($a['service_name'] ?? '—') ?></td>
                        <td style="color:var(--accent)"><?= $a['price'] ? formatMoney($a['price']) : '—' ?></td>
                        <td>
                            <button class="btn-primary btn-sm"
                                onclick="cobrarCita(<?= $a['id'] ?>, '<?= addslashes($a['client_name']) ?>', <?= $a['price'] ?? 0 ?>)">
                                💳 Cobrar
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<div class="modal-overlay" id="modalNuevo">
    <div class="modal">
        <div class="modal-title">Registrar pago <button class="modal-close" onclick="document.getElementById('modalNuevo').classList.remove('show')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="appointment_id" id="aptId">
            <div id="aptInfo" style="display:none;background:var(--info-bg);border:1px solid var(--info-border);border-radius:var(--radius-md);padding:10px;margin-bottom:14px;font-size:13px;color:var(--info)"></div>
            <div class="form-group">
                <label>Monto *</label>
                <input type="number" name="amount" id="payAmount" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Método de pago *</label>
                <select name="method">
                    <option value="cash">💵 Efectivo</option>
                    <option value="card">💳 Tarjeta</option>
                    <option value="transfer">🏦 Transferencia</option>
                </select>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" rows="2" placeholder="Concepto del pago..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalNuevo').classList.remove('show')">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar pago</button>
            </div>
        </form>
    </div>
</div>

<script>
function cobrarCita(id, cliente, precio) {
    document.getElementById('aptId').value      = id;
    document.getElementById('payAmount').value  = precio || '';
    const info = document.getElementById('aptInfo');
    info.style.display   = 'block';
    info.textContent     = '📅 Cita de: ' + cliente;
    document.getElementById('modalNuevo').classList.add('show');
}
</script>
</body>
</html>