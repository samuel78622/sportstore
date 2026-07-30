<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_producto.php';
require_once '../../includes/funciones_variantes.php';

soloAdmin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$producto = obtenerProducto($id);

if (!$producto) {
    header("Location: index.php?error=producto_no_encontrado");
    exit();
}

$error = '';
$success = '';

// Mensaje si viene de crear producto nuevo
if (isset($_GET['nuevo'])) {
    $success = '¡Producto creado! Ahora agrega las variantes (tallas y colores).';
}

// ── CREAR VARIANTE ──
if (isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $color = trim($_POST['color']);
    $precio = $_POST['precio'];
    $tallas = $_POST['talla'] ?? [];
    $stocks = $_POST['stock'] ?? [];

    if (!is_array($tallas)) {
        $tallas = [$tallas];
    }
    if (!is_array($stocks)) {
        $stocks = [$stocks];
    }

    $lineas = [];
    foreach ($tallas as $index => $talla) {
        $talla = trim($talla);
        $stock = isset($stocks[$index]) ? intval($stocks[$index]) : 0;
        if ($talla === '') {
            continue;
        }

        $lineas[] = [
            'talla' => $talla,
            'stock' => $stock
        ];
    }

    if (empty($color)) {
        $error = 'El color es obligatorio';
    } elseif (empty($precio) || !is_numeric($precio) || $precio <= 0) {
        $error = 'El precio debe ser mayor a 0';
    } elseif (empty($lineas)) {
        $error = 'Debes agregar al menos una talla con stock';
    }

    if (empty($error)) {
        $mensajes = [];
        foreach ($lineas as $linea) {
            $resultado = crearOActualizarVariante(
                $id,
                $linea['talla'],
                $color,
                floatval($precio),
                intval($linea['stock'])
            );

            if (!$resultado['exito']) {
                $error = $resultado['mensaje'];
                break;
            }

            $mensajes[] = $resultado['mensaje'];
        }

        if (empty($error)) {
            $success = implode(' | ', $mensajes);
        }
    }
}

// ── EDITAR VARIANTE ──
if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $resultado = editarVariante(
        $_POST['variante_id'],
        trim($_POST['talla']),
        trim($_POST['color']),
        $_POST['precio'],
        $_POST['stock']
    );

    if ($resultado['exito']) {
        $success = $resultado['mensaje'];
    } else {
        $error = $resultado['mensaje'];
    }
}

// ── ELIMINAR VARIANTE ──
if (isset($_GET['eliminar_variante'])) {
    $resultado = eliminarVariante($_GET['eliminar_variante']);
    if ($resultado['exito']) {
        $success = $resultado['mensaje'];
    } else {
        $error = $resultado['mensaje'];
    }
}

$variantes = obtenerVariantesPorProducto($id);

