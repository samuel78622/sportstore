<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_cupon.php';

soloAdmin();

$success = '';
$error = '';

// Activar o desactivar cupón
if (isset($_GET['toggle'])) {
    $resultado = toggleCupon($_GET['toggle']);
    $success = $resultado['mensaje'];
}

// Eliminar cupón
if (isset($_GET['eliminar'])) {
    $resultado = eliminarCupon($_GET['eliminar']);
    if ($resultado['exito']) {
        $success = $resultado['mensaje'];
    } else {
        $error = $resultado['mensaje'];
    }
}

$cupones = listarCupones();
$estadisticas = estadisticasCupones();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupones — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/cupones/index.css">
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
            <a href="index.php" class="menu-item active">
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
                <h1><i class="fas fa-tag me-2"></i>Cupones</h1>
                <small class="text-muted">Gestión de descuentos y promociones</small>
            </div>
            <a href="crear.php" class="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Cupón
            </a>
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

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4 mb-2">
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $estadisticas['activos'] ?></h3>
                        <p>Cupones activos</p>
                    </div>
                    <div class="stat-icon icon-activos">
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $estadisticas['vencidos'] ?></h3>
                        <p>Cupones vencidos</p>
                    </div>
                    <div class="stat-icon icon-vencidos">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $estadisticas['mas_usado']['codigo'] ?? 'N/A' ?></h3>
                        <p>Cupón más usado (<?= $estadisticas['mas_usado']['usos_actuales'] ?? 0 ?> usos)</p>
                    </div>
                    <div class="stat-icon icon-usado">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3>$<?= number_format($estadisticas['total_descuentos'], 0, ',', '.') ?></h3>
                        <p>Total en descuentos</p>
                    </div>
                    <div class="stat-icon icon-descuento">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de cupones -->
        <div class="card-section">
            <div class="card-section-header">
                <h5><i class="fas fa-list me-2"></i>Todos los cupones</h5>
            </div>

            <table id="tablaCupones" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Descuento</th>
                        <th>Usos</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cupones as $cupon): ?>
                        <tr>
                            <!-- Código -->
                            <td>
                                <span class="cupon-codigo">
                                    <?= htmlspecialchars($cupon['codigo']) ?>
                                </span>
                            </td>

                            <!-- Tipo -->
                            <td>
                                <span class="tipo-badge tipo-<?= $cupon['tipo'] ?>">
                                    <?= $cupon['tipo'] === 'porcentaje' ? '% Porcentaje' : '$ Monto fijo' ?>
                                </span>
                            </td>

                            <!-- Descuento -->
                            <td>
                                <strong>
                                    <?php if ($cupon['tipo'] === 'porcentaje'): ?>
                                        <?= $cupon['descuento'] ?>%
                                    <?php else: ?>
                                        $<?= number_format($cupon['descuento'], 0, ',', '.') ?>
                                    <?php endif; ?>
                                </strong>
                            </td>

                            <!-- Usos -->
                            <td>
                                <?php $porcentaje_uso = $cupon['usos_maximos'] > 0
                                    ? ($cupon['usos_actuales'] / $cupon['usos_maximos']) * 100
                                    : 0;
                                ?>
                                <div class="usos-container">
                                    <div class="usos-barra">
                                        <div class="usos-fill" style="width:<?= $porcentaje_uso ?>%"></div>
                                    </div>
                                    <small style="white-space:nowrap; color:#888">
                                        <?= $cupon['usos_actuales'] ?>/<?= $cupon['usos_maximos'] ?>
                                    </small>
                                </div>
                            </td>

                            <!-- Vencimiento -->
                            <td>
                                <?php if ($cupon['fecha_vencimiento']): ?>
                                    <?php
                                    $hoy = new DateTime();
                                    $vence = new DateTime($cupon['fecha_vencimiento']);
                                    $diff = $hoy->diff($vence);
                                    $vencido = $vence < $hoy;
                                    ?>
                                    <span style="color:<?= $vencido ? '#e44d26' : ($diff->days <= 7 ? '#f39c12' : '#333') ?>">
                                        <?= date('d/m/Y', strtotime($cupon['fecha_vencimiento'])) ?>
                                        <?php if (!$vencido && $diff->days <= 7): ?>
                                            <br><small style="color:#f39c12">Vence en <?= $diff->days ?> días</small>
                                        <?php elseif ($vencido): ?>
                                            <br><small style="color:#e44d26">Vencido</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <small class="text-muted">Sin vencimiento</small>
                                <?php endif; ?>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span class="estado-badge estado-<?= $cupon['estado_cupon'] ?>">
                                    <?= $cupon['estado_cupon'] ?>
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td>
                                <a href="editar.php?id=<?= $cupon['id'] ?>" class="btn-accion btn-editar">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <a href="index.php?toggle=<?= $cupon['id'] ?>" class="btn-accion btn-toggle">
                                    <i class="fas fa-power-off"></i>
                                    <?= $cupon['activo'] ? 'Desactivar' : 'Activar' ?>
                                </a>
                                <a href="index.php?eliminar=<?= $cupon['id'] ?>" class="btn-accion btn-eliminar"
                                    onclick="return confirm('¿Eliminar este cupón?')">
                                    <i class="fas fa-trash"></i> Eliminar
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
            $('#tablaCupones').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });
        });
    </script>
</body>

</html>