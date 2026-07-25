<?php
/*
 * ui/layout/sidebar.php — Navegación principal
 * Muestra solo los módulos activos según licencia y rol
 */
$current     = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($pages) {
    global $current, $current_dir;
    if (is_array($pages)) {
        return in_array($current, $pages) || in_array($current_dir, $pages) ? 'active' : '';
    }
    return ($current === $pages || $current_dir === $pages) ? 'active' : '';
}
?>
<div class="sidebar">

    <a href="<?= BASE_URL ?>/dashboard.php" class="sidebar-logo">
        <svg viewBox="0 0 680 420" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(340,210)">
                <polygon points="0,-110 95,-55 95,55 0,110 -95,55 -95,-55" fill="#1A0A2E" stroke="#2E1060" stroke-width="1"/>
                <polygon points="0,-110 95,-55 0,-10 -95,-55" fill="#2D1260"/>
                <polygon points="95,-55 95,55 0,110 0,-10" fill="#130824"/>
                <polygon points="-95,-55 0,-10 0,110 -95,55" fill="#1F0D40"/>
                <polygon points="0,-70 60,-35 60,35 0,70 -60,35 -60,-35" fill="#1A0A2E"/>
                <polygon points="0,-70 60,-35 0,0 -60,-35" fill="#4A1A8C"/>
                <polygon points="60,-35 60,35 0,70 0,0" fill="#1E0840"/>
                <polygon points="-60,-35 0,0 0,70 -60,35" fill="#311060"/>
                <polygon points="0,-30 25,-15 25,15 0,30 -25,15 -25,-15" fill="#1A0A2E"/>
                <polygon points="0,-30 25,-15 0,0 -25,-15" fill="#9D4EDD"/>
                <polygon points="25,-15 25,15 0,30 0,0" fill="#3A1272"/>
                <polygon points="-25,-15 0,0 0,30 -25,15" fill="#5A1EA8"/>
                <line x1="0" y1="-10" x2="0" y2="18" stroke="#5B1EA8" stroke-width="0.6" stroke-dasharray="4,4" opacity="0.5"/>
                <line x1="70" y1="-35" x2="70" y2="-5" stroke="#2D0F55" stroke-width="0.6" stroke-dasharray="4,4" opacity="0.4"/>
                <line x1="-70" y1="-35" x2="-70" y2="-5" stroke="#3A1272" stroke-width="0.6" stroke-dasharray="4,4" opacity="0.4"/>
            </g>
        </svg>
        <div class="sidebar-logo-text">Turn<span>iq</span></div>
    </a>

    <nav class="sidebar-nav">

        <div class="nav-label">Principal</div>
        <a href="<?= BASE_URL ?>/dashboard.php" class="nav-item <?= isActive('dashboard.php') ?>">
            <span class="icon">📊</span> Dashboard
        </a>

        <?php if (hasPermission('appointments') && isModuleActive('appointments')): ?>
        <div class="nav-label">Agenda</div>
        <a href="<?= BASE_URL ?>/modules/appointments/index.php" class="nav-item <?= isActive('appointments') ?>">
            <span class="icon">📅</span> Citas y turnos
        </a>
        <?php endif; ?>

        <?php if (hasPermission('pos') && isModuleActive('pos')): ?>
        <div class="nav-label">Ventas</div>
        <a href="<?= BASE_URL ?>/modules/pos/index.php" class="nav-item <?= isActive('pos') ?>">
            <span class="icon">🛒</span> Punto de venta
        </a>
        <?php endif; ?>

        <?php if (hasPermission('payments') && isModuleActive('payments')): ?>
        <a href="<?= BASE_URL ?>/modules/payments/index.php" class="nav-item <?= isActive('payments') ?>">
            <span class="icon">💳</span> Pagos
        </a>
        <?php endif; ?>

        <?php if (hasPermission('clients') && isModuleActive('clients')): ?>
        <div class="nav-label">Clientes</div>
        <a href="<?= BASE_URL ?>/modules/clients/index.php" class="nav-item <?= isActive('clients') ?>">
            <span class="icon">👥</span> Clientes
        </a>
        <?php endif; ?>

        <?php if (hasPermission('reports') && isModuleActive('reports')): ?>
        <div class="nav-label">Reportes</div>
        <a href="<?= BASE_URL ?>/modules/reports/index.php" class="nav-item <?= isActive('reports') ?>">
            <span class="icon">📈</span> Reportes
        </a>
        <?php endif; ?>

        <?php if (hasPermission('alerts') && isModuleActive('alerts')): ?>
        <?php
        $db_sb = getDB();
        $uid_sb = $_SESSION['user_id'];
        $unread_sb = $db_sb->query("SELECT COUNT(*) as c FROM alerts WHERE (user_id=$uid_sb OR user_id IS NULL) AND is_read=0")->fetch_assoc();
        $db_sb->close();
        ?>
        <div class="nav-label">Notificaciones</div>
        <a href="<?= BASE_URL ?>/modules/alerts/index.php" class="nav-item <?= isActive('alerts') ?>">
            <span class="icon">🔔</span> Alertas
            <?php if ($unread_sb['c'] > 0): ?>
            <span class="nav-badge"><?= $unread_sb['c'] ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('settings')): ?>
        <div class="nav-label">Sistema</div>
        <a href="<?= BASE_URL ?>/modules/settings/index.php" class="nav-item <?= isActive('settings') ?>">
            <span class="icon">⚙️</span> Configuración
        </a>
        <a href="<?= BASE_URL ?>/modules/users/index.php" class="nav-item <?= isActive('users') ?>">
            <span class="icon">👤</span> Usuarios
        </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
        <?= htmlspecialchars(BUSINESS_NAME) ?> · v<?= APP_VERSION ?>
    </div>
</div>