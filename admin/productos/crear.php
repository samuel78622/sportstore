<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_producto.php';

soloAdmin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $coleccion   = $_POST['coleccion'] ?? 'normal';
    $imagen      = null;

    // Validaciones
    if (empty($nombre) || empty($categoria_id)) {
        $error = 'El nombre y la categoría son obligatorios';
    } else {
        // Subir imagen si se seleccionó
        if (!empty($_FILES['imagen']['name'])) {
            $resultado_img = subirImagenProducto($_FILES['imagen']);
            if (!$resultado_img['exito']) {
                $error = $resultado_img['mensaje'];
            } else {
                $imagen = $resultado_img['imagen'];
            }
        }

        if (empty($error)) {
            $resultado = crearProducto(
                $nombre,
                $descripcion,
                $categoria_id,
                $coleccion,
                $imagen
            );

            if ($resultado['exito']) {
                $producto_id = $resultado['producto_id'];

                if (!isset($_FILES['imagenes_adicionales'])) {
                    $_FILES['imagenes_adicionales'] = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];
                }

                foreach ($_FILES['imagenes_adicionales']['name'] as $index => $name) {
                    if (empty($name)) {
                        continue;
                    }

                    $file = [
                        'name'     => $_FILES['imagenes_adicionales']['name'][$index],
                        'type'     => $_FILES['imagenes_adicionales']['type'][$index],
                        'tmp_name' => $_FILES['imagenes_adicionales']['tmp_name'][$index],
                        'error'    => $_FILES['imagenes_adicionales']['error'][$index],
                        'size'     => $_FILES['imagenes_adicionales']['size'][$index],
                    ];

                    $resultado_img = subirImagenProducto($file);
                    if ($resultado_img['exito']) {
                        agregarImagenProducto($producto_id, $resultado_img['imagen']);
                    }
                }

                // Redirigir a variantes del producto recién creado
                header("Location: variantes.php?id=" . $producto_id . "&nuevo=1");
                exit();
            } else {
                $error = $resultado['mensaje'];
            }
        }
    }
}

