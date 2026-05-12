<?php
session_start();
require_once "includes/conexion.php";
require_once "includes/funciones_producto.php";
require_once "includes/funciones_carrito.php";

$conexion = conectar();

// Normalizar carrito con el formato de funciones_carrito
$_SESSION['carrito'] = obtenerCarrito();

// Agregar producto al carrito
if (isset($_POST['agregar'])) {
    $producto_id = intval($_POST['id_producto']);
    $cantidad = max(1, intval($_POST['cantidad']));

    $stmt = $conexion->prepare(
        "SELECT id FROM variantes
         WHERE producto_id = ? AND activo = 1 AND stock > 0
         ORDER BY precio ASC
         LIMIT 1"
    );
    $stmt->execute([$producto_id]);
    $variante = $stmt->fetch();

    if ($variante) {
        agregarAlCarrito(intval($variante['id']), $cantidad);
    }
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    eliminarDelCarrito($id);
}

// Vaciar carrito
if (isset($_GET['vaciar'])) {
    vaciarCarrito();
}

// Actualizar cantidades
if (isset($_POST['actualizar'])) {
    foreach ($_POST['cantidades'] as $id => $cantidad) {
        actualizarCantidad(intval($id), intval($cantidad));
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SportStore - Inicio</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/carrito.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand" href="#">SPORT<span>STORE</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="catalogo.php" class="btn btn-outline-light">Catálogo</a>
            <a href="carrito.php" class="btn btn-outline-light"> Carrito</a>
            <?php if (isset($_SESSION['usuario'])): ?>
                <span class="text-white"> <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="carrito-container">
        <div class="carrito-header">
            <h1> Carrito de Compras</h1>
            <a href="index.php" class="btn-regreso">
                <i class="fas fa-arrow-left"></i> Seguir comprando
            </a>
        </div>

        <?php if (!empty($_SESSION['carrito'])): ?>

            <form method="POST">
                <table class="carrito-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;

                        foreach ($_SESSION['carrito'] as $id => $item) {
                            $id = (int) $id;
                            $cantidad = 0;
                            $nombre = '';
                            $precio = 0;

                            if (is_array($item) && isset($item['cantidad'])) {
                                $cantidad = intval($item['cantidad']);
                                $precio = floatval($item['precio'] ?? 0);
                                $nombre = $item['nombre'] ?? '';

                                if (!$nombre) {
                                    $sql = "SELECT p.nombre FROM variantes v JOIN productos p ON v.producto_id = p.id WHERE v.id = ?";
                                    $resultado = $conexion->prepare($sql);
                                    $resultado->execute([$id]);
                                    $producto = $resultado->fetch();
                                    $nombre = $producto['nombre'] ?? '';
                                }
                            } else {
                                $cantidad = intval($item);

                                $sql = "SELECT id, nombre FROM productos WHERE id = ?";
                                $resultado = $conexion->prepare($sql);
                                $resultado->execute([$id]);
                                $producto = $resultado->fetch();

                                if (!$producto) {
                                    continue;
                                }

                                $nombre = $producto['nombre'];
                                $precio = obtenerPrecioMinimoProducto($id) ?? 0;
                            }

                            if ($cantidad <= 0) {
                                continue;
                            }

                            $subtotal = $precio * $cantidad;
                            $total += $subtotal;
                            ?>

                            <tr>
                                <td><?php echo htmlspecialchars($nombre); ?></td>
                                <td>$<?php echo number_format($precio, 0, ',', '.'); ?></td>
                                <td>
                                    <input type="number" name="cantidades[<?php echo $id; ?>]" value="<?php echo $cantidad; ?>"
                                        min="1" class="cantidad-input">
                                </td>
                                <td>$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                <td>
                                    <a href="carrito.php?eliminar=<?php echo $id; ?>" class="btn-eliminar"> Eliminar</a>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>

                <div class="carrito-acciones">
                    <button type="submit" name="actualizar" class="btn-actualizar">
                        <i class="fas fa-sync"></i> Actualizar carrito
                    </button>
                    <a href="carrito.php?vaciar=true" class="btn-vaciar" onclick="return confirm('¿Estás seguro?')">
                        <i class="fas fa-trash"></i> Vaciar carrito
                    </a>
                </div>
            </form>
            </div>

            <div class="carrito-total">
                <h2>Total a pagar:</h2>
                <div style="font-size: 28px; font-weight: 900; color: #e44d26;">
                    $<?php echo number_format($total, 0, ',', '.'); ?>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 30px;">
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Finalizar Compra
                </a>
            </div>

        <?php else: ?>

            <div class="carrito-vacio">
                <i class="fas fa-shopping-cart" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
                <p>Tu carrito está vacío</p>
                <a href="catalogo.php" class="btn btn-main btn-lg">Ir al catálogo</a>
            </div>

        <?php endif; ?>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>