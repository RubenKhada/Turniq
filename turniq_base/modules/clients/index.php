<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('clients');

$db  = getDB();
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name      = trim($_POST['name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $birthdate = $_POST['birthdate'] ?? null;
        $notes     = trim($_POST['notes'] ?? '');
        if ($name) {
            $stmt = $db->prepare("INSERT INTO clients (name, phone, email, birthdate, notes) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $phone, $email, $birthdate, $notes);
            $stmt->execute();
            $msg = '✅ Cliente registrado correctamente.';
            $msg_type = 'success';
        }
    }
    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $db->query("UPDATE clients SET is_active = 0 WHERE id = $id");
        $msg = 'Cliente eliminado.';
        $msg_type = 'success';
    }
}

$search  = trim($_GET['q'] ?? '');
$where   = "is_active = 1";
if ($search) {
    $s     = $db->real_escape_string($search);
    $where .= " AND (name LIKE '%$s%' OR phone LIKE '%$s%' OR email LIKE '%$s%')";
}

$clients = $db->query("
    SELECT c.*,
        (SELECT COUNT(*) FROM appointments WHERE client_id = c.id) as total_citas,
        (SELECT COUNT(*) FROM appointments WHERE client_id = c.id AND status = 'completed') as citas_completadas,
        (SELECT MAX(date) FROM appointments WHERE client_id = c.id AND status = 'completed') as ultima_visita
    FROM clients c WHERE $where ORDER BY name
");

$total = $db->query("SELECT COUNT(*) as t FROM clients WHERE is_active=1")->fetch_assoc();
$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <div class="section-header" style="margin-bottom:8px">
            <div>
                <h1 class="page-title">Clientes</h1>
                <p class="page-subtitle"><?= $total['t'] ?> clientes registrados</p>
            </div>
            <button class="btn-primary" onclick="document.getElementById('modalNuevo').classList.add('show')">+ Nuevo cliente</button>
        </div>

        <?php if ($msg): ?><div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div><?php endif; ?>

        <div class="section-card" style="padding:14px;margin-bottom:16px">
            <form method="GET" style="display:flex;gap:10px">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Buscar por nombre, teléfono o email..."
                       style="flex:1;padding:9px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none">
                <button type="submit" class="btn-primary">Buscar</button>
                <?php if($search): ?><a href="index.php" class="btn-secondary">Limpiar</a><?php endif; ?>
            </form>
        </div>

        <div class="section-card">
            <?php if ($clients->num_rows > 0): ?>
            <table class="data-table">
                <thead><tr><th>Cliente</th><th>Contacto</th><th>Visitas</th><th>Última visita</th><th>Notas</th><th></th></tr></thead>
                <tbody>
                    <?php while($c = $clients->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--text)"><?= htmlspecialchars($c['name']) ?></strong>
                            <?php if ($c['birthdate']): ?>
                            <div style="font-size:11px;color:var(--text-dim)">🎂 <?= date('d/m', strtotime($c['birthdate'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['phone']): ?><div>📱 <?= htmlspecialchars($c['phone']) ?></div><?php endif; ?>
                            <?php if ($c['email']): ?><div style="font-size:12px;color:var(--text-dim)">✉️ <?= htmlspecialchars($c['email']) ?></div><?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-purple"><?= $c['citas_completadas'] ?> visitas</span>
                        </td>
                        <td style="color:var(--text-muted)">
                            <?= $c['ultima_visita'] ? date('d/m/Y', strtotime($c['ultima_visita'])) : '—' ?>
                        </td>
                        <td style="color:var(--text-muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= htmlspecialchars($c['notes'] ?? '—') ?>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar este cliente?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <p>No se encontraron clientes.</p>
                <button onclick="document.getElementById('modalNuevo').classList.add('show')" class="btn-primary">Registrar cliente</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalNuevo">
    <div class="modal">
        <div class="modal-title">Nuevo cliente <button class="modal-close" onclick="document.getElementById('modalNuevo').classList.remove('show')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="name" placeholder="Ej: María García" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="phone" placeholder="555-0000">
                </div>
                <div class="form-group">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="birthdate">
                    <div class="form-hint">Para recordatorios de cumpleaños</div>
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="cliente@email.com">
            </div>
            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" rows="2" placeholder="Preferencias, alergias, observaciones..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalNuevo').classList.remove('show')">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar cliente</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>