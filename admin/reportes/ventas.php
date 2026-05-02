<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_reportes.php';

soloAdmin();

$ventas_mes = ventasPorMes();
$ventas_dia = ventasPorDia(30);
$reporte_general = reporteGeneral();
$ordenes_estado = ordenesPorEstado();
$productos_mas_vendidos = reporteProductosMasVendidos(10);
$categorias_mas_vendidas = ventasPorCategoria();

// Exportar CSV
if (isset($_GET['exportar'])) {
    exportarCSV(
        $ventas_mes,
        'ventas_' . date('Y'),
        ['Mes', 'Nombre Mes', 'Total Órdenes', 'Ingresos']
    );
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../assets/css/reportes/ventas.css" rel="stylesheet">
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
            <a href="ventas.php" class="menu-item active">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="productos.php" class="menu-item">
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
                <h1><i class="fas fa-chart-line me-2"></i>Reporte de Ventas</h1>
                <small class="text-muted">Análisis de ingresos y órdenes</small>
            </div>
            <a href="?exportar=1" class="btn-exportar">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="row g-4 mb-2">
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3>$<?= number_format($reporte_general['hoy']['ingresos'] ?? 0, 0, ',', '.') ?></h3>
                        <p>Ingresos hoy</p>
                        <?php $v = $reporte_general['variacion']; ?>
                        <div class="variacion" style="color:<?= $v >= 0 ? '#27ae60' : '#e44d26' ?>">
                            <?= $v >= 0 ? '▲' : '▼' ?> <?= abs($v) ?>% vs ayer
                        </div>
                    </div>
                    <div class="stat-icon icon-hoy">
                        <i class="fas fa-sun"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3>$<?= number_format($reporte_general['mes']['ingresos'] ?? 0, 0, ',', '.') ?></h3>
                        <p>Ingresos del mes</p>
                    </div>
                    <div class="stat-icon icon-mes">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $reporte_general['hoy']['ordenes'] ?? 0 ?></h3>
                        <p>Órdenes hoy</p>
                    </div>
                    <div class="stat-icon icon-anio">
                        <i class="fas fa-bag-shopping"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $reporte_general['mes']['ordenes'] ?? 0 ?></h3>
                        <p>Órdenes del mes</p>
                    </div>
                    <div class="stat-icon icon-total">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de ventas mensuales -->
        <div class="card-section">
            <div class="card-section-header">
                <h5><i class="fas fa-chart-line me-2"></i>Ingresos por mes — <?= date('Y') ?></h5>
            </div>
            <canvas id="graficaVentas" height="100"></canvas>
        </div>

        <div class="row g-4 mt-1">

            <!-- Ventas por mes tabla -->
            <div class="col-md-7">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-table me-2"></i>Ventas por mes</h5>
                    </div>
                    <?php if (empty($ventas_mes)): ?>
                        <p class="text-muted text-center py-4">No hay datos de ventas aún</p>
                    <?php else: ?>
                        <?php
                        $max_ingresos = max(array_column($ventas_mes, 'ingresos'));
                        ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Órdenes</th>
                                    <th>Ingresos</th>
                                    <th>Nivel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas_mes as $venta): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($venta['nombre_mes']) ?></strong></td>
                                        <td><?= $venta['total_ordenes'] ?></td>
                                        <td><strong>$<?= number_format($venta['ingresos'], 0, ',', '.') ?></strong></td>
                                        <td>
                                            <?php $porcentaje = $max_ingresos > 0 ? ($venta['ingresos'] / $max_ingresos) * 100 : 0; ?>
                                            <div class="barra-container">
                                                <div class="barra">
                                                    <div class="barra-fill" style="width:<?= $porcentaje ?>%"></div>
                                                </div>
                                                <small style="color:#888; font-size:11px; white-space:nowrap">
                                                    <?= round($porcentaje) ?>%
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Órdenes por estado -->
            <div class="col-md-5">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Órdenes por estado</h5>
                    </div>
                    <canvas id="graficaEstados" height="200"></canvas>
                    <div class="mt-3">
                        <?php foreach ($ordenes_estado as $oe): ?>
                            <div class="d-flex justify-content-between align-items-center py-2"
                                style="border-bottom:1px solid #f0f0f0">
                                <span class="badge-estado estado-<?= $oe['estado'] ?>">
                                    <?= ucfirst($oe['estado']) ?>
                                </span>
                                <div class="text-end">
                                    <strong><?= $oe['total'] ?></strong>
                                    <small class="text-muted ms-2">
                                        $<?= number_format($oe['monto_total'], 0, ',', '.') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <!-- Gráficas de productos y categorías -->
        <div class="row g-4 mt-1">
            <!-- Productos más vendidos -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-star me-2"></i>Productos más vendidos</h5>
                    </div>
                    <canvas id="graficaProductos" height="100"></canvas>
                </div>
            </div>

            <!-- Categorías más vendidas -->
            <div class="col-md-6">
                <div class="card-section">
                    <div class="card-section-header">
                        <h5><i class="fas fa-box me-2"></i>Categorías más vendidas</h5>
                    </div>
                    <canvas id="graficaCategorias" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Análisis de unidades vendidas -->
        <div class="card-section mt-4">
            <div class="card-section-header">
                <h5><i class="fas fa-chart-bar me-2"></i>Análisis de ventas por producto (Unidades vs Ingresos)</h5>
            </div>
            <table class="tabla-analisis">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th style="text-align:center">Unidades</th>
                        <th style="text-align:center">Ingresos</th>
                        <th style="text-align:center">Ingreso/Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $max_unidades = max(array_column($productos_mas_vendidos, 'unidades_vendidas')) ?: 1;
                    $max_ingresos = max(array_column($productos_mas_vendidos, 'ingresos_generados')) ?: 1;
                    ?>
                    <?php foreach ($productos_mas_vendidos as $index => $producto): 
                        $porcentaje_unidades = ($producto['unidades_vendidas'] / $max_unidades) * 100;
                        $porcentaje_ingresos = ($producto['ingresos_generados'] / $max_ingresos) * 100;
                        $ingreso_unitario = $producto['unidades_vendidas'] > 0 ? $producto['ingresos_generados'] / $producto['unidades_vendidas'] : 0;
                    ?>
                    <tr>
                        <td><span class="badge-numero"><?= $index + 1 ?></span></td>
                        <td><strong><?= htmlspecialchars(substr($producto['producto'], 0, 30)) ?></strong></td>
                        <td style="text-align:center">
                            <div class="barra-container-analisis">
                                <div class="barra-azul" style="width:<?= $porcentaje_unidades ?>%"></div>
                                <span class="valor-barra"><?= $producto['unidades_vendidas'] ?></span>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <div class="barra-container-analisis">
                                <div class="barra-roja" style="width:<?= $porcentaje_ingresos ?>%"></div>
                                <span class="valor-barra">$<?= number_format($producto['ingresos_generados'], 0, ',', '.') ?></span>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <strong style="color:#27ae60">$<?= number_format($ingreso_unitario, 0, ',', '.') ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── GRÁFICA VENTAS POR MES ──
        const meses = <?= json_encode(array_column($ventas_mes, 'nombre_mes')) ?>;
        const ingresos = <?= json_encode(array_column($ventas_mes, 'ingresos')) ?>;

        new Chart(document.getElementById('graficaVentas'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: ingresos,
                    backgroundColor: '#555',
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#ddd' },
                        border: { color: '#999' }
                    },
                    x: {
                        grid: { display: false },
                        border: { color: '#999' }
                    }
                }
            }
        });

        // ── GRÁFICA ESTADOS ──
        const estadosLabels = <?= json_encode(array_column($ordenes_estado, 'estado')) ?>;
        const estadosTotales = <?= json_encode(array_column($ordenes_estado, 'total')) ?>;
        const colores = {
            'pendiente': '#daa520',
            'empacado': '#4a90e2',
            'enviado': '#5cb85c',
            'entregado': '#17a2b8',
            'cancelado': '#dc3545'
        };

        new Chart(document.getElementById('graficaEstados'), {
            type: 'doughnut',
            data: {
                labels: estadosLabels.map(e => e.charAt(0).toUpperCase() + e.slice(1)),
                datasets: [{
                    data: estadosTotales,
                    backgroundColor: estadosLabels.map(e => colores[e] || '#999'),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '60%'
            }
        });

        // ── GRÁFICA PRODUCTOS MÁS VENDIDOS ──
        const productosLabels = <?= json_encode(array_map(fn($p) => substr($p['producto'], 0, 20), $productos_mas_vendidos)) ?>;
        const productosUnidades = <?= json_encode(array_column($productos_mas_vendidos, 'unidades_vendidas')) ?>;

        new Chart(document.getElementById('graficaProductos'), {
            type: 'bar',
            data: {
                labels: productosLabels,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: productosUnidades,
                    backgroundColor: '#6b8cae',
                    borderColor: '#333',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#ddd' },
                        border: { color: '#999' }
                    },
                    y: {
                        grid: { display: false },
                        border: { color: '#999' }
                    }
                }
            }
        });

        // ── GRÁFICA CATEGORÍAS MÁS VENDIDAS ──
        const categoriasLabels = <?= json_encode(array_column($categorias_mas_vendidas, 'categoria')) ?>;
        const categoriasUnidades = <?= json_encode(array_column($categorias_mas_vendidas, 'unidades_vendidas')) ?>;
        const categoriasIngresos = <?= json_encode(array_column($categorias_mas_vendidas, 'ingresos')) ?>;
        const coloresCategorias = ['#e44d26', '#f39c12', '#3498db', '#27ae60', '#1abc9c', '#9b59b6', '#34495e', '#e67e22'];

        new Chart(document.getElementById('graficaCategorias'), {
            type: 'doughnut',
            data: {
                labels: categoriasLabels,
                datasets: [{
                    data: categoriasUnidades,
                    backgroundColor: ['#999', '#777', '#555', '#aaa', '#888', '#666', '#bbbbbb', '#888'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' unidades';
                            }
                        }
                    }
                }
            }
        });

        // ── GRÁFICA ANÁLISIS DE VENTAS ──
        // Reemplazada por tabla interactiva más legible
    </script>
</body>

</html>