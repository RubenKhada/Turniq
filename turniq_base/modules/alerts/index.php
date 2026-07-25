<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('alerts');

$db  = getDB();
$uid = $_SESSION['user_id'];

if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $db->query("UPDATE alerts SET is_read=1 WHERE id=$id");
    header('Location: index.php');
    exit;
}

if (isset($_GET['mark_all'])) {
    $db->query("UPDATE alerts SET is_read=1 WHERE user_id=$uid OR user_id IS NULL");
    header('Location: index.php');
    exit;
}

$filter = $_GET['filter'] ?? 'unread';
$where  = $filter === 'all' ? "1=1" : "is_read=0";
$alerts = $db->query("SELECT * FROM alerts WHERE ($where) AND (user_id=$uid OR user_id IS NULL) ORDER BY created_at DESC");
$unread = $db->query("SELECT COUNT(*) as c FROM alerts WHERE is_read=0 AND (user_id=$uid OR user_id IS NULL)")->fetch_assoc();
$db->close();

$type_icons = [
    'appointment' => '📅',
    'low_stock'   => '📦',
    'payment'     => '💳',
    'system'      => '⚙️',
    'info'        => 'ℹ️',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <div class="section-header" style="margin-bottom:8px">
            <div>
                <h1 class="page-title">Alertas</h1>
                <p class="page-subtitle"><?= $unread['c'] ?> alertas sin leer</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="?filter=unread" class="btn-<?= $filter==='unread'?'primary':'secondary' ?>">Sin leer</a>
                <a href="?filter=all" class="btn-<?= $filter==='all'?'primary':'secondary' ?>">Todas</a>
                <?php if($unread['c']>0): ?>
                <a href="?mark_all=1" class="btn-secondary" onclick="return confirm('¿Marcar todas como leídas?')">✓ Marcar todas</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-card">
            <?php if ($alerts->num_rows > 0): ?>
            <div style="display:flex;flex-direction:column;gap:8px">
                <?php while($a = $alerts->fetch_assoc()):
                    $icon = $type_icons[$a['type']] ?? '🔔';
                    $bg   = $a['is_read'] ? 'var(--bg)' : 'var(--warning-bg)';
                    $border = $a['is_read'] ? 'var(--border)' : 'var(--warning-border)';
                ?>
                <div style="display:flex;gap:12px;padding:14px;background:<?= $bg ?>;border:1px solid <?= $border ?>;border-radius:var(--radius-md)">
                    <div style="font-size:22px;flex-shrink:0"><?= $icon ?></div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600;color:var(--text)"><?= htmlspecialchars($a['title']) ?></div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= htmlspecialchars($a['message']) ?></div>
                        <div style="font-size:11px;color:var(--text-dim);margin-top:5px"><?= timeAgo($a['created_at']) ?></div>
                    </div>
                    <?php if (!$a['is_read']): ?>
                    <a href="?mark_read=<?= $a['id'] ?>&filter=<?= $filter ?>" class="btn-secondary btn-sm" style="align-self:center;white-space:nowrap">✓ Leída</a>
                    <?php else: ?>
                    <span class="badge badge-gray" style="align-self:center">Leída</span>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">🔔</span>
                <p><?= $filter==='unread' ? 'No tienes alertas sin leer.' : 'No hay alertas registradas.' ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>