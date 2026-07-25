<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('appointments');

$db  = getDB();
$msg = '';
$msg_type = '';

// Mes/año del calendario
$year  = intval($_GET['year']  ?? date('Y'));
$month = intval($_GET['month'] ?? date('m'));
$view  = $_GET['view'] ?? 'calendar'; // calendar | list

// Crear cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $client_id   = intval($_POST['client_id']);
    $employee_id = intval($_POST['employee_id']) ?: null;
    $service_id  = intval($_POST['service_id'])  ?: null;
    $date        = $_POST['date'] ?? '';
    $time        = $_POST['time'] ?? '';
    $notes       = trim($_POST['notes'] ?? '');
    $uid         = $_SESSION['user_id'];

    // Calcula end_time si hay servicio con duración
    $end_time = null;
    if ($service_id) {
        $svc = $db->query("SELECT duration_minutes FROM services WHERE id = $service_id")->fetch_assoc();
        if ($svc) {
            $end_time = date('H:i', strtotime($time) + $svc['duration_minutes'] * 60);
        }
    }

    if ($client_id && $date && $time) {
        $stmt = $db->prepare("INSERT INTO appointments (client_id, employee_id, service_id, date, time, end_time, notes, created_by) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iiiisssi", $client_id, $employee_id, $service_id, $date, $time, $end_time, $notes, $uid);
        $stmt->execute();

        // Genera alerta
        $client = $db->query("SELECT name FROM clients WHERE id = $client_id")->fetch_assoc();
        $msg_al = "Nueva cita: {$client['name']} el $date a las $time";
        $db->query("INSERT INTO alerts (type, title, message) VALUES ('appointment','Nueva cita','$msg_al')");

        $msg = '✅ Cita registrada correctamente.';
        $msg_type = 'success';
    } else {
        $msg = 'Cliente, fecha y hora son obligatorios.';
        $msg_type = 'error';
    }
}

// Cambiar estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    $id     = intval($_POST['id']);
    $status = $_POST['status'];
    $db->query("UPDATE appointments SET status = '$status' WHERE id = $id");
    $msg = '✅ Estado actualizado.';
    $msg_type = 'success';
}

// Datos para el formulario
$clients   = $db->query("SELECT id, name, phone FROM clients WHERE is_active=1 ORDER BY name");
$employees = $db->query("SELECT id, name, role FROM employees WHERE is_active=1 ORDER BY name");
$services  = $db->query("SELECT id, name, duration_minutes, price FROM services WHERE is_active=1 ORDER BY name");

