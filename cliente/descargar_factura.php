<?php
// ============================================
// DESCARGAR FACTURA — CLIENTE
// ============================================

require_once '../includes/auth.php';
require_once '../includes/funciones_orden.php';
require_once '../includes/funciones_factura.php';

soloLogueados();

$usuario = usuarioActual();

// Obtener orden_id de la URL
if (!isset($_GET['orden_id'])) {
    header("Location: mis_pedidos.php?error=parametro_faltante");
    exit();
}

$orden_id = intval($_GET['orden_id']);

// Verificar que la orden le pertenezca al usuario
$db = conectar();
$stmt = $db->prepare("SELECT usuario_id FROM ordenes WHERE id = ?");
$stmt->execute([$orden_id]);
$orden = $stmt->fetch();

if (!$orden || $orden['usuario_id'] != $usuario['id']) {
    header("Location: mis_pedidos.php?error=acceso_denegado");
    exit();
}

// Generar y descargar PDF
generarPDFFactura($orden_id, true);
exit();
?>