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

// Verificar que la orden exista
$db = conectar();
$stmt = $db->prepare("SELECT usuario_id FROM ordenes WHERE id = ?");
$stmt->execute([$orden_id]);
$orden = $stmt->fetch();

if (!$orden) {
    header("Location: mis_pedidos.php?error=pedido_no_encontrado");
    exit();
}

// Si no es administrador, debe ser el dueño del pedido
if (!esAdmin() && $orden['usuario_id'] != $usuario['id']) {
    header("Location: mis_pedidos.php?error=acceso_denegado");
    exit();
}

// Mostrar factura en navegador si se solicita
if (isset($_GET['view']) && $_GET['view'] === '1') {
    echo generarHTMLFactura($orden_id);
    exit();
}

// Generar y descargar PDF
$resultado = generarPDFFactura($orden_id, true);

if (is_array($resultado) && isset($resultado['exito']) && !$resultado['exito']) {
    echo '<h1>Error al generar factura</h1>';
    echo '<p>' . htmlspecialchars($resultado['mensaje']) . '</p>';
    exit();
}

exit();
?>