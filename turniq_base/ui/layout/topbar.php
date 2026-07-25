<?php
/*
 * ui/layout/topbar.php — Barra superior
 * Muestra nombre del negocio, alertas y usuario activo
 */
$db_top   = getDB();
$uid_top  = $_SESSION['user_id'];
$unread   = $db_top->query("SELECT COUNT(*) as c FROM alerts WHERE (user_id = $uid_top OR user_id IS NULL) AND is_read = 0")->fetch_assoc();
$db_top->close();
$alert_count = $unread['c'] ?? 0;
?>
<div class="topbar">
    <span class="topbar-title"><?= htmlspecialchars(BUSINESS_NAME) ?></span>
    <div class="topbar-right">
        <!-- Campana de alertas -->
        <a href="<?= BASE_URL ?>/modules/alerts/index.php" class="topbar-alerts" title="Alertas">
            🔔
            <?php if ($alert_count > 0): ?>
            <span class="badge"><?= $alert_count > 9 ? '9+' : $alert_count ?></span>
            <?php endif; ?>
        </a>
        <div class="topbar-user">
            <span>👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="<?= BASE_URL ?>/logout.php">Salir</a>
        </div>
    </div>
</div>