<?php
// ============================================
// CONEXIÓN A LA BASE DE DATOS (PDO)
// ============================================

// Iniciar sesión SOLO si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datos de conexión (XAMPP Local)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// Función de conexión
function conectar() {
    static $conexion = null;

    if ($conexion === null) {
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    return $conexion;
}