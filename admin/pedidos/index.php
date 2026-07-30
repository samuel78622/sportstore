<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_orden.php';

soloAdmin();

$estado_filtro = $_GET['estado'] ?? null;
$ordenes = listarOrdenes($estado_filtro);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/pedidos/index.css">
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
             <a href="../facturas/index.php" class="menu-item">
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

        <div class="page-header">
            <div>
                <h1><i class="fas fa-bag-shopping me-2"></i>Pedidos</h1>
                <small class="text-muted"><?= count($ordenes) ?> órdenes encontradas</small>
            </div>
        </div>

        <!-- Filtros por estado -->
        <div class="filtros-estado">
            <?php
            $estados = [
                null => ['label' => 'Todos', 'clase' => 'active-todos'],
                'pendiente' => ['label' => 'Pendiente', 'clase' => 'active-pendiente'],
                'empacado' => ['label' => 'Empacado', 'clase' => 'active-empacado'],
                'enviado' => ['label' => 'Enviado', 'clase' => 'active-enviado'],
                'entregado' => ['label' => 'Entregado', 'clase' => 'active-entregado'],
                'cancelado' => ['label' => 'Cancelado', 'clase' => 'active-cancelado'],
            ];

            foreach ($estados as $valor => $info):
                $activo = ($estado_filtro === $valor) ? $info['clase'] : '';
                ?>
                <a href="index.php<?= $valor ? '?estado=' . $valor : '' ?>" class="filtro-btn <?= $activo ?>">
                    <?= $info['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tabla de pedidos -->
        <div class="card-section">
            <table id="tablaOrdenes" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th># Orden</th>
                        <th>Cliente</th>
                        <th>Factura</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $orden): ?>
                        <tr>
                            <td><strong>#<?= $orden['id'] ?></strong></td>
                            <td><?= htmlspecialchars($orden['cliente']) ?></td>
                            <td>
                                <small style="color:#888">
                                    <?= htmlspecialchars($orden['numero_factura'] ?? 'Sin factura') ?>
                                </small>
                            </td>
                            <td><strong>$<?= number_format($orden['total'], 0, ',', '.') ?></strong></td>
                            <td>
                                <span class="badge-estado estado-<?= $orden['estado'] ?>">
                                    <?= ucfirst($orden['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($orden['fecha'])) ?>
                                <br>
                                <small class="text-muted"><?= date('H:i', strtotime($orden['fecha'])) ?></small>
                            </td>
                            <td>
                                <a href="detalle.php?id=<?= $orden['id'] ?>" class="btn-ver">
                                    <i class="fas fa-eye"></i> Ver detalle
                                </a>
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
            $('#tablaOrdenes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });
        });
    </script>
</body>

</html>