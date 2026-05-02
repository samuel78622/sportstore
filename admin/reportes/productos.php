<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_reportes.php';

soloAdmin();

$mas_vendidos = reporteProductosMasVendidos(10);
$por_categoria = ventasPorCategoria();
$tallas = tallasMasVendidas();
$clientes = clientesMasActivos(5);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos más vendidos — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/reportes/productos.css">
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
            <a href="../pedidos/index.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Pedidos
            </a>
            <a href="../cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>
            <div class="menu-label">Usuarios</div>
            <a href="../clientes/index.php" class="menu-item">
                <i class="fas fa-users"></i> Clientes
            </a>
            <div class="menu-label">Reportes</div>
            <a href="ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="productos.php" class="menu-item active">
                <i class="fas fa-star"></i> Más vendidos
            </a>
            <a href="inventario.php" class="menu-item">
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

        <div class="page-header">
            <div>
                <h1><i class="fas fa-star me-2"></i>Productos más vendidos</h1>
                <small class="text-muted">Análisis de rendimiento de productos</small>
            </div>
        </div>

        <div class="row g-4">

            <!-- Top 10 productos -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-trophy me-2"></i>Top 10 Productos</h5>
                    </div>

                    <?php if (empty($mas_vendidos)): ?>
                        <p class="text-muted text-center py-4">No hay ventas registradas aún</p>
                    <?php else: ?>
                        <?php foreach ($mas_vendidos as $i => $prod): ?>
                            <div class="producto-rank">
                                <span class="rank-numero <?= $i < 3 ? 'rank-' . ($i + 1) : 'rank-n' ?>">
                                    <?= $i + 1 ?>
                                </span>

                                <?php if ($prod['imagen_principal']): ?>
                                    <img src="../../uploads/productos/<?= htmlspecialchars($prod['imagen_principal']) ?>" alt="">
                                <?php else: ?>
                                    <div class="img-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="info">
                                    <h6><?= htmlspecialchars($prod['producto']) ?></h6>
                                    <small><?= htmlspecialchars($prod['categoria']) ?></small>
                                </div>

                                <div class="stats">
                                    <strong><?= $prod['unidades_vendidas'] ?> uds</strong>
                                    <small>$<?= number_format($prod['ingresos_generados'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ventas por categoría -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Ventas por categoría</h5>
                    </div>
                    <canvas id="graficaCategorias" height="220"></canvas>

                    <?php if (!empty($por_categoria)): ?>
                        <?php $max = max(array_column($por_categoria, 'ingresos')); ?>
                        <div class="mt-3">
                            <?php foreach ($por_categoria as $cat): ?>
                                <div style="padding:8px 0; border-bottom:1px solid #f9f9f9">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small style="font-weight:600"><?= htmlspecialchars($cat['categoria']) ?></small>
                                        <small style="color:#888">
                                            <?= $cat['unidades_vendidas'] ?> uds —
                                            $<?= number_format($cat['ingresos'], 0, ',', '.') ?>
                                        </small>
                                    </div>
                                    <div class="barra-container">
                                        <div class="barra">
                                            <div class="barra-fill"
                                                style="width:<?= $max > 0 ? ($cat['ingresos'] / $max) * 100 : 0 ?>%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tallas más vendidas -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-ruler me-2"></i>Tallas más vendidas</h5>
                    </div>

                    <?php if (empty($tallas)): ?>
                        <p class="text-muted text-center py-4">No hay datos aún</p>
                    <?php else: ?>
                        <?php $max_talla = max(array_column($tallas, 'unidades_vendidas')); ?>
                        <?php foreach ($tallas as $talla): ?>
                            <div class="talla-item">
                                <span class="talla-badge"><?= htmlspecialchars($talla['talla']) ?></span>
                                <div class="barra-container" style="flex:1; margin:0 15px">
                                    <div class="barra">
                                        <div class="barra-fill"
                                            style="width:<?= $max_talla > 0 ? ($talla['unidades_vendidas'] / $max_talla) * 100 : 0 ?>%; background:#111">
                                        </div>
                                    </div>
                                </div>
                                <strong><?= $talla['unidades_vendidas'] ?> uds</strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Clientes más activos -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-users me-2"></i>Clientes más activos</h5>
                    </div>

                    <?php if (empty($clientes)): ?>
                        <p class="text-muted text-center py-4">No hay datos aún</p>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <div class="cliente-item">
                                <div class="d-flex align-items-center gap-12" style="gap:12px">
                                    <div class="cliente-avatar">
                                        <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong style="font-size:14px">
                                            <?= htmlspecialchars($cliente['nombre']) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($cliente['email']) ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong>$<?= number_format($cliente['total_gastado'], 0, ',', '.') ?></strong>
                                    <br>
                                    <small class="text-muted"><?= $cliente['total_ordenes'] ?> órdenes</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfica categorías
        const categorias = <?= json_encode(array_column($por_categoria, 'categoria')) ?>;
        const ingresosCat = <?= json_encode(array_column($por_categoria, 'ingresos')) ?>;
        const coloresCat = ['#111', '#e44d26', '#f39c12', '#27ae60', '#3498db', '#9b59b6'];

        new Chart(document.getElementById('graficaCategorias'), {
            type: 'doughnut',
            data: {
                labels: categorias,
                datasets: [{
                    data: ingresosCat,
                    backgroundColor: coloresCat,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                cutout: '60%'
            }
        });
    </script>
</body>

</html>