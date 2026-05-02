<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_producto.php';

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

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? '';
    $coleccion    = $_POST['coleccion'] ?? 'normal';
    $imagen       = $producto['imagen_principal']; // Mantener imagen actual

    // Validaciones
    if (empty($nombre) || empty($categoria_id)) {
        $error = 'El nombre y la categoría son obligatorios';
    } else {
        // Subir nueva imagen si se seleccionó
        if (!empty($_FILES['imagen']['name'])) {
            $resultado_img = subirImagenProducto($_FILES['imagen']);
            if (!$resultado_img['exito']) {
                $error = $resultado_img['mensaje'];
            } else {
                // Eliminar imagen anterior si existe
                if ($producto['imagen_principal']) {
                    $ruta_anterior = '../../uploads/productos/' . $producto['imagen_principal'];
                    if (file_exists($ruta_anterior)) {
                        unlink($ruta_anterior);
                    }
                }
                $imagen = $resultado_img['imagen'];
            }
        }

        if (empty($error)) {
            $resultado = editarProducto(
                $id,
                $nombre,
                $descripcion,
                $categoria_id,
                $coleccion,
                $imagen
            );

            if ($resultado['exito']) {
                $success = $resultado['mensaje'];
                // Recargar datos actualizados
                $producto = obtenerProducto($id);
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
    <title>Editar Producto — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
 <link href="../assets/css/productos/variantes.css" rel="stylesheet">
    <link href="../assets/css/productos/editar.css" rel="stylesheet">
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
            <h1><i class="fas fa-pen me-2"></i>Editar Producto</h1>
            <small class="text-muted">ID: #<?= $producto['id'] ?> — <?= htmlspecialchars($producto['nombre']) ?></small>
        </div>
        <a href="index.php" class="btn-cancelar">← Volver</a>
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
                            value="<?= htmlspecialchars($_POST['nombre'] ?? $producto['nombre']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control"><?= htmlspecialchars($_POST['descripcion'] ?? $producto['descripcion'] ?? '') ?></textarea>
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
                                    <?= ($_POST['categoria_id'] ?? $producto['categoria_id']) == $cat['id'] ? 'selected' : '' ?>
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

                    <?php
                    $coleccion_actual = $_POST['coleccion'] ?? $producto['coleccion'];
                    $colecciones = [
                        'normal'          => ['icon' => '👕', 'label' => 'Normal',          'desc' => 'Producto estándar'],
                        'nueva_temporada' => ['icon' => '✨', 'label' => 'Nueva Temporada',  'desc' => 'Lo más reciente'],
                        'outlet'          => ['icon' => '🏷️', 'label' => 'Outlet',           'desc' => 'Precios especiales'],
                        'mas_vendidos'    => ['icon' => '🔥', 'label' => 'Más Vendidos',     'desc' => 'Los favoritos'],
                    ];
                    ?>

                    <div class="coleccion-options">
                        <?php foreach ($colecciones as $valor => $info): ?>
                        <label class="coleccion-option <?= $coleccion_actual === $valor ? 'selected' : '' ?>">
                            <input type="radio" name="coleccion" value="<?= $valor ?>"
                                <?= $coleccion_actual === $valor ? 'checked' : '' ?>>
                            <span class="coleccion-icon"><?= $info['icon'] ?></span>
                            <div>
                                <div class="coleccion-label"><?= $info['label'] ?></div>
                                <div class="coleccion-desc"><?= $info['desc'] ?></div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Columna derecha -->
            <div class="col-md-4">

                <!-- Imagen actual -->
                <div class="card-section">
                    <h5><i class="fas fa-image me-2"></i>Imagen del producto</h5>

                    <?php if ($producto['imagen_principal']): ?>
                        <img
                            src="../../uploads/productos/<?= htmlspecialchars($producto['imagen_principal']) ?>"
                            class="imagen-actual"
                            alt="Imagen actual"
                            id="imagen-actual"
                        >
                        <small class="text-muted d-block mb-3">
                            Imagen actual — sube una nueva para reemplazarla
                        </small>
                    <?php else: ?>
                        <div class="imagen-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>

                    <div class="upload-area" onclick="document.getElementById('imagen').click()">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p>Haz clic para cambiar la imagen</p>
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
                </div>

                <!-- Acciones rápidas -->
                <div class="card-section">
                    <h5><i class="fas fa-bolt me-2"></i>Acciones rápidas</h5>
                    <div class="d-grid gap-2">
                        <a href="variantes.php?id=<?= $producto['id'] ?>" class="btn-variantes text-center">
                            <i class="fas fa-layer-group me-2"></i>Gestionar Variantes
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-3 mt-2">
            <button type="submit" class="btn-guardar">
                <i class="fas fa-save me-2"></i>GUARDAR CAMBIOS
            </button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preview de imagen
    function previewImagen(input) {
        const preview  = document.getElementById('preview-img');
        const actual   = document.getElementById('imagen-actual');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (actual) actual.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

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