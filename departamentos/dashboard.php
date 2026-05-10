<?php
// ============================================
// REDIRECCIONAR SEGÚN ROL DEL USUARIO
// ============================================

require_once '../includes/auth.php';

soloLogueados();

$rol = obtenerRol();

// Redirigir según el rol
switch ($rol) {
    case 'admin':
        header("Location: ../admin/index.php");
        break;
    case 'contador':
        header("Location: contador/index.php");
        break;
    case 'vendedor':
        header("Location: vendedor/index.php");
        break;
    case 'inventario':
        header("Location: inventario/index.php");
        break;
    case 'cliente':
        header("Location: ../index.php");
        break;
    default:
        header("Location: ../index.php");
}
exit();
?>
