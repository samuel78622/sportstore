<?php
session_start();
require_once "includes/conexion.php";
require_once "includes/funciones_producto.php";

$conexion = conectar();

function obtenerVarianteActivaMinPrecio($producto_id, $db) {
    $stmt = $db->prepare("SELECT * FROM variantes WHERE producto_id = ? AND activo = 1 AND stock > 0 ORDER BY precio ASC LIMIT 1");
    $stmt->execute([$producto_id]);
    return $stmt->fetch();
}

// 🔐 Validar usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Validar carrito
if (empty($_SESSION['carrito'])) {
    echo "El carrito está vacío";
    exit();
}

// Procesar compra
if (isset($_POST['comprar'])) {

    $id_usuario = $_SESSION['usuario_id'];
    $total = 0;

    // Calcular total
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $prod = obtenerProducto($id);
        if (!$prod) {
            continue;
        }

        $precio = obtenerPrecioMinimoProducto($id) ?? 0;
        $total += $precio * $cantidad;
    }

    // Insertar orden
    $sql_orden = "INSERT INTO ordenes (usuario_id, subtotal, descuento, total, estado, direccion_envio)
                  VALUES (?, ?, ?, ?, 'pendiente', ?)";
    $stmt = $conexion->prepare($sql_orden);
    $stmt->execute([$id_usuario, $total, 0, $total, 'Sin dirección']);

    $id_orden = $conexion->lastInsertId();

    // Insertar detalle + actualizar stock
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $prod = obtenerProducto($id);
        if (!$prod) {
            continue;
        }

        $variante = obtenerVarianteActivaMinPrecio($id, $conexion);
        if (!$variante) {
            continue;
        }

        $precio = $variante['precio'];
        if ($cantidad > $variante['stock']) {
            echo 'No hay suficiente stock para ' . htmlspecialchars($prod['nombre']);
            exit();
        }

        $subtotal_item = $precio * $cantidad;

        // Guardar detalle de orden
        $sql_detalle = "INSERT INTO detalle_orden 
                        (orden_id, variante_id, cantidad, precio_unitario, subtotal)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql_detalle);
        $stmt->execute([$id_orden, $variante['id'], $cantidad, $precio, $subtotal_item]);

        // Descontar stock de la variante
        $nuevo_stock = $variante['stock'] - $cantidad;
        $sql_stock = "UPDATE variantes SET stock = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql_stock);
        $stmt->execute([$nuevo_stock, $variante['id']]);
    }

    // Vaciar carrito
    $_SESSION['carrito'] = [];

    // Redirigir
    header("Location: confirmacion.php?id_pedido=" . $id_orden);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
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

    <h1>💳 Finalizar Compra</h1>

    <h3>Resumen del pedido:</h3>

    <table border="1" width="100%">
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>

        <?php
        $total = 0;

        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            $prod = obtenerProducto($id);
            if (!$prod) {
                continue;
            }

            $precio = obtenerPrecioMinimoProducto($id) ?? 0;
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            ?>

            <tr>
                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                <td>$<?php echo number_format($precio, 0, ',', '.'); ?></td>
                <td><?php echo $cantidad; ?></td>
                <td>$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
            </tr>

        <?php } ?>

    </table>

    <h2>Total a pagar: $<?php echo $total; ?></h2>

    <form method="POST">
        <h3>Método de pago</h3>

        <select name="pago">
            <option value="contra_entrega">Pago contra entrega</option>
            <option value="tarjeta">Tarjeta (simulado)</option>
        </select>

        <br><br>

        <button type="submit" name="comprar">✅ Confirmar compra</button>
    </form>

    <br>

    <a href="carrito.php" class="btn btn-secondary">⬅ Volver al carrito</a>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>