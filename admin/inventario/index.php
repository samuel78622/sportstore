<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_inventario.php';

soloAdminOInventario();

$stock = obtenerStockGeneral();
$resumen = resumenInventario();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/inventario/index.css">
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
            <a href="index.php" class="menu-item active">
                <i class="fas fa-boxes-stacked"></i> Stock General
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="alertas.php" class="menu-item">
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
            <div>
                <h1><i class="fas fa-boxes-stacked me-2"></i>Stock General</h1>
                <small class="text-muted">Control de inventario por variante</small>
            </div>
            <div class="d-flex gap-2">
                <a href="movimientos.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrows-up-down me-1"></i> Movimientos
                </a>
                <a href="alertas.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-triangle-exclamation me-1"></i> Alertas
                </a>
            </div>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="row g-4 mb-2">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-info">
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
                    <div class="stat-info">
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
                    <div class="stat-info">
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
                    <div class="stat-info">
                        <h3 class="text-success">
                            $<?= number_format($resumen['valor_inventario'], 0, ',', '.') ?>
                        </h3>
                        <p>Valor del inventario</p>
                    </div>
                    <div class="stat-icon icon-valor">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de stock -->
        <div class="card-section">
            <div class="card-section-header">
                <h5><i class="fas fa-table me-2"></i>Inventario detallado</h5>
                <a href="../reportes/inventario.php" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-file-export me-1"></i> Exportar reporte
                </a>
            </div>

            <table id="tablaStock" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stock as $item): ?>
                        <tr>
                            <!-- Imagen -->
                            <td>
                                <?php if ($item['imagen_principal']): ?>
                                    <img src="../../uploads/productos/<?= htmlspecialchars($item['imagen_principal']) ?>"
                                        class="producto-img" alt="">
                                <?php else: ?>
                                    <div class="producto-img-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Producto -->
                            <td><strong><?= htmlspecialchars($item['nombre']) ?></strong></td>

                            <!-- Categoría -->
                            <td><?= htmlspecialchars($item['categoria']) ?></td>

                            <!-- Talla -->
                            <td>
                                <span
                                    style="background:#f0f0f0; padding:3px 10px; border-radius:6px; font-weight:700; font-size:13px">
                                    <?= htmlspecialchars($item['talla']) ?>
                                </span>
                            </td>

                            <!-- Color -->
                            <td><?= htmlspecialchars($item['color']) ?></td>

                            <!-- Precio -->
                            <td>$<?= number_format($item['precio'], 2) ?></td>

                            <!-- Stock -->
                            <td><strong><?= $item['stock'] ?></strong></td>

                            <!-- Estado -->
                            <td>
                                <?php
                                $clase = 'stock-ok';
                                $label = 'Disponible';
                                if ($item['stock'] == 0) {
                                    $clase = 'stock-agotado';
                                    $label = 'Agotado';
                                } elseif ($item['stock'] <= 5) {
                                    $clase = 'stock-bajo';
                                    $label = 'Stock bajo';
                                }
                                ?>
                                <span class="stock-badge <?= $clase ?>">
                                    <?= $label ?>
                                </span>
                            </td>

                            <!-- Acción -->
                            <td>
                                <button class="btn-movimientos" onclick="abrirModalmovimientos(
                                <?= $item['variante_id'] ?>,
                                '<?= htmlspecialchars($item['nombre']) ?>',
                                '<?= htmlspecialchars($item['talla']) ?>',
                                '<?= htmlspecialchars($item['color']) ?>',
                                <?= $item['stock'] ?>
                            )">
                                    <i class="fas fa-plus"></i> movimientos
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ════════════ MODAL MOVIMIENTOS ════════════ -->
    <div class="modal fade" id="modalmovimientos" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; overflow:hidden">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-boxes-stacked me-2"></i>movimientos de Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <!-- Info de la variante -->
                    <div style="background:#f9f9f9; border-radius:10px; padding:15px; margin-bottom:20px">
                        <p style="font-size:13px; color:#888; margin:0">Producto</p>
                        <p style="font-weight:800; color:#111; margin:3px 0" id="modal_nombre"></p>
                        <div class="d-flex gap-3 mt-1">
                            <small>Talla: <strong id="modal_talla"></strong></small>
                            <small>Color: <strong id="modal_color"></strong></small>
                            <small>Stock actual: <strong id="modal_stock"></strong></small>
                        </div>
                    </div>

                    <form method="POST" action="movimientos.php">
                        <input type="hidden" name="variante_id" id="modal_variante_id">

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
            $('#tablaStock').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                columnDefs: [
                    { orderable: false, targets: [0, 8] }
                ],
                order: [[6, 'asc']] // Ordenar por stock ascendente
            });
        });

        function abrirModalmovimientos(id, nombre, talla, color, stock) {
            document.getElementById('modal_variante_id').value = id;
            document.getElementById('modal_nombre').textContent = nombre;
            document.getElementById('modal_talla').textContent = talla;
            document.getElementById('modal_color').textContent = color;
            document.getElementById('modal_stock').textContent = stock + ' uds';

            new bootstrap.Modal(document.getElementById('modalmovimientos')).show();
        }
    </script>
</body>

</html>