<?php
/*
 * config/license.php — Sistema de licencias de Turniq
 *
 * Define qué módulos están activos para esta instalación
 * El distribuidor configura este archivo al instalar el sistema
 *
 * Licencias disponibles:
 * - base     → módulos del núcleo
 * - starter  → base + inventario + reportes
 * - pro      → todo
 * - custom   → configuración manual
 */
return [
    'business_type' => 'general',
    'license_type'  => 'base',
    'licensed_to'   => 'Mi Negocio',
    'issued_at'     => '2025-01-01',

    /*
     * Módulos activos — true = disponible, false = bloqueado
     * El LicenseManager lee este array al iniciar el sistema
     */
    'modules' => [
        // Núcleo base — siempre activo
        'dashboard'    => true,
        'appointments' => true,
        'clients'      => true,
        'pos'          => true,
        'payments'     => true,
        'alerts'       => true,
        'reports'      => true,
        'settings'     => true,
        'users'        => true,

        // Módulos adicionales — se activan con licencia superior
        'employees'    => true,
        'services'     => true,
        'inventory'    => false,
        'suppliers'    => false,
        'expenses'     => false,
        'cashregister' => false,
        'credits'      => false,
    ]
];