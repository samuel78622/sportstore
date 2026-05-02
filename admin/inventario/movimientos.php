<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_inventario.php';

soloAdminOInventario();

$success = '';
$error = '';

// ── PROCESAR REABASTECIMIENTO ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variante_id = $_POST['variante_id'] ?? null;
    $cantidad = $_POST['cantidad'] ?? 0;
    $motivo = $_POST['motivo'] ?? 'Ajuste de inventario';

    if ($variante_id && $cantidad > 0) {
        $resultado = moverStock(
            $variante_id,
            $cantidad,
            $motivo,
            $_SESSION['usuario_id']
        );
        if ($resultado['exito']) {
            $success = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    } else {
        $error = 'La cantidad debe ser mayor a 0';
    }
}

// ── FILTRO POR FECHA ──
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');  // Hoy

$movimientos = obtenerMovimientosPorFecha($fecha_inicio, $fecha_fin);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/inventario/movimientos.css">
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
            <a href="movimientos.php" class="menu-item active">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="alertas.php" class="menu-item">
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
                <h1><i class="fas fa-arrows-up-down me-2"></i>Movimientos</h1>
                <small class="text-muted">Historial de entradas y salidas de inventario</small>
            </div>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalReabastecer">
                <i class="fas fa-plus me-1"></i> Reabastecer stock
            </button>
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

        <!-- Filtros de fecha -->
        <div class="filtros-card">
            <h6><i class="fas fa-filter me-2"></i>Filtrar por fecha</h6>
            <form method="GET" action="">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" style="font-weight:600; font-size:13px">
                            Fecha inicio
                        </label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-weight:600; font-size:13px">
                            Fecha fin
                        </label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn-filtrar">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de movimientos -->
        <div class="card-section">
            <div class="card-section-header">
                <h5>
                    <i class="fas fa-list me-2"></i>Historial
                    <span class="badge bg-secondary ms-2"><?= count($movimientos) ?> registros</span>
                </h5>
                <small class="text-muted">
                    Del <?= date('d/m/Y', strtotime($fecha_inicio)) ?>
                    al <?= date('d/m/Y', strtotime($fecha_fin)) ?>
                </small>
            </div>

            <?php if (empty($movimientos)): ?>
                <div class="empty-state">
                    <i class="fas fa-arrows-up-down"></i>
                    <p>No hay movimientos en este período</p>
                </div>
            <?php else: ?>
                <table id="tablaMovimientos" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <!-- Fecha -->
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($mov['fecha'])) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($mov['fecha'])) ?></small>
                                </td>

                                <!-- Tipo -->
                                <td>
                                    <?php if ($mov['tipo'] === 'entrada'): ?>
                                        <span class="badge-entrada">
                                            <i class="fas fa-arrow-down me-1"></i>Entrada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-salida">
                                            <i class="fas fa-arrow-up me-1"></i>Salida
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Producto -->
                                <td><strong><?= htmlspecialchars($mov['producto']) ?></strong></td>

                                <!-- Talla -->
                                <td>
                                    <span
                                        style="background:#f0f0f0; padding:3px 10px; border-radius:6px; font-weight:700; font-size:13px">
                                        <?= htmlspecialchars($mov['talla']) ?>
                                    </span>
                                </td>

                                <!-- Color -->
                                <td><?= htmlspecialchars($mov['color']) ?></td>

                                <!-- Cantidad -->
                                <td>
                                    <span class="<?= $mov['tipo'] === 'entrada' ? 'cantidad-entrada' : 'cantidad-salida' ?>">
                                        <?= $mov['tipo'] === 'entrada' ? '+' : '-' ?>        <?= $mov['cantidad'] ?>
                                    </span>
                                </td>

                                <!-- Motivo -->
                                <td>
                                    <small><?= htmlspecialchars($mov['motivo'] ?? 'Sin motivo') ?></small>
                                </td>

                                <!-- Usuario -->
                                <td>
                                    <small><?= htmlspecialchars($mov['usuario'] ?? 'Sistema') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <!-- ════════════ MODAL REABASTECER ════════════ -->
    <div class="modal fade" id="modalReabastecer" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; overflow:hidden">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-boxes-stacked me-2"></i>Reabastecer Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="">

                        <div class="mb-3">
                            <label class="form-label">
                                ID de variante <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="variante_id" class="form-control" placeholder="Ej: 5" min="1"
                                required>
                            <small class="text-muted">
                                Puedes ver el ID en
                                <a href="index.php" target="_blank">Stock General</a>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Cantidad a agregar <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="cantidad" class="form-control" placeholder="Ej: 50" min="1"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Motivo</label>
                            <select name="motivo" class="form-select">
                                <option value="Compra a proveedor">Compra a proveedor</option>
                                <option value="Devolución de cliente">Devolución de cliente</option>
                                <option value="Ajuste de inventario">Ajuste de inventario</option>
                                <option value="Transferencia de bodega">Transferencia de bodega</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-guardar">
                            <i class="fas fa-plus me-2"></i>AGREGAR STOCK
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tablaMovimientos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']], // Más recientes primero
                columnDefs: [
                    { orderable: false, targets: [] }
                ]
            });
        });
    </script>
</body>

</html>