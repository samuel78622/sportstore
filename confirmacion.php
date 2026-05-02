<?php
session_start();

// 🔌 IMPORTANTE: incluir conexión
require_once __DIR__ . "/includes/conexion.php";

// 🔥 CREAR LA CONEXIÓN (ANTES DE USARLA)
$conexion = conectar();
?>

<?php



// 🔐 Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    session_start();
    header("Location: login.php");
    exit();
}

// Validar ID pedido
if (!isset($_GET['id_pedido'])) {
    echo "Pedido no válido";
    exit();
}

$id_pedido = $_GET['id_pedido'];
$id_usuario = $_SESSION['usuario_id'];

// 🔒 Verificar que la orden pertenece al usuario
$sql = "SELECT * FROM ordenes WHERE id = ? AND usuario_id = ?";
$res = $conexion->prepare($sql);
$res->execute([$id_pedido, $id_usuario]);
$pedido = $res->fetch();

if (!$pedido) {
    echo "No tienes acceso a este pedido";
    exit();
}

// Obtener detalle de la orden
$sql_detalle = "SELECT d.*, p.nombre 
                FROM detalle_orden d
                JOIN variantes v ON d.variante_id = v.id
                JOIN productos p ON v.producto_id = p.id
                WHERE d.orden_id = ?";

$res_detalle = $conexion->prepare($sql_detalle);
$res_detalle->execute([$id_pedido]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Confirmación de compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand" href="index.php">SPORT<span>STORE</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="catalogo.php" class="btn btn-outline-light">Catálogo</a>
            <a href="carrito.php" class="btn btn-outline-light">🛒 Carrito</a>
            <?php if (isset($_SESSION['usuario'])): ?>
                <span class="text-white">👋 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">

        <h1>✅ Compra realizada con éxito</h1>

        <p><strong>Pedido #:</strong> <?php echo $pedido['id']; ?></p>
        <p><strong>Fecha:</strong> <?php echo $pedido['fecha']; ?></p>
        <p><strong>Estado:</strong> <?php echo $pedido['estado']; ?></p>

        <hr>

        <h3>📦 Productos comprados</h3>

        <table border="1" width="100%">
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>

            <?php
            while ($item = $res_detalle->fetch()) {
                $subtotal = $item['precio_unitario'] * $item['cantidad'];
                ?>

                <tr>
                    <td><?php echo $item['nombre']; ?></td>
                    <td>$<?php echo $item['precio_unitario']; ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td>$<?php echo $subtotal; ?></td>
                </tr>

            <?php } ?>

        </table>

        <h2>💲 Total pagado: $<?php echo $pedido['total']; ?></h2>

        <br>

        <a href="catalogo.php" class="btn btn-primary">🛍️ Seguir comprando</a>
        <br><br>
        <a href="cliente/mis_pedidos.php" class="btn btn-info">📦 Ver mis pedidos</a>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
</body>

</html>