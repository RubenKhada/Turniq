<?php
/*
 * theme.php — CSS dinámico de colores
 * Lee el color de la BD y lo inyecta como variables CSS
 */
require_once __DIR__ . '/config/database.php';
header('Content-Type: text/css');
header('Cache-Control: no-cache, no-store, must-revalidate');

$db  = getDB();
$r   = $db->query("SELECT setting_val FROM settings WHERE setting_key = 'theme_color' LIMIT 1");
$row = $r ? $r->fetch_assoc() : null;
$primary = ($row && !empty($row['setting_val'])) ? $row['setting_val'] : '#7B2FBE';
$db->close();

list($r, $g, $b) = sscanf($primary, "#%02x%02x%02x");
$hover = sprintf("#%02x%02x%02x", min(255,$r+38), min(255,$g+38), min(255,$b+38));
$light = sprintf("#%02x%02x%02x", max(0,$r-40),  max(0,$g-60),  max(0,$b-20));

echo ":root {
    --primary:       {$primary};
    --primary-h:     {$hover};
    --primary-light: {$light};
}
.btn-primary                          { background: var(--primary); }
.btn-primary:hover                    { background: var(--primary-h); }
.nav-item.active, .nav-item:hover     { border-left-color: var(--primary); }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus            { border-color: var(--primary); box-shadow: 0 0 0 3px rgba({$r},{$g},{$b},0.15); }
.sidebar-logo-text span               { color: var(--primary); }
.topbar-user a                        { color: var(--primary); }
.topbar-user a:hover                  { background: var(--primary); }
";