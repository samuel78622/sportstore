<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_orden.php';
require_once '../../includes/funciones_factura.php';

soloAdmin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$orden = obtenerOrden($id);

if (!$orden) {
    header("Location: index.php?error=orden_no_encontrada");
    exit();
}

$items   = obtenerItemsOrden($id);
$success = '';
$error   = '';

// ── CAMBIAR ESTADO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_estado'])) {
    $resultado = cambiarEstadoOrden($id, $_POST['nuevo_estado']);

    if ($resultado['exito']) {
        $success = $resultado['mensaje'];
        $orden   = obtenerOrden($id); // Recargar orden actualizada
    } else {
        $error = $resultado['mensaje'];
    }
}

// Estados con íconos y colores
$estados_info = [
    'pendiente' => ['icon' => '🟡', 'label' => 'Pendiente',  'color' => '#856404'],
    'empacado'  => ['icon' => '🔵', 'label' => 'Empacado',   'color' => '#004085'],
    'enviado'   => ['icon' => '🚚', 'label' => 'Enviado',    'color' => '#155724'],
    'entregado' => ['icon' => '✅', 'label' => 'Entregado',  'color' => '#0c5460'],
    'cancelado' => ['icon' => '❌', 'label' => 'Cancelado',  'color' => '#721c24'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?= $orden['id'] ?> — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/pedidos/detalle.css" rel="stylesheet">
</head>
<body>

<!-- ════════════ SIDEBAR ════════════ -->
<div class="sidebar">
    <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>
    <nav class="sidebar-menu">
        <div class="menu-label">Principal</div>
        <a href="../index.php" class="menu-item">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <div class="menu-label">Catálogo</div>
        <a href="../productos/index.php" class="menu-item">
            <i class="fas fa-shirt"></i> Productos
        </a>
        <a href="../productos/crear.php" class="menu-item">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
        <div class="menu-label">Inventario</div>
        <a href="../inventario/index.php" class="menu-item">
            <i class="fas fa-boxes-stacked"></i> Stock General
        </a>
        <a href="../inventario/movimientos.php" class="menu-item">
            <i class="fas fa-arrows-up-down"></i> Movimientos
        </a>
        <a href="../inventario/alertas.php" class="menu-item">
            <i class="fas fa-triangle-exclamation"></i> Alertas
        </a>
        <div class="menu-label">Ventas</div>
        <a href="index.php" class="menu-item active">
            <i class="fas fa-bag-shopping"></i> Pedidos
        </a>
         <a href="facturas/index.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
        <a href="../cupones/index.php" class="menu-item">
            <i class="fas fa-tag"></i> Cupones
        </a>
        <div class="menu-label">Usuarios</div>
        <a href="../clientes/index.php" class="menu-item">
            <i class="fas fa-users"></i> Clientes
        </a>
        <div class="menu-label">Reportes</div>
        <a href="../reportes/ventas.php" class="menu-item">
            <i class="fas fa-chart-line"></i> Ventas
        </a>
        <a href="../reportes/productos.php" class="menu-item">
            <i class="fas fa-star"></i> Más vendidos
        </a>
        <a href="../reportes/inventario.php" class="menu-item">
            <i class="fas fa-warehouse"></i> Inventario
        </a>
    </nav>
    <div class="sidebar-footer">
        <span style="color:#666; font-size:12px">
            👤 <?= htmlspecialchars($_SESSION['nombre']) ?>
        </span><br><br>
        <a href="../../logout.php">
            <i class="fas fa-right-from-bracket"></i> Cerrar sesión
        </a>
    </div>
</div>

<!-- ════════════ CONTENIDO ════════════ -->
<div class="main-content">

    <!-- Encabezado -->
    <div class="page-header">
        <div>
            <h1><i class="fas fa-bag-shopping me-2"></i>Pedido #<?= $orden['id'] ?></h1>
            <small class="text-muted">
                <?= htmlspecialchars($orden['numero_factura'] ?? 'Sin factura') ?> —
                <?= date('d/m/Y H:i', strtotime($orden['fecha'])) ?>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge-estado estado-<?= $orden['estado'] ?>">
                <?= $estados_info[$orden['estado']]['icon'] ?>
                <?= $estados_info[$orden['estado']]['label'] ?>
            </span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                ← Volver
            </a>
        </div>
    </div>

    <!-- Alertas -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-circle-check me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Timeline de estado -->
    <div class="card-section">
        <h5><i class="fas fa-route me-2"></i>Estado del pedido</h5>
        <?php
        $orden_estados  = ['pendiente', 'empacado', 'enviado', 'entregado'];
        $estado_actual  = $orden['estado'];
        $indice_actual  = array_search($estado_actual, $orden_estados);
        ?>
        <div class="timeline">
            <?php foreach ($orden_estados as $i => $est): ?>
                <?php
                if ($estado_actual === 'cancelado') {
                    $clase = '';
                } elseif ($i < $indice_actual) {
                    $clase = 'completado';
                } elseif ($i === $indice_actual) {
                    $clase = 'activo';
                } else {
                    $clase = '';
                }
                ?>
                <div class="timeline-step">
                    <div class="timeline-dot <?= $clase ?>">
                        <?php
                        $iconos = ['fa-clock', 'fa-box', 'fa-truck', 'fa-circle-check'];
                        echo '<i class="fas ' . $iconos[$i] . '"></i>';
                        ?>
                    </div>
                    <span class="timeline-label <?= $clase ?>">
                        <?= ucfirst($est) ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <!-- Cancelado -->
            <div class="timeline-step">
                <div class="timeline-dot <?= $estado_actual === 'cancelado' ? 'cancelado' : '' ?>">
                    <i class="fas fa-xmark"></i>
                </div>
                <span class="timeline-label <?= $estado_actual === 'cancelado' ? 'activo' : '' ?>">
                    Cancelado
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Columna izquierda -->
        <div class="col-md-8">

            <!-- Productos del pedido -->
            <div class="card-section">
                <h5><i class="fas fa-shirt me-2"></i>Productos</h5>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Talla / Color</th>
                            <th>Cant.</th>
                            <th>Precio unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['imagen_principal']): ?>
                                    <img
                                        src="../../uploads/productos/<?= htmlspecialchars($item['imagen_principal']) ?>"
                                        class="producto-img"
                                        alt=""
                                    >
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#bbb">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($item['nombre']) ?></strong></td>
                            <td>
                                <small>
                                    <?= htmlspecialchars($item['talla']) ?> /
                                    <?= htmlspecialchars($item['color']) ?>
                                </small>
                            </td>
                            <td><strong><?= $item['cantidad'] ?></strong></td>
                            <td>$<?= number_format($item['precio_unitario'], 0, ',', '.') ?></td>
                            <td><strong>$<?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Totales -->
                <div class="totales-box mt-4">
                    <div class="totales-row">
                        <span>Subtotal</span>
                        <span>$<?= number_format($orden['subtotal'], 0, ',', '.') ?></span>
                    </div>
                    <?php if ($orden['descuento'] > 0): ?>
                    <div class="totales-row">
                        <span>Descuento</span>
                        <span class="descuento">- $<?= number_format($orden['descuento'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="totales-row">
                        <span>TOTAL</span>
                        <span>$<?= number_format($orden['total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Datos del cliente -->
            <div class="card-section">
                <h5><i class="fas fa-user me-2"></i>Datos del cliente</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <p>Nombre</p>
                        <h6><?= htmlspecialchars($orden['cliente']) ?></h6>
                    </div>
                    <div class="info-item">
                        <p>Email</p>
                        <h6><?= htmlspecialchars($orden['email']) ?></h6>
                    </div>
                    <div class="info-item">
                        <p>Dirección de envío</p>
                        <h6><?= htmlspecialchars($orden['direccion_envio'] ?? 'No especificada') ?></h6>
                    </div>
                    <div class="info-item">
                        <p>Fecha del pedido</p>
                        <h6><?= date('d/m/Y H:i', strtotime($orden['fecha'])) ?></h6>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna derecha -->
        <div class="col-md-4">

            <!-- Cambiar estado -->
            <div class="card-section">
                <h5><i class="fas fa-pen me-2"></i>Cambiar estado</h5>
                <form method="POST" action="">
                    <select name="nuevo_estado" class="form-select mb-2">
                        <?php foreach ($estados_info as $valor => $info): ?>
                            <option
                                value="<?= $valor ?>"
                                <?= $orden['estado'] === $valor ? 'selected' : '' ?>
                            >
                                <?= $info['icon'] ?> <?= $info['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-cambiar">
                        <i class="fas fa-save me-2"></i>ACTUALIZAR ESTADO
                    </button>
                </form>

                <a
                    href="../../cliente/descargar_factura.php?orden_id=<?= $orden['id'] ?>&view=1"
                    target="_blank"
                    class="btn-factura"
                >
                    <i class="fas fa-file-invoice"></i> Ver Factura
                </a>
            </div>

            <!-- Resumen de la orden -->
            <div class="card-section">
                <h5><i class="fas fa-info-circle me-2"></i>Resumen</h5>
                <div class="info-grid" style="grid-template-columns: 1fr;">
                    <div class="info-item">
                        <p>Número de factura</p>
                        <h6><?= htmlspecialchars($orden['numero_factura'] ?? 'Sin factura') ?></h6>
                    </div>
                    <div class="info-item">
                        <p>Total de productos</p>
                        <h6><?= count($items) ?> producto(s)</h6>
                    </div>
                    <div class="info-item">
                        <p>Total de la orden</p>
                        <h6 style="color:#e44d26; font-size:20px">
                            $<?= number_format($orden['total'], 0, ',', '.') ?>
                        </h6>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>