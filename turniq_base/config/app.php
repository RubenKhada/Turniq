<?php
/*
 * config/app.php — Configuración central de Turniq
 *
 * Inicializa la sesión, define constantes globales,
 * carga los permisos y el sistema de licencias
 *
 * Se incluye en TODAS las páginas del sistema
 */
session_start();

define('APP_NAME',    'Turniq');
define('APP_VERSION', '0.1');
define('BASE_URL',    'http://localhost/turniq_base');

/*
 * getBusinessName() — Lee el nombre del negocio desde la BD
 * Usa la sesión como caché para evitar múltiples consultas
 * @return string
 */
function getBusinessName() {
    if (isset($_SESSION['business_name'])) {
        return $_SESSION['business_name'];
    }
    try {
        $conn = new mysqli('localhost', 'root', '', 'turniq_base');
        if ($conn->connect_error) return 'Mi Negocio';
        $conn->set_charset("utf8");
        $r   = $conn->query("SELECT setting_val FROM settings WHERE setting_key = 'business_name' LIMIT 1");
        $row = $r ? $r->fetch_assoc() : null;
        $conn->close();
        $name = ($row && !empty($row['setting_val'])) ? $row['setting_val'] : 'Mi Negocio';
        $_SESSION['business_name'] = $name;
        return $name;
    } catch (Exception $e) {
        return 'Mi Negocio';
    }
}

define('BUSINESS_NAME', getBusinessName());

// Carga el sistema de permisos por rol
require_once __DIR__ . '/permissions.php';

// Carga la configuración de licencia
// Usamos include en lugar de require_once para que siempre retorne el array
if (!isset($GLOBALS['license'])) {
    $GLOBALS['license'] = include __DIR__ . '/license.php';
}

/*
 * isModuleActive() — Verifica si un módulo está activo en la licencia
 * @param string $module
 * @return bool
 */
function isModuleActive($module) {
    return $GLOBALS['license']['modules'][$module] ?? false;
}

/*
 * isLoggedIn() — Verifica si hay sesión activa
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/*
 * requireLogin() — Redirige al login si no hay sesión
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/*
 * redirect() — Redirige a una ruta relativa
 * @param string $path
 */
function redirect($path) {
    header('Location: ' . BASE_URL . '/' . $path);
    exit;
}

/*
 * getCurrencySymbol() — Retorna el símbolo de moneda configurado
 * @return string
 */
function getCurrencySymbol() {
    return $_SESSION['currency_symbol'] ?? '$';
}

/*
 * formatMoney() — Formatea un número como moneda
 * @param float $amount
 * @return string
 */
function formatMoney($amount) {
    return getCurrencySymbol() . number_format($amount, 2);
}

/*
 * timeAgo() — Convierte una fecha a formato "hace X tiempo"
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'hace un momento';
    if ($diff < 3600)   return 'hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'hace ' . floor($diff/3600) . ' h';
    return 'hace ' . floor($diff/86400) . ' días';
}