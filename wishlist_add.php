<?php
require_once "includes/auth.php";
require_once "includes/conexion.php";
require_once "includes/funciones_carrito.php";

if (session_status() === PHP_SESSION_NONE) session_start();

if (!estaLogueado()) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$conexion = conectar();

$variante_id = null;

if (isset($_GET['variante_id'])) {
    $variante_id = intval($_GET['variante_id']);
} elseif (isset($_GET['producto_id'])) {
    $producto_id = intval($_GET['producto_id']);

    $stmt = $conexion->prepare("SELECT id FROM variantes WHERE producto_id = ? AND stock > 0 LIMIT 1");
    $stmt->execute([$producto_id]);
    $v = $stmt->fetch();

    if ($v) {
        $variante_id = $v['id'];
    } else {
        $stmt = $conexion->prepare("SELECT id FROM variantes WHERE producto_id = ? LIMIT 1");
        $stmt->execute([$producto_id]);
        $v = $stmt->fetch();
        if ($v) $variante_id = $v['id'];
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'catalogo.php';

if (!$variante_id) {
    header("Location: $referer");
    exit();
}

$res = agregarWishlist($usuario_id, $variante_id);

header("Location: $referer");
exit();
