<?php
/*
 * config/database.php — Conexión a la base de datos
 *
 * Define las credenciales y la función getDB()
 * que retorna una conexión activa a MySQL
 *
 * IMPORTANTE: Cambia DB_NAME al nombre de tu base de datos
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'turniq_base');

/*
 * getDB() — Retorna una conexión MySQLi activa
 * Termina la ejecución si no puede conectar
 * @return mysqli
 */
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode([
            'error' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }
    $conn->set_charset("utf8");
    return $conn;
}