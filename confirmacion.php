<?php
session_start();

// 🔌 IMPORTANTE: incluir conexión y autenticación
require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/funciones_factura.php";

$conexion = conectar();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_pedido']) && !isset($_GET['orden'])) {
    echo "Pedido no válido";
    exit();
}

$id_pedido = $_GET['id_pedido'] ?? $_GET['orden'];
$id_usuario = $_SESSION['usuario_id'];

// 🔒 Verificar la orden
if (esAdmin()) {
    $sql = "SELECT * FROM ordenes WHERE id = ?";
    $res = $conexion->prepare($sql);
    $res->execute([$id_pedido]);
} else {
    $sql = "SELECT * FROM ordenes WHERE id = ? AND usuario_id = ?";
    $res = $conexion->prepare($sql);
    $res->execute([$id_pedido, $id_usuario]);
}

$pedido = $res->fetch();

if (!$pedido) {
    echo esAdmin() ? "Pedido no encontrado" : "No tienes acceso a este pedido";
    exit();
}

echo generarHTMLFactura($id_pedido);
exit();
