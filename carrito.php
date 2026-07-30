<?php
session_start();

require_once "includes/conexion.php";
require_once "includes/funciones_producto.php";
require_once "includes/funciones_carrito.php";

$conexion = conectar();

// ============================================
// NORMALIZAR CARRITO
// ============================================
$_SESSION['carrito'] = obtenerCarrito();

// ============================================
// AGREGAR PRODUCTO
// ============================================
if (isset($_POST['agregar'])) {

    $cantidad = max(1, intval($_POST['cantidad']));
    $variante_id = intval($_POST['variante_id'] ?? 0);

    if ($variante_id > 0) {
        agregarAlCarrito($variante_id, $cantidad);
    } else {
        $producto_id = intval($_POST['id_producto']);

        $stmt = $conexion->prepare("
            SELECT id 
            FROM variantes
            WHERE producto_id = ?
            AND activo = 1
            AND stock > 0
            ORDER BY precio ASC
            LIMIT 1
        ");

        $stmt->execute([$producto_id]);

        $variante = $stmt->fetch();

        if ($variante) {
            agregarAlCarrito(intval($variante['id']), $cantidad);
        }
    }
}

// ============================================
// ELIMINAR PRODUCTO
// ============================================
if (isset($_GET['eliminar'])) {

    $id = intval($_GET['eliminar']);
    eliminarDelCarrito($id);
}

// ============================================
// VACIAR CARRITO
// ============================================
if (isset($_GET['vaciar'])) {
    vaciarCarrito();
}

// ============================================
// ACTUALIZAR CANTIDADES
// ============================================
if (isset($_POST['actualizar']) || isset($_POST['checkout'])) {

    foreach ($_POST['cantidades'] as $id => $cantidad) {

        actualizarCantidad(
            intval($id),
            intval($cantidad)
        );
    }

    if (isset($_POST['checkout'])) {

        header("Location: checkout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>SportStore - Carrito</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Consolidated Styles -->
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/global.css" rel="stylesheet">
    <link href="assets/css/public.css" rel="stylesheet">

</head>

<body>

    <?php require_once __DIR__ . "/includes/header_public.php"; ?>

    <!-- ============================================
    CONTENEDOR CARRITO
    ============================================= -->
    <div class="carrito-container">

        <!-- HEADER -->
        <div class="carrito-header">

            <h1>
                Carrito de Compras
            </h1>

            <a href="catalogo.php" class="btn-regreso">

                <i class="fas fa-arrow-left"></i>

                Seguir comprando
            </a>

        </div>

        <!-- ============================================
        SI HAY PRODUCTOS
        ============================================= -->
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

                        foreach ($_SESSION['carrito'] as $id => $item):

                            $id = (int) $id;

                            $cantidad = 0;
                            $nombre = '';
                            $precio = 0;

                            // ===============================
                            // FORMATO NUEVO
                            // ===============================
                            if (is_array($item) && isset($item['cantidad'])) {

                                $cantidad = intval($item['cantidad']);

                                $precio = floatval($item['precio'] ?? 0);

                                $nombre = $item['nombre'] ?? '';

                                // SI NO EXISTE NOMBRE
                                if (!$nombre) {

                                    $sql = "
                                        SELECT p.nombre
                                        FROM variantes v
                                        JOIN productos p
                                        ON v.producto_id = p.id
                                        WHERE v.id = ?
                                    ";

                                    $resultado = $conexion->prepare($sql);

                                    $resultado->execute([$id]);

                                    $producto = $resultado->fetch();

                                    $nombre = $producto['nombre'] ?? '';
                                }

                            } else {

                                // ===============================
                                // FORMATO ANTIGUO
                                // ===============================
                                $cantidad = intval($item);

                                $sql = "
                                    SELECT id, nombre
                                    FROM productos
                                    WHERE id = ?
                                ";

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

                                <!-- PRODUCTO -->
                                <td>
                                    <?= htmlspecialchars($nombre) ?>
                                </td>

                                <!-- PRECIO -->
                                <td>
                                    $<?= number_format($precio, 0, ',', '.') ?>
                                </td>

                                <!-- CANTIDAD -->
                                <td>

                                    <input type="number" name="cantidades[<?= $id ?>]" value="<?= $cantidad ?>" min="1"
                                        class="cantidad-input">

                                </td>

                                <!-- SUBTOTAL -->
                                <td>
                                    $<?= number_format($subtotal, 0, ',', '.') ?>
                                </td>

                                <!-- ELIMINAR -->
                                <td>

                                    <a href="carrito.php?eliminar=<?= $id ?>" class="btn-eliminar">

                                        Eliminar

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

                <!-- ACCIONES -->
                <div class="carrito-acciones">

                    <button type="submit" name="actualizar" class="btn-actualizar">

                        <i class="fas fa-sync"></i>

                        Actualizar carrito

                    </button>

                    <a href="carrito.php?vaciar=true" class="btn-vaciar" onclick="return confirm('¿Estás seguro?')">

                        <i class="fas fa-trash"></i>

                        Vaciar carrito

                    </a>

                </div>

                <!-- TOTAL -->
                <div class="carrito-total">

                    <h2>Total a pagar:</h2>

                    <div style="font-size: 28px; font-weight: 900; color: #e44d26;">

                        $<?= number_format($total, 0, ',', '.') ?>

                    </div>

                </div>

                <!-- CHECKOUT -->
                <div style="text-align: right; margin-bottom: 30px;">

                    <button type="submit" name="checkout" class="btn-checkout">

                        <i class="fas fa-credit-card"></i>

                        Finalizar Compra

                    </button>

                </div>

            </form>

            <!-- ============================================
        CARRITO VACÍO
        ============================================= -->
        <?php else: ?>

            <div class="carrito-vacio">

                <i class="fas fa-shopping-cart" style="font-size: 60px; color: #ccc; margin-bottom: 20px;">
                </i>

                <p>
                    Tu carrito está vacío
                </p>

                <a href="catalogo.php" class="btn btn-dark btn-lg">

                    Ir al catálogo

                </a>

            </div>

        <?php endif; ?>

    </div>

    <!-- ============================================
    FOOTER
    ============================================= -->
    <footer class="text-center mt-5 p-4">

        <p>

            &copy; <?= date("Y") ?>

            SportStore - Todos los derechos reservados

        </p>

    </footer>

    <!-- BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>