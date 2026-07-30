<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_usuario.php';

soloAdmin();

$success = '';
$error = '';

// Activar o desactivar cliente
if (isset($_GET['toggle'])) {
    $resultado = toggleUsuario($_GET['toggle']);
    $success = $resultado['mensaje'];
}

$clientes = listarClientes();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="../assets/css/clientes/index.css" rel="stylesheet">
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
             <a href="../facturas/index.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
            <a href="../cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>
            <div class="menu-label">Usuarios</div>
            <a href="index.php" class="menu-item active">
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
                <h1><i class="fas fa-users me-2"></i>Clientes</h1>
                <small class="text-muted"><?= count($clientes) ?> usuarios registrados</small>
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

        <!-- Tarjetas resumen -->
        <?php
        $total = count($clientes);
        $activos = count(array_filter($clientes, fn($c) => $c['activo'] == 1));
        $inactivos = $total - $activos;
        $admins = count(array_filter($clientes, fn($c) => $c['rol'] === 'admin'));
        ?>
        <div class="row g-4 mb-2">
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $total ?></h3>
                        <p>Total usuarios</p>
                    </div>
                    <div class="stat-icon icon-total">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $activos ?></h3>
                        <p>Usuarios activos</p>
                    </div>
                    <div class="stat-icon icon-activos">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $inactivos ?></h3>
                        <p>Usuarios inactivos</p>
                    </div>
                    <div class="stat-icon icon-inactivos">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <h3><?= $admins ?></h3>
                        <p>Administradores</p>
                    </div>
                    <div class="stat-icon icon-admins">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de clientes -->
        <div class="card-section">
            <table id="tablaClientes" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <!-- Avatar + Nombre -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="cliente-avatar">
                                        <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                                </div>
                            </td>

                            <!-- Email -->
                            <td><?= htmlspecialchars($cliente['email']) ?></td>

                            <!-- Teléfono -->
                            <td>
                                <?= $cliente['telefono']
                                    ? htmlspecialchars($cliente['telefono'])
                                    : '<small class="text-muted">Sin teléfono</small>' ?>
                            </td>

                            <!-- Rol -->
                            <td>
                                <span class="rol-badge rol-<?= $cliente['rol'] ?>">
                                    <?= ucfirst($cliente['rol']) ?>
                                </span>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span class="activo-badge <?= $cliente['activo'] ? 'activo-si' : 'activo-no' ?>">
                                    <?= $cliente['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>

                            <!-- Fecha registro -->
                            <td>
                                <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?>
                            </td>

                            <!-- Acciones -->
                            <td>
                                <a href="detalle.php?id=<?= $cliente['id'] ?>" class="btn-accion btn-ver">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <?php if ($cliente['rol'] !== 'admin'): ?>
                                    <a href="index.php?toggle=<?= $cliente['id'] ?>" class="btn-accion btn-toggle">
                                        <i class="fas fa-power-off"></i>
                                        <?= $cliente['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                <?php endif; ?>
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
            $('#tablaClientes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[5, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 6] }
                ]
            });
        });
    </script>
</body>

</html>