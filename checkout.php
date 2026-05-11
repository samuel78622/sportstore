<?php
session_start();
require_once "includes/auth.php";
require_once "includes/conexion.php";
require_once "includes/funciones_carrito.php";
require_once "includes/funciones_orden.php";
require_once "includes/funciones_producto.php";

// ✅ Solo usuarios logueados pueden comprar
soloLogueados();

$usuario_id = $_SESSION['usuario_id'];
$carrito = obtenerCarrito();

// Validar que el carrito no esté vacío
if (empty($carrito)) {
    header("Location: carrito.php?error=carrito_vacio");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion_envio = trim($_POST['direccion_envio'] ?? '');
    
    if (empty($direccion_envio)) {
        $mensaje = 'Por favor ingresa una dirección de envío';
        $tipo_mensaje = 'danger';
    } else {
        // Usar función centralizada que incluye generación de factura
        $resultado = completarCompra($usuario_id, $direccion_envio);
        
        if ($resultado['exito']) {
            header("Location: confirmacion.php?id_pedido=" . $resultado['orden_id']);
            exit();
        } else {
            $mensaje = $resultado['mensaje'];
            $tipo_mensaje = 'danger';
        }
    }
}

// Recalcular valores en caso de actualización
$subtotal = calcularSubtotal();
$descuento = $_SESSION['cupon']['descuento'] ?? 0;
$total = calcularTotal();
$usuario = usuarioActual();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-container {
            max-width: 900px;
            margin: 50px auto;
        }
        .resumen-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .item-carrito {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .item-carrito:last-child {
            border-bottom: none;
        }
        .totales {
            background: #111;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .fila-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .linea-separadora {
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            margin: 10px 0;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-bolt"></i> <strong>SPORT</strong><span style="color: #ff6b6b;">STORE</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">
                        <i class="fas fa-store"></i> Catálogo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="carrito.php">
                        <i class="fas fa-shopping-cart"></i> Carrito
                    </a>
                </li>
                <li class="nav-item">
                    <span class="nav-link text-light">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenido -->
<div class="checkout-container">
    <h1 class="mb-4">
        <i class="fas fa-credit-card"></i> Finalizar Compra
    </h1>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Resumen del Carrito -->
        <div class="col-lg-8">
            <div class="resumen-card">
                <h2 class="mb-3">
                    <i class="fas fa-list"></i> Resumen del Pedido
                </h2>
                
                <?php foreach ($carrito as $item): ?>
                    <div class="item-carrito">
                        <div>
                            <strong><?= htmlspecialchars($item['nombre']) ?></strong><br>
                            <small class="text-muted">
                                Talla: <?= htmlspecialchars($item['talla']) ?> | 
                                Color: <?= htmlspecialchars($item['color']) ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <div>Cantidad: <strong><?= $item['cantidad'] ?></strong></div>
                            <div>$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Formulario de Dirección -->
            <div class="resumen-card">
                <h3 class="mb-3">
                    <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                </h3>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="direccion_envio" class="form-label">Dirección Completa *</label>
                        <textarea 
                            id="direccion_envio"
                            name="direccion_envio"
                            class="form-control"
                            rows="3"
                            placeholder="Ej: Carrera 5 #123-456, Apartamento 302, Bogotá, Cundinamarca"
                            required
                        ></textarea>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check-circle"></i> Completar Compra
                    </button>
                </form>
            </div>
        </div>

        <!-- Resumen de Totales -->
        <div class="col-lg-4">
            <div class="totales">
                <h3 class="mb-3">
                    <i class="fas fa-calculator"></i> Totales
                </h3>

                <div class="fila-total">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <?php if ($descuento > 0): ?>
                    <div class="fila-total" style="color: #28a745;">
                        <span>Descuento (Cupón):</span>
                        <span>-$<?= number_format($descuento, 2) ?></span>
                    </div>
                <?php endif; ?>

                <div class="linea-separadora"></div>

                <div class="fila-total">
                    <span>TOTAL:</span>
                    <span style="color: #ffc107;">$<?= number_format($total, 2) ?></span>
                </div>

                <?php if (isset($_SESSION['cupon'])): ?>
                    <div class="mt-3 p-2" style="background: rgba(255, 255, 255, 0.1); border-radius: 5px;">
                        <small>
                            <i class="fas fa-ticket-alt"></i>
                            Cupón: <strong><?= htmlspecialchars($_SESSION['cupon']['codigo']) ?></strong>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botón de Regreso -->
            <a href="carrito.php" class="btn btn-outline-light w-100">
                <i class="fas fa-arrow-left"></i> Volver al Carrito
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
