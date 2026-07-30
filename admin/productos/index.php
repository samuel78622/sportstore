<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_producto.php';

soloAdmin();

// Eliminar producto si se solicita
if (isset($_GET['eliminar'])) {
    $resultado = eliminarProducto($_GET['eliminar']);
    $msg_tipo  = $resultado['exito'] ? 'success' : 'danger';
    $msg       = $resultado['mensaje'];
}

$productos   = listarProductos();
$categorias  = listarCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="../assets/css/productos/producto.css" rel="stylesheet">
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
        <a href="index.php" class="menu-item active">
            <i class="fas fa-shirt"></i> Productos
        </a>
        <a href="crear.php" class="menu-item">
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
        <h1><i class="fas fa-shirt me-2"></i>Productos</h1>
        <a href="crear.php" class="btn-nuevo">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </div>

    <!-- Alertas -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg_tipo ?> alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla de productos -->
    <div class="card-section">
        <table id="tablaProductos" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Colección</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <!-- Imagen -->
                    <td>
                        <?php if ($producto['imagen_principal']): ?>
                            <img
                                src="../../uploads/productos/<?= htmlspecialchars($producto['imagen_principal']) ?>"
                                class="producto-img"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            >
                        <?php else: ?>
                            <div class="producto-img-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Nombre -->
                    <td>
                        <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 50)) ?>
                            <?= strlen($producto['descripcion'] ?? '') > 50 ? '...' : '' ?>
                        </small>
                    </td>

                    <!-- Categoría -->
                    <td><?= htmlspecialchars($producto['categoria'] ?? 'Sin categoría') ?></td>

                    <!-- Colección -->
                    <td>
                        <span class="badge-coleccion coleccion-<?= $producto['coleccion'] ?>">
                            <?= ucwords(str_replace('_', ' ', $producto['coleccion'])) ?>
                        </span>
                    </td>

                    <!-- Estado -->
                    <td>
                        <span class="badge-activo <?= $producto['activo'] ? 'activo-si' : 'activo-no' ?>">
                            <?= $producto['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td>
                        <a href="editar.php?id=<?= $producto['id'] ?>" class="btn-accion btn-editar">
                            <i class="fas fa-pen"></i> Editar
                        </a>
                        <a href="variantes.php?id=<?= $producto['id'] ?>" class="btn-accion btn-variantes">
                            <i class="fas fa-layer-group"></i> Variantes
                        </a>
                        <a href="index.php?eliminar=<?= $producto['id'] ?>"
                           class="btn-accion btn-eliminar"
                           onclick="return confirm('¿Seguro que deseas eliminar este producto?')">
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
        $('#tablaProductos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            columnDefs: [
                { orderable: false, targets: [0, 5] } // Sin ordenar imagen y acciones
            ]
        });
    });
</script>
</body>
</html>