// Tallas predefinidas
$tallas_predefinidas = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Variantes — <?= htmlspecialchars($producto['nombre']) ?> — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/productos/variantes.css" rel="stylesheet">
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
            <div>
                <h1><i class="fas fa-layer-group me-2"></i>Variantes</h1>
                <small class="text-muted">Gestiona tallas, colores y stock</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                ← Volver a productos
            </a>
        </div>

        <!-- Info del producto -->
        <div class="producto-info">
            <?php if ($producto['imagen_principal']): ?>
                <img src="../../uploads/productos/<?= htmlspecialchars($producto['imagen_principal']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>">
            <?php else: ?>
                <div class="placeholder-img"><i class="fas fa-image"></i></div>
            <?php endif; ?>
            <div>
                <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
                <p>
                    <?= htmlspecialchars($producto['categoria']) ?> —
                    <?= ucwords(str_replace('_', ' ', $producto['coleccion'])) ?>
                </p>
            </div>
        </div>

        <!-- Alertas -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-circle-exclamation me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-circle-check me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Formulario agregar variante -->
            <div class="col-md-4">
                <div class="card-section">
                    <h5><i class="fas fa-plus me-2"></i>Agregar Variante</h5>

                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="crear">

                        <div class="mb-3">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="color" id="colorNombre" class="form-control"
                                    placeholder="Ej: Negro, Rojo..." required>
                                <input type="color" id="colorPicker" class="form-control"
                                    style="max-width:50px; padding:4px; cursor:pointer" title="Referencia visual"
                                    onchange="document.getElementById('colorNombre').value = this.value">
                            </div>
                            <small class="text-muted">El selector es solo de referencia visual</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" class="form-control" placeholder="0.00" min="0"
                                    step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Stock por talla <span class="text-danger">*</span></label>
                            <div id="tallasStockContainer">
                                <div class="talla-stock-row row g-2 align-items-end mb-2">
                                    <div class="col-6">
                                        <select name="talla[]" class="form-select" required>
                                            <option value="">Selecciona talla</option>
                                            <optgroup label="Ropa">
                                                <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $t): ?>
                                                    <option value="<?= $t ?>"><?= $t ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <optgroup label="Calzado">
                                                <?php foreach (['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'] as $t): ?>
                                                    <option value="<?= $t ?>"><?= $t ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <option value="UNICA">Talla única</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="stock[]" class="form-control" placeholder="Stock" min="0" required>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarFila(this)">-</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarFila()">
                                <i class="fas fa-plus me-2"></i>Agregar talla
                            </button>
                            <small class="text-muted d-block mt-2">Agrega varias tallas con stock en un solo paso.</small>
                        </div>

                        <button type="submit" class="btn-guardar w-100">
                            <i class="fas fa-plus me-2"></i>AGREGAR VARIANTES
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de variantes -->
            <div class="col-md-8">
                <div class="card-section">
                    <h5>
                        <i class="fas fa-list me-2"></i>Variantes del producto
                        <span class="badge bg-secondary ms-2"><?= count($variantes) ?></span>
                    </h5>

                    <?php if (empty($variantes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-layer-group"></i>
                            <p>No hay variantes aún</p>
                            <small>Agrega la primera talla y color desde el formulario</small>
                        </div>
                    <?php else: ?>
                        <table class="variantes-table">
                            <thead>
                                <tr>
                                    <th>Talla</th>
                                    <th>Color</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variantes as $v): ?>
                                    <tr>
                                        <td>
                                            <span style="
                                        background:#f0f0f0;
                                        padding:4px 10px;
                                        border-radius:6px;
                                        font-weight:700;
                                        font-size:13px
                                    ">
                                                <?= htmlspecialchars($v['talla']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="color-preview">
                                                <span class="color-dot"
                                                    style="background:<?= htmlspecialchars($v['color']) ?>"></span>
                                                <?= htmlspecialchars($v['color']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($v['precio'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $clase = 'stock-ok';
                                            if ($v['stock'] == 0)
                                                $clase = 'stock-agotado';
                                            elseif ($v['stock'] <= 5)
                                                $clase = 'stock-bajo';
                                            ?>
                                            <span class="stock-badge <?= $clase ?>">
                                                <?= $v['stock'] == 0 ? 'Agotado' : $v['stock'] . ' uds' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-accion btn-editar" onclick="abrirModalEditar(
                                            <?= $v['id'] ?>,
                                            '<?= htmlspecialchars($v['talla']) ?>',
                                            '<?= htmlspecialchars($v['color']) ?>',
                                            <?= $v['precio'] ?>,
                                            <?= $v['stock'] ?>
                                        )">
                                                <i class="fas fa-pen"></i> Editar
                                            </button>

                                            <a href="variantes.php?id=<?= $id ?>&eliminar_variante=<?= $v['id'] ?>"
                                            class="btn-accion btn-eliminar"
                                            onclick="return confirm('¿Eliminar esta variante?')"
                                            >
                                            <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ════════════ MODAL EDITAR ════════════ -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; overflow:hidden">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-pen me-2"></i>Editar Variante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="variante_id" id="edit_variante_id">

                        <div class="mb-3">
                            <label class="form-label">Talla</label>
                            <select name="talla" id="edit_talla" class="form-select" required>
                                <optgroup label="Ropa">
                                    <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $t): ?>
                                        <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Calzado">
                                    <?php foreach (['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'] as $t): ?>
                                        <option value="<?= $t ?>"><?= $t ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <option value="UNICA">Talla única</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" id="edit_color" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" id="edit_precio" class="form-control" min="0"
                                    step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
                        </div>

                        <button type="submit" class="btn-guardar w-100">
                            <i class="fas fa-save me-2"></i>GUARDAR CAMBIOS
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirModalEditar(id, talla, color, precio, stock) {
            document.getElementById('edit_variante_id').value = id;
            document.getElementById('edit_talla').value = talla;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_precio').value = precio;
            document.getElementById('edit_stock').value = stock;

            new bootstrap.Modal(document.getElementById('modalEditar')).show();
        }

        function agregarFila() {
            const contenedor = document.getElementById('tallasStockContainer');
            const fila = document.createElement('div');
            fila.className = 'talla-stock-row row g-2 align-items-end mb-2';
            fila.innerHTML = `
                <div class="col-6">
                    <select name="talla[]" class="form-select" required>
                        <option value="">Selecciona talla</option>
                        <optgroup label="Ropa">
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </optgroup>
                        <optgroup label="Calzado">
                            <option value="36">36</option>
                            <option value="37">37</option>
                            <option value="38">38</option>
                            <option value="39">39</option>
                            <option value="40">40</option>
                            <option value="41">41</option>
                            <option value="42">42</option>
                            <option value="43">43</option>
                            <option value="44">44</option>
                            <option value="45">45</option>
                        </optgroup>
                        <option value="UNICA">Talla única</option>
                    </select>
                </div>
                <div class="col-4">
                    <input type="number" name="stock[]" class="form-control" placeholder="Stock" min="0" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarFila(this)">-</button>
                </div>
            `;
            contenedor.appendChild(fila);
        }

        function eliminarFila(button) {
            const fila = button.closest('.talla-stock-row');
            if (!fila) return;
            const contenedor = document.getElementById('tallasStockContainer');
            if (contenedor.querySelectorAll('.talla-stock-row').length > 1) {
                fila.remove();
            } else {
                fila.querySelector('select[name="talla[]"]').value = '';
                fila.querySelector('input[name="stock[]"]').value = '';
            }
        }
    </script>
</body>

</html>