$categorias = listarCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/productos/variantes.css" rel="stylesheet">
    <link href="../assets/css/productos/crear.css" rel="stylesheet">
    <link href="/assets/css/productos.css" rel="stylesheet">

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
        <a href="index.php" class="menu-item">
            <i class="fas fa-shirt"></i> Productos
        </a>
        <a href="crear.php" class="menu-item active">
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
            <h1><i class="fas fa-plus me-2"></i>Nuevo Producto</h1>
            <small class="text-muted">Completa los datos del producto</small>
        </div>
        <a href="index.php" class="btn-cancelar">
            ← Volver a productos
        </a>
    </div>

    <!-- Alerta de error -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-4">

            <!-- Columna izquierda -->
            <div class="col-md-8">

                <!-- Información básica -->
                <div class="card-section">
                    <h5><i class="fas fa-info-circle me-2"></i>Información básica</h5>

                    <div class="mb-3">
                        <label class="form-label">
                            Nombre del producto <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            placeholder="Ej: Camiseta Deportiva Pro"
                            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea
                            name="descripcion"
                            class="form-control"
                            placeholder="Describe el producto, materiales, características..."
                        ><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Categoría <span class="text-danger">*</span>
                        </label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option
                                    value="<?= $cat['id'] ?>"
                                    <?= ($_POST['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Colección -->
                <div class="card-section">
                    <h5><i class="fas fa-layer-group me-2"></i>Colección</h5>

                    <div class="coleccion-options">
                        <label class="coleccion-option <?= ($_POST['coleccion'] ?? 'normal') === 'normal' ? 'selected' : '' ?>">
                            <input type="radio" name="coleccion" value="normal"
                                <?= ($_POST['coleccion'] ?? 'normal') === 'normal' ? 'checked' : '' ?>>
                            <span class="coleccion-icon">👕</span>
                            <div>
                                <div class="coleccion-label">Normal</div>
                                <div class="coleccion-desc">Producto estándar</div>
                            </div>
                        </label>

                        <label class="coleccion-option <?= ($_POST['coleccion'] ?? '') === 'nueva_temporada' ? 'selected' : '' ?>">
                            <input type="radio" name="coleccion" value="nueva_temporada"
                                <?= ($_POST['coleccion'] ?? '') === 'nueva_temporada' ? 'checked' : '' ?>>
                            <span class="coleccion-icon">✨</span>
                            <div>
                                <div class="coleccion-label">Nueva Temporada</div>
                                <div class="coleccion-desc">Lo más reciente</div>
                            </div>
                        </label>

                        <label class="coleccion-option <?= ($_POST['coleccion'] ?? '') === 'outlet' ? 'selected' : '' ?>">
                            <input type="radio" name="coleccion" value="outlet"
                                <?= ($_POST['coleccion'] ?? '') === 'outlet' ? 'checked' : '' ?>>
                            <span class="coleccion-icon">🏷️</span>
                            <div>
                                <div class="coleccion-label">Outlet</div>
                                <div class="coleccion-desc">Precios especiales</div>
                            </div>
                        </label>

                        <label class="coleccion-option <?= ($_POST['coleccion'] ?? '') === 'mas_vendidos' ? 'selected' : '' ?>">
                            <input type="radio" name="coleccion" value="mas_vendidos"
                                <?= ($_POST['coleccion'] ?? '') === 'mas_vendidos' ? 'checked' : '' ?>>
                            <span class="coleccion-icon">🔥</span>
                            <div>
                                <div class="coleccion-label">Más Vendidos</div>
                                <div class="coleccion-desc">Los favoritos</div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Columna derecha -->
            <div class="col-md-4">

                <!-- Imagen -->
                <div class="card-section">
                    <h5><i class="fas fa-image me-2"></i>Imagen del producto</h5>

                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('imagen').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p>Haz clic para subir una imagen</p>
                        <small>JPG, PNG o WEBP — Máx. 2MB</small>
                        <br>
                        <img id="preview-img" src="" alt="Preview">
                    </div>

                    <input
                        type="file"
                        name="imagen"
                        id="imagen"
                        accept="image/jpeg,image/png,image/webp"
                        style="display:none"
                        onchange="previewImagen(this)"
                    >

                    <div class="mt-4">
                        <label class="form-label">Imágenes adicionales</label>
                        <input
                            type="file"
                            name="imagenes_adicionales[]"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="form-control"
                        >
                        <small class="text-muted">Puedes subir varias imágenes adicionales para el producto.</small>
                    </div>
                </div>

                <!-- Info adicional -->
                <div class="card-section">
                    <h5><i class="fas fa-lightbulb me-2"></i>¿Qué sigue?</h5>
                    <p class="text-muted" style="font-size:13px; line-height:1.6">
                        Después de crear el producto serás redirigido a la pantalla de
                        <strong>variantes</strong> donde podrás agregar las tallas,
                        colores y stock disponible.
                    </p>
                </div>

            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-3 mt-2">
            <button type="submit" class="btn-guardar">
                <i class="fas fa-check me-2"></i>CREAR PRODUCTO
            </button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preview de imagen
    function previewImagen(input) {
        const preview = document.getElementById('preview-img');
        const area    = document.getElementById('uploadArea');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src     = e.target.result;
                preview.style.display = 'block';
                area.querySelector('i').style.display    = 'none';
                area.querySelector('p').style.display    = 'none';
                area.querySelector('small').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Drag and drop
    const uploadArea = document.getElementById('uploadArea');

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('imagen').files = e.dataTransfer.files;
            previewImagen(document.getElementById('imagen'));
        }
    });

    // Selección visual de colección
    document.querySelectorAll('.coleccion-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.coleccion-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>
</body>
</html>