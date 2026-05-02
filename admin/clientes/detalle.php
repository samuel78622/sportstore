<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_usuario.php';
require_once '../../includes/funciones_orden.php';

soloAdmin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$cliente = obtenerPerfil($id);
$direcciones = obtenerDirecciones($id);
$ordenes = obtenerOrdenesCliente($id);

if (!$cliente) {
    header("Location: index.php?error=cliente_no_encontrado");
    exit();
}

// Calcular total gastado
$total_gastado = array_sum(array_column(
    array_filter($ordenes, fn($o) => $o['estado'] !== 'cancelado'),
    'total'
));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cliente['nombre']) ?> — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/clientes/detalle.css" rel="stylesheet">
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
                <h1><i class="fas fa-user me-2"></i>Detalle del cliente</h1>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Volver</a>
        </div>

        <!-- Perfil del cliente -->
        <div class="perfil-card">
            <div class="perfil-avatar">
                <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
            </div>
            <div class="perfil-info">
                <h2><?= htmlspecialchars($cliente['nombre']) ?></h2>
                <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($cliente['email']) ?></p>
                <?php if ($cliente['telefono']): ?>
                    <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($cliente['telefono']) ?></p>
                <?php endif; ?>
                <div class="mt-2">
                    <span class="rol-badge rol-<?= $cliente['rol'] ?>">
                        <?= ucfirst($cliente['rol']) ?>
                    </span>
                </div>
            </div>
            <div class="ms-auto text-end">
                <small class="text-muted">Miembro desde</small>
                <p style="font-weight:700; color:#111">
                    <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?>
                </p>
            </div>
        </div>

        <!-- Estadísticas del cliente -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?= count($ordenes) ?></h3>
                    <p>Total de pedidos</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>
                        <?= count(array_filter($ordenes, fn($o) => $o['estado'] === 'entregado')) ?>
                    </h3>
                    <p>Pedidos entregados</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3 style="color:#e44d26">
                        $<?= number_format($total_gastado, 0, ',', '.') ?>
                    </h3>
                    <p>Total gastado</p>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- Columna izquierda -->
            <div class="col-md-8">

                <!-- Historial de pedidos -->
                <div class="card-section">
                    <h5><i class="fas fa-bag-shopping me-2"></i>Historial de pedidos</h5>

                    <?php if (empty($ordenes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bag-shopping"></i>
                            <p>Este cliente no tiene pedidos aún</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th># Orden</th>
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
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($orden['numero_factura'] ?? 'Sin factura') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($orden['total'], 0, ',', '.') ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge-estado estado-<?= $orden['estado'] ?>">
                                                <?= ucfirst($orden['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($orden['fecha'])) ?></td>
                                        <td>
                                            <a href="../pedidos/detalle.php?id=<?= $orden['id'] ?>" class="btn-ver-orden">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Columna derecha -->
            <div class="col-md-4">

                <!-- Datos personales -->
                <div class="card-section">
                    <h5><i class="fas fa-info-circle me-2"></i>Datos personales</h5>
                    <div class="info-grid" style="grid-template-columns:1fr">
                        <div class="info-item">
                            <p>Nombre completo</p>
                            <h6><?= htmlspecialchars($cliente['nombre']) ?></h6>
                        </div>
                        <div class="info-item">
                            <p>Email</p>
                            <h6><?= htmlspecialchars($cliente['email']) ?></h6>
                        </div>
                        <div class="info-item">
                            <p>Teléfono</p>
                            <h6><?= htmlspecialchars($cliente['telefono'] ?? 'No registrado') ?></h6>
                        </div>
                        <div class="info-item">
                            <p>Rol</p>
                            <h6><?= ucfirst($cliente['rol']) ?></h6>
                        </div>
                    </div>
                </div>

                <!-- Direcciones -->
                <div class="card-section">
                    <h5><i class="fas fa-location-dot me-2"></i>Direcciones</h5>

                    <?php if (empty($direcciones)): ?>
                        <div class="empty-state">
                            <i class="fas fa-location-dot"></i>
                            <p>Sin direcciones registradas</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($direcciones as $dir): ?>
                            <div class="direccion-card <?= $dir['principal'] ? 'principal' : '' ?>">
                                <h6>
                                    <?= $dir['principal'] ? '⭐ Principal' : 'Dirección' ?>
                                </h6>
                                <p><?= htmlspecialchars($dir['direccion']) ?></p>
                                <p><?= htmlspecialchars($dir['ciudad']) ?>, <?= htmlspecialchars($dir['departamento']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>