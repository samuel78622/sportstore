<?php
session_start();

// Vaciar variables de sesión
$_SESSION = [];

// Destruir cookie de sesión (importante para seguridad)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir sesión
session_destroy();

// Redirigir al login
header("Location: login.php");
exit();