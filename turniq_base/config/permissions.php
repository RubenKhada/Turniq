<?php
/*
 * config/permissions.php — Sistema de permisos por rol
 *
 * Define qué módulos puede acceder cada rol
 * Se incluye automáticamente desde app.php
 *
 * Roles disponibles:
 * - admin        → acceso total
 * - cashier      → ventas, clientes, citas
 * - receptionist → citas y clientes
 * - viewer       → solo dashboard y reportes
 */
$GLOBALS['role_permissions'] = [
    'admin' => [
        'dashboard','appointments','clients','employees',
        'services','pos','payments','alerts',
        'reports','settings','users'
    ],
    'cashier' => [
        'dashboard','appointments','clients',
        'pos','payments','alerts','reports'
    ],
    'receptionist' => [
        'dashboard','appointments','clients','alerts'
    ],
    'viewer' => [
        'dashboard','reports'
    ],
];

/*
 * hasPermission() — Verifica si el usuario puede acceder a un módulo
 * @param string $module
 * @return bool
 */
function hasPermission($module) {
    $role  = $_SESSION['user_role'] ?? 'viewer';
    $perms = $GLOBALS['role_permissions'][$role] ?? [];
    return in_array($module, $perms);
}

/*
 * requirePermission() — Redirige si no tiene permiso
 * @param string $module
 */
function requirePermission($module) {
    if (!hasPermission($module)) {
        header('Location: ' . BASE_URL . '/dashboard.php?error=sin_permiso');
        exit;
    }
}