// Citas del mes para el calendario
$first_day = "$year-$month-01";
$last_day  = date('Y-m-t', strtotime($first_day));
$citas_mes = $db->query("
    SELECT a.*, c.name as client_name, e.name as employee_name, s.name as service_name, s.color as service_color
    FROM appointments a
    LEFT JOIN clients   c ON a.client_id   = c.id
    LEFT JOIN employees e ON a.employee_id = e.id
    LEFT JOIN services  s ON a.service_id  = s.id
    WHERE a.date BETWEEN '$first_day' AND '$last_day'
    ORDER BY a.date, a.time
");

// Agrupa citas por fecha
$citas_by_date = [];
while ($c = $citas_mes->fetch_assoc()) {
    $citas_by_date[$c['date']][] = $c;
}

$db->close();

$status_labels = [
    'pending'   => ['Pendiente', 'badge-yellow'],
    'confirmed' => ['Confirmada','badge-blue'],
    'completed' => ['Completada','badge-green'],
    'cancelled' => ['Cancelada', 'badge-red'],
];

$month_names = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Navegación del calendario
$prev_month = $month - 1; $prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

// Día de la semana del primer día del mes (0=Dom)
$first_weekday = (int)date('w', strtotime($first_day));
$days_in_month = (int)date('t', strtotime($first_day));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
    <style>
        .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
        .cal-head { text-align:center; font-size:11px; color:var(--text-dim); padding:8px 4px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
        .cal-cell { background:var(--bg); border:1px solid var(--border-light); border-radius:var(--radius-md); padding:6px; min-height:90px; cursor:pointer; transition:border-color 0.15s; }
        .cal-cell:hover { border-color:var(--primary); }
        .cal-cell.today { border-color:var(--primary); background:var(--primary-light); }
        .cal-cell.other { opacity:0.3; }
        .cal-num { font-size:12px; color:var(--text-muted); font-weight:600; margin-bottom:4px; }
        .cal-cell.today .cal-num { color:var(--accent); }
        .cal-ev { font-size:10px; padding:2px 5px; border-radius:3px; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; }
    </style>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">

        <div class="section-header" style="margin-bottom:8px">
            <div>
                <h1 class="page-title">Citas y turnos</h1>
                <p class="page-subtitle"><?= $month_names[$month] ?> <?= $year ?></p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="?view=calendar&year=<?= $year ?>&month=<?= $month ?>" class="btn-<?= $view==='calendar'?'primary':'secondary' ?>">Calendario</a>
                <a href="?view=list&year=<?= $year ?>&month=<?= $month ?>" class="btn-<?= $view==='list'?'primary':'secondary' ?>">Lista</a>
                <button class="btn-primary" onclick="document.getElementById('modalNuevo').classList.add('show')">+ Nueva cita</button>
            </div>
        </div>

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
        <?php endif; ?>

        <!-- Navegación del mes -->
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <a href="?view=<?= $view ?>&year=<?= $prev_year ?>&month=<?= $prev_month ?>" class="btn-icon">‹</a>
            <span style="font-size:15px;font-weight:500;color:var(--text)"><?= $month_names[$month] ?> <?= $year ?></span>
            <a href="?view=<?= $view ?>&year=<?= $next_year ?>&month=<?= $next_month ?>" class="btn-icon">›</a>
            <a href="?view=<?= $view ?>&year=<?= date('Y') ?>&month=<?= date('m') ?>" class="btn-secondary btn-sm">Hoy</a>
        </div>

        <?php if ($view === 'calendar'): ?>
        <!-- Vista calendario -->
        <div class="section-card">
            <div class="cal-grid">
                <?php foreach(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $d): ?>
                <div class="cal-head"><?= $d ?></div>
                <?php endforeach; ?>

                <?php
                // Celdas vacías antes del primer día
                for ($i = 0; $i < $first_weekday; $i++):
                    $prev_days = $days_in_month - $first_weekday + $i + 1;
                    $prev_date = date('Y-m-', strtotime($first_day . ' -1 month')) . str_pad($prev_days,2,'0',STR_PAD_LEFT);
                ?>
                <div class="cal-cell other">
                    <div class="cal-num"><?= $prev_days ?></div>
                </div>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= $days_in_month; $day++):
                    $date_str  = "$year-" . str_pad($month,2,'0',STR_PAD_LEFT) . '-' . str_pad($day,2,'0',STR_PAD_LEFT);
                    $is_today  = $date_str === date('Y-m-d');
                    $day_citas = $citas_by_date[$date_str] ?? [];
                ?>
                <div class="cal-cell <?= $is_today ? 'today' : '' ?>"
                     onclick="window.location='?view=list&year=<?= $year ?>&month=<?= $month ?>&day=<?= $day ?>'">
                    <div class="cal-num"><?= $day ?></div>
                    <?php foreach(array_slice($day_citas,0,3) as $cita):
                        $bg = match($cita['status']) {
                            'completed' => 'var(--success-bg);color:var(--success)',
                            'cancelled' => 'var(--danger-bg);color:var(--danger)',
                            'pending'   => 'var(--warning-bg);color:var(--warning)',
                            default     => 'var(--primary-light);color:var(--accent)'
                        };
                    ?>
                    <div class="cal-ev" style="background:<?= $bg ?>">
                        <?= date('H:i',strtotime($cita['time'])) ?> <?= htmlspecialchars($cita['client_name'] ?? '') ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($day_citas) > 3): ?>
                    <div style="font-size:10px;color:var(--text-dim)">+<?= count($day_citas)-3 ?> más</div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Vista lista -->
        <?php
        $day_filter = intval($_GET['day'] ?? 0);
        $db2 = getDB();
        $where_list = "a.date BETWEEN '$first_day' AND '$last_day'";
        if ($day_filter) {
            $date_filter = "$year-".str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day_filter,2,'0',STR_PAD_LEFT);
            $where_list  = "a.date = '$date_filter'";
        }
        $lista = $db2->query("
            SELECT a.*, c.name as client_name, c.phone as client_phone,
                   e.name as employee_name, s.name as service_name, s.price as service_price
            FROM appointments a
            LEFT JOIN clients   c ON a.client_id   = c.id
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN services  s ON a.service_id  = s.id
            WHERE $where_list
            ORDER BY a.date, a.time
        ");
        $db2->close();
        ?>
        <div class="section-card">
            <div class="section-header"><h2>Lista de citas</h2></div>
            <?php if ($lista->num_rows > 0): ?>
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Empleado</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php while($a = $lista->fetch_assoc()):
                        $st = $status_labels[$a['status']] ?? ['?','badge-gray'];
                    ?>
                    <tr>
                        <td><?= date('d/m/Y',strtotime($a['date'])) ?></td>
                        <td style="color:var(--accent);font-weight:500"><?= date('H:i',strtotime($a['time'])) ?></td>
                        <td>
                            <div style="font-weight:500;color:var(--text)"><?= htmlspecialchars($a['client_name'] ?? '—') ?></div>
                            <?php if ($a['client_phone']): ?>
                            <div style="font-size:11px;color:var(--text-dim)"><?= htmlspecialchars($a['client_phone']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($a['service_name'] ?? '—') ?>
                            <?php if ($a['service_price']): ?>
                            <div style="font-size:11px;color:var(--accent)"><?= formatMoney($a['service_price']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($a['employee_name'] ?? '—') ?></td>
                        <td><span class="badge <?= $st[1] ?>"><?= $st[0] ?></span></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <?php if ($a['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn-primary btn-sm">✓ Completar</button>
                                </form>
                                <form method="POST" style="display:inline" onsubmit="return confirm('¿Cancelar esta cita?')">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn-danger btn-sm">✕</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📅</span>
                <p>No hay citas en este período.</p>
                <button onclick="document.getElementById('modalNuevo').classList.add('show')" class="btn-primary">+ Nueva cita</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal nueva cita -->
<div class="modal-overlay" id="modalNuevo">
    <div class="modal" style="max-width:540px">
        <div class="modal-title">
            Nueva cita
            <button class="modal-close" onclick="document.getElementById('modalNuevo').classList.remove('show')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-row">
                <div class="form-group">
                    <label>Cliente *</label>
                    <select name="client_id" required>
                        <option value="">Seleccionar...</option>
                        <?php $clients->data_seek(0); while($c = $clients->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Empleado</label>
                    <select name="employee_id">
                        <option value="">Sin asignar</option>
                        <?php $employees->data_seek(0); while($e = $employees->fetch_assoc()): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Servicio</label>
                <select name="service_id" onchange="updateDuration(this)">
                    <option value="">Sin especificar</option>
                    <?php $services->data_seek(0); while($s = $services->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>" data-duration="<?= $s['duration_minutes'] ?>">
                        <?= htmlspecialchars($s['name']) ?> — <?= formatMoney($s['price']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Hora *</label>
                    <input type="time" name="time" value="09:00" required>
                </div>
            </div>
            <div class="form-group" id="durationInfo" style="display:none">
                <div style="background:var(--info-bg);border:1px solid var(--info-border);border-radius:var(--radius-md);padding:10px;font-size:12px;color:var(--info)">
                    ⏱️ Duración estimada: <span id="durationText"></span>
                </div>
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" rows="2" placeholder="Observaciones..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalNuevo').classList.remove('show')">Cancelar</button>
                <button type="submit" class="btn-primary">Agendar cita</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateDuration(sel) {
    const opt = sel.options[sel.selectedIndex];
    const dur = parseInt(opt.dataset.duration) || 0;
    const info = document.getElementById('durationInfo');
    if (dur > 0) {
        info.style.display = 'block';
        document.getElementById('durationText').textContent = dur >= 60
            ? Math.floor(dur/60) + 'h ' + (dur%60 ? dur%60+'min' : '')
            : dur + ' minutos';
    } else {
        info.style.display = 'none';
    }
}
</script>
</body>
</html>