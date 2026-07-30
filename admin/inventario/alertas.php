<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_inventario.php';

soloAdminOInventario();

$stock_bajo = obtenerStockBajo(5);
$agotados = obtenerProductosAgotados();
$resumen = resumenInventario();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Stock — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/inventario/alertas.css" rel="stylesheet">
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
            <a href="index.php" class="menu-item">
                <i class="fas fa-boxes-stacked"></i> Stock General
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="alertas.php" class="menu-item active">
                <i class="fas fa-triangle-exclamation"></i> Alertas
            </a>

            <div class="menu-label">Ventas</div>
            <a href="../pedidos/index.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Pedidos
            </a>
            <a href="../cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>
              <a href="../facturas/index.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
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
                <h1><i class="fas fa-triangle-exclamation me-2"></i>Alertas de Stock</h1>
                <small class="text-muted">Productos que requieren atención inmediata</small>
            </div>
            <a href="movimientos.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> Reabastecer stock
            </a>
        </div>

        <!-- Banner de alerta si hay productos críticos -->
        <?php if (count($agotados) > 0 || count($stock_bajo) > 0): ?>
            <div class="banner-alerta">
                <i class="fas fa-triangle-exclamation"></i>
                <div>
                    <p>¡Atención! Tienes productos que requieren reabastecimiento urgente</p>
                    <small>
                        <?= count($agotados) ?> agotados —
                        <?= count($stock_bajo) ?> con stock bajo
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de resumen -->
        <div class="row g-4 mb-2">
            <div class="col-md-4">
                <div class="alert-card alert-card-danger">
                    <div>
                        <h3><?= count($agotados) ?></h3>
                        <p>Variantes agotadas</p>
                    </div>
                    <i class="fas fa-ban card-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert-card alert-card-warning">
                    <div>
                        <h3><?= count($stock_bajo) ?></h3>
                        <p>Con stock bajo (≤ 5 uds)</p>
                    </div>
                    <i class="fas fa-triangle-exclamation card-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert-card alert-card-success">
                    <div>
                        <h3><?= $resumen['total_variantes'] - count($agotados) - count($stock_bajo) ?></h3>
                        <p>Variantes en buen estado</p>
                    </div>
                    <i class="fas fa-circle-check card-icon"></i>
                </div>
            </div>
        </div>

        <!-- ── PRODUCTOS AGOTADOS ── -->
        <div class="card-section">
            <div class="card-section-header">
                <h5>
                    <i class="fas fa-ban text-danger me-2"></i>
                    Productos Agotados
                    <span class="badge bg-danger ms-2"><?= count($agotados) ?></span>
                </h5>
                <a href="movimientos.php" class="btn btn-sm btn-outline-danger">
                    Reabastecer →
                </a>
            </div>

            <?php if (empty($agotados)): ?>
                <div class="empty-state">
                    <i class="fas fa-circle-check" style="color:#27ae60"></i>
                    <p style="color:#27ae60">¡No hay productos agotados! 🎉</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agotados as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['imagen_principal']): ?>
                                        <img src="../../uploads/productos/<?= htmlspecialchars($item['imagen_principal']) ?>"
                                            class="producto-img" alt="">
                                    <?php else: ?>
                                        <div class="producto-img-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['nombre']) ?></strong></td>
                                <td>
                                    <span
                                        style="background:#f0f0f0; padding:3px 10px; border-radius:6px; font-weight:700; font-size:13px">
                                        <?= htmlspecialchars($item['talla']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['color']) ?></td>
                                <td>
                                    <span class="stock-badge stock-agotado">
                                        <i class="fas fa-ban me-1"></i>Agotado
                                    </span>
                                </td>
                                <td>
                                    <a href="movimientos.php" class="btn-reabastecer">
                                        <i class="fas fa-plus"></i> Reabastecer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ── STOCK BAJO ── -->
        <div class="card-section">
            <div class="card-section-header">
                <h5>
                    <i class="fas fa-triangle-exclamation text-warning me-2"></i>
                    Stock Bajo (≤ 5 unidades)
                    <span class="badge bg-warning text-dark ms-2"><?= count($stock_bajo) ?></span>
                </h5>
                <a href="movimientos.php" class="btn btn-sm btn-outline-warning">
                    Reabastecer →
                </a>
            </div>

            <?php if (empty($stock_bajo)): ?>
                <div class="empty-state">
                    <i class="fas fa-circle-check" style="color:#27ae60"></i>
                    <p style="color:#27ae60">¡Todo el stock está en buen nivel! 👍</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Stock restante</th>
                            <th>Nivel</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stock_bajo as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['imagen_principal']): ?>
                                        <img src="../../uploads/productos/<?= htmlspecialchars($item['imagen_principal']) ?>"
                                            class="producto-img" alt="">
                                    <?php else: ?>
                                        <div class="producto-img-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['nombre']) ?></strong></td>
                                <td>
                                    <span
                                        style="background:#f0f0f0; padding:3px 10px; border-radius:6px; font-weight:700; font-size:13px">
                                        <?= htmlspecialchars($item['talla']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['color']) ?></td>
                                <td>
                                    <span class="stock-badge stock-bajo">
                                        <?= $item['stock'] ?> uds
                                    </span>
                                </td>
                                <td>
                                    <!-- Barra de nivel de stock -->
                                    <?php
                                    $porcentaje = ($item['stock'] / 5) * 100;
                                    $clase_bar = $item['stock'] <= 2 ? 'fill-danger' : 'fill-warning';
                                    ?>
                                    <div class="stock-bar-container">
                                        <div class="stock-bar">
                                            <div class="stock-bar-fill <?= $clase_bar ?>" style="width:<?= $porcentaje ?>%">
                                            </div>
                                        </div>
                                        <small style="color:#888; font-size:11px">
                                            <?= $item['stock'] ?>/5
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <a href="movimientos.php" class="btn-reabastecer">
                                        <i class="fas fa-plus"></i> Reabastecer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>