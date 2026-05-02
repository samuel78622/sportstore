<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_inventario.php';
require_once '../../includes/funciones_reportes.php';

soloAdmin();

$reporte  = reporteInventario();
$resumen  = resumenInventario();

// Exportar CSV
if (isset($_GET['exportar'])) {
    exportarCSV(
        $reporte,
        'inventario_' . date('Y-m-d'),
        ['Producto', 'Categoría', 'Talla', 'Color', 'Precio', 'Stock', 'Valor Total', 'Estado']
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/reportes/inventario.css">
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
        <a href="productos.php" class="menu-item">
            <i class="fas fa-star"></i> Más vendidos
        </a>
        <a href="inventario.php" class="menu-item active">
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
            <h1><i class="fas fa-warehouse me-2"></i>Reporte de Inventario</h1>
            <small class="text-muted">Estado actual de todo el inventario</small>
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
                    <h3><?= $resumen['total_productos'] ?></h3>
                    <p>Productos activos</p>
                </div>
                <div class="stat-icon icon-total">
                    <i class="fas fa-shirt"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <h3><?= $resumen['total_variantes'] ?></h3>
                    <p>Variantes totales</p>
                </div>
                <div class="stat-icon icon-total">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <h3 class="text-danger"><?= $resumen['agotados'] ?></h3>
                    <p>Variantes agotadas</p>
                </div>
                <div class="stat-icon icon-agotado">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div>
                    <h3 class="text-success">
                        $<?= number_format($resumen['valor_inventario'], 0, ',', '.') ?>
                    </h3>
                    <p>Valor total inventario</p>
                </div>
                <div class="stat-icon icon-valor">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla completa de inventario -->
    <div class="card-section">
        <div class="card-section-header">
            <h5><i class="fas fa-table me-2"></i>Inventario completo</h5>
            <small class="text-muted"><?= count($reporte) ?> variantes</small>
        </div>

        <table id="tablaInventario" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Talla</th>
                    <th>Color</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Valor total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['producto']) ?></strong></td>
                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                    <td>
                        <span style="background:#f0f0f0; padding:3px 10px; border-radius:6px; font-weight:700; font-size:13px">
                            <?= htmlspecialchars($item['talla']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($item['color']) ?></td>
                    <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                    <td><strong><?= $item['stock'] ?></strong></td>
                    <td>$<?= number_format($item['valor_total'], 0, ',', '.') ?></td>
                    <td>
                        <?php
                        $clase = 'stock-ok';
                        if ($item['estado_stock'] === 'Agotado') $clase = 'stock-agotado';
                        elseif ($item['estado_stock'] === 'Stock bajo') $clase = 'stock-bajo';
                        ?>
                        <span class="stock-badge <?= $clase ?>">
                            <?= $item['estado_stock'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#tablaInventario').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[5, 'asc']] // Ordenar por stock ascendente
        });
    });
</script>
</body>
</html>