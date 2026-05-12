<?php
session_start();

require_once "includes/auth.php";
require_once "includes/conexion.php";
require_once "includes/funciones_carrito.php";
require_once "includes/funciones_orden.php";
require_once "includes/funciones_producto.php";

// ============================================
// SOLO USUARIOS LOGUEADOS
// ============================================
soloLogueados();

$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario = usuarioActual();

// ============================================
// OBTENER CARRITO
// ============================================
$carrito = obtenerCarrito();

// Validar carrito vacío
if (empty($carrito) || !is_array($carrito)) {
    header("Location: carrito.php?error=carrito_vacio");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// ============================================
// PROCESAR COMPRA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $direccion_envio = trim($_POST['direccion_envio'] ?? '');

    if (empty($direccion_envio)) {

        $mensaje = 'Por favor ingresa una dirección de envío';
        $tipo_mensaje = 'danger';

    } else {

        // Completar compra
        $resultado = completarCompra($usuario_id, $direccion_envio);

        if (isset($resultado['exito']) && $resultado['exito']) {

            header("Location: confirmacion.php?id_pedido=" . $resultado['orden_id']);
            exit();

        } else {

            $mensaje = $resultado['mensaje'] ?? 'Error al procesar la compra';
            $tipo_mensaje = 'danger';

        }
    }
}

// ============================================
// CALCULAR SUBTOTAL MANUALMENTE
// ============================================
$subtotal = 0;

foreach ($carrito as $item) {

    // Validar que sea arreglo
    if (
        is_array($item) &&
        isset($item['precio']) &&
        isset($item['cantidad'])
    ) {

        $subtotal += $item['precio'] * $item['cantidad'];

    }
}

// ============================================
// CUPÓN
// ============================================
$descuento = $_SESSION['cupon']['descuento'] ?? 0;

// ============================================
// TOTAL
// ============================================
$total = $subtotal - $descuento;

if ($total < 0) {
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — SportStore</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
        }

        .checkout-container {
            max-width: 1100px;
            margin: 40px auto;
        }

        .resumen-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .item-carrito {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .item-carrito:last-child {
            border-bottom: none;
        }

        .totales {
            background: #111;
            color: #fff;
            padding: 25px;
            border-radius: 10px;
            position: sticky;
            top: 20px;
        }

        .fila-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .linea-separadora {
            border-bottom: 1px solid rgba(255,255,255,.3);
            margin: 15px 0;
        }

        textarea {
            resize: none;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <i class="fas fa-bolt"></i>
            <strong>SPORT</strong>
            <span style="color:#ff6b6b;">STORE</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">
                        <i class="fas fa-store"></i>
                        Catálogo
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="carrito.php">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito
                    </a>
                </li>

                <li class="nav-item">
                    <span class="nav-link text-light">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?>
                    </span>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="container checkout-container">

    <h1 class="mb-4">
        <i class="fas fa-credit-card"></i>
        Finalizar Compra
    </h1>

    <!-- MENSAJES -->
    <?php if (!empty($mensaje)): ?>

        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">

            <?= htmlspecialchars($mensaje) ?>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <div class="row">

        <!-- RESUMEN -->
        <div class="col-lg-8">

            <div class="resumen-card">

                <h3 class="mb-4">
                    <i class="fas fa-list"></i>
                    Resumen del Pedido
                </h3>

                <?php foreach ($carrito as $item): ?>

                    <?php if (is_array($item)): ?>

                        <div class="item-carrito">

                            <div>

                                <strong>
                                    <?= htmlspecialchars($item['nombre'] ?? 'Producto') ?>
                                </strong>

                                <br>

                                <small class="text-muted">

                                    Talla:
                                    <?= htmlspecialchars($item['talla'] ?? 'N/A') ?>

                                    |

                                    Color:
                                    <?= htmlspecialchars($item['color'] ?? 'N/A') ?>

                                </small>

                            </div>

                            <div class="text-end">

                                <div>
                                    Cantidad:
                                    <strong>
                                        <?= intval($item['cantidad'] ?? 0) ?>
                                    </strong>
                                </div>

                                <div>

                                    $
                                    <?= number_format(
                                        ($item['precio'] ?? 0) *
                                        ($item['cantidad'] ?? 0),
                                        2
                                    ) ?>

                                </div>

                            </div>

                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

            <!-- DIRECCIÓN -->
            <div class="resumen-card">

                <h3 class="mb-4">
                    <i class="fas fa-map-marker-alt"></i>
                    Dirección de Envío
                </h3>

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">
                            Dirección Completa
                        </label>

                        <textarea
                            name="direccion_envio"
                            class="form-control"
                            rows="4"
                            required
                            placeholder="Ej: Carrera 10 #20-30 Bogotá"></textarea>

                    </div>

                    <button type="submit"
                            class="btn btn-success btn-lg w-100">

                        <i class="fas fa-check-circle"></i>
                        Completar Compra

                    </button>

                </form>

            </div>

        </div>

        <!-- TOTALES -->
        <div class="col-lg-4">

            <div class="totales">

                <h3 class="mb-4">
                    <i class="fas fa-calculator"></i>
                    Totales
                </h3>

                <div class="fila-total">

                    <span>Subtotal:</span>

                    <span>
                        $<?= number_format($subtotal, 2) ?>
                    </span>

                </div>

                <?php if ($descuento > 0): ?>

                    <div class="fila-total text-success">

                        <span>Descuento:</span>

                        <span>
                            -$<?= number_format($descuento, 2) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <div class="linea-separadora"></div>

                <div class="fila-total">

                    <strong>TOTAL:</strong>

                    <strong style="color:#ffc107;">
                        $<?= number_format($total, 2) ?>
                    </strong>

                </div>

                <?php if (isset($_SESSION['cupon'])): ?>

                    <div class="mt-3 p-3 rounded"
                         style="background: rgba(255,255,255,.1);">

                        <small>

                            <i class="fas fa-ticket-alt"></i>

                            Cupón:
                            <strong>
                                <?= htmlspecialchars($_SESSION['cupon']['codigo']) ?>
                            </strong>

                        </small>

                    </div>

                <?php endif; ?>

            </div>

            <!-- VOLVER -->
            <a href="carrito.php"
               class="btn btn-dark w-100 mt-3">

                <i class="fas fa-arrow-left"></i>
                Volver al Carrito

            </a>

        </div>

    </div>

</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>