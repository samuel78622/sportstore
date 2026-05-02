<?php
require_once '../includes/auth.php';
require_once '../includes/funciones_reportes.php';
require_once '../includes/funciones_inventario.php';
require_once '../includes/funciones_orden.php';

soloAdmin();

$reporte = reporteGeneral();
$resumen = resumenInventario();
$stats = estadisticasDashboard();
$stock_bajo = obtenerStockBajo(5);
$ordenes = listarOrdenes('pendiente');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>

<body>

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>

        <nav class="sidebar-menu">
            <div class="menu-label">Principal</div>
            <a href="index.php" class="menu-item active">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="menu-label">Catálogo</div>
            <a href="productos/index.php" class="menu-item">
                <i class="fas fa-shirt"></i> Productos
            </a>
            <a href="productos/crear.php" class="menu-item">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>

            <div class="menu-label">Inventario</div>
            <a href="inventario/index.php" class="menu-item">
                <i class="fas fa-boxes-stacked"></i> Stock General
            </a>
            <a href="inventario/movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="inventario/alertas.php" class="menu-item">
                <i class="fas fa-triangle-exclamation"></i> Alertas
            </a>

            <div class="menu-label">Ventas</div>
            <a href="pedidos/index.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Pedidos
            </a>
            <a href="cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>

            <div class="menu-label">Usuarios</div>
            <a href="clientes/index.php" class="menu-item">
                <i class="fas fa-users"></i> Clientes
            </a>

            <div class="menu-label">Reportes</div>
            <a href="reportes/ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="reportes/productos.php" class="menu-item">
                <i class="fas fa-star"></i> Más vendidos
            </a>
            <a href="reportes/inventario.php" class="menu-item">
                <i class="fas fa-warehouse"></i> Inventario
            </a>
        </nav>

        <div class="sidebar-footer">
            <span style="color:#666; font-size:12px">
                👤 <?= htmlspecialchars($_SESSION['nombre']) ?>
            </span><br><br>
            <a href="../logout.php">
                <i class="fas fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <!-- ════════════ CONTENIDO ════════════ -->
    <div class="main-content">

        <!-- Encabezado -->
        <div class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p class="fecha"><?= date('l, d \d\e F \d\e Y') ?></p>
            </div>
            <a href="../index.php" class="btn btn-sm btn-outline-secondary" target="_blank">
                <i class="fas fa-store"></i> Ver tienda
            </a>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $stats['ordenes_hoy'] ?></h3>
                        <p>Órdenes hoy</p>
                        <?php if ($reporte['variacion'] >= 0): ?>
                            <div class="variacion" style="color:#27ae60">
                                ▲ <?= $reporte['variacion'] ?>% vs ayer
                            </div>
                        <?php else: ?>
                            <div class="variacion" style="color:#e44d26">
                                ▼ <?= abs($reporte['variacion']) ?>% vs ayer
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-icon icon-ventas">
                        <i class="fas fa-bag-shopping"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>$<?= number_format($stats['ingresos_mes'], 0, ',', '.') ?></h3>
                        <p>Ingresos del mes</p>
                    </div>
                    <div class="stat-icon icon-ingresos">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $stats['total_clientes'] ?></h3>
                        <p>Clientes registrados</p>
                    </div>
                    <div class="stat-icon icon-clientes">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $stats['stock_bajo'] ?></h3>
                        <p>Productos stock bajo</p>
                    </div>
                    <div class="stat-icon icon-stock">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Órdenes pendientes y stock bajo -->
        <div class="row g-4 mt-1">

            <!-- Órdenes pendientes -->
            <div class="col-md-7">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-clock text-warning me-2"></i>Órdenes Pendientes</h5>
                        <a href="pedidos/index.php">Ver todas →</a>
                    </div>
                    <?php if (empty($ordenes)): ?>
                        <p class="text-muted text-center py-3">No hay órdenes pendientes</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th># Orden</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($ordenes, 0, 5) as $orden): ?>
                                    <tr>
                                        <td><strong>#<?= $orden['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($orden['cliente']) ?></td>
                                        <td>$<?= number_format($orden['total'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge-estado estado-<?= $orden['estado'] ?>">
                                                <?= ucfirst($orden['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="pedidos/detalle.php?id=<?= $orden['id'] ?>"
                                                class="btn btn-sm btn-outline-dark">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stock bajo -->
            <div class="col-md-5">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-triangle-exclamation text-danger me-2"></i>Stock Crítico</h5>
                        <a href="inventario/alertas.php">Ver todos →</a>
                    </div>
                    <?php if (empty($stock_bajo)): ?>
                        <p class="text-muted text-center py-3">Todo el inventario está bien 👍</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Talla</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($stock_bajo, 0, 6) as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                                        <td><?= $item['talla'] ?> / <?= $item['color'] ?></td>
                                        <td>
                                            <span
                                                class="stock-badge <?= $item['stock'] == 0 ? 'stock-agotado' : 'stock-bajo' ?>">
                                                <?= $item['stock'] == 0 ? 'Agotado' : $item['stock'] . ' uds' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Resumen de inventario -->
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-warehouse me-2"></i>Resumen de Inventario</h5>
                        <a href="inventario/index.php">Ver detalle →</a>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-md-3">
                            <h4 class="fw-bold"><?= $resumen['total_productos'] ?></h4>
                            <small class="text-muted">Productos activos</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="fw-bold"><?= $resumen['total_variantes'] ?></h4>
                            <small class="text-muted">Variantes totales</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="fw-bold text-danger"><?= $resumen['agotados'] ?></h4>
                            <small class="text-muted">Variantes agotadas</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="fw-bold text-success">
                                $<?= number_format($resumen['valor_inventario'], 0, ',', '.') ?>
                            </h4>
                            <small class="text-muted">Valor del inventario</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>