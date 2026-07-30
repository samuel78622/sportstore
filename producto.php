<?php
session_start();
require_once "includes/conexion.php";
require_once "includes/funciones_producto.php";

$conexion = conectar();

// Validar ID
if (!isset($_GET['id'])) {
    echo "Producto no encontrado";
    exit();
}

$id = intval($_GET['id']);

// Obtener producto
$sql = "SELECT * FROM productos WHERE id = ?";
$resultado = $conexion->prepare($sql);
$resultado->execute([$id]);
$producto = $resultado->fetch();

$precio = obtenerPrecioMinimoProducto($id);
$stock = obtenerStockProducto($id);
$variantes = obtenerVariantes($id);
$tallas = obtenerTallas($id);
$imagenes_adicionales = obtenerImagenesProducto($id);
$selected_talla = $tallas[0]['talla'] ?? null;
$colores = [];
$selected_variant = null;

if ($selected_talla) {
    $colores = obtenerColoresPorTalla($id, $selected_talla);
}

if (!empty($colores)) {
    $selected_variant = $colores[0];
}

if (!$selected_variant && !empty($variantes)) {
    $selected_variant = $variantes[0];
}

if (!$selected_variant) {
    $selected_variant = null;
}

$mostrar_stock = $selected_variant ? $selected_variant['stock'] : $stock;
$mostrar_precio = $selected_variant ? $selected_variant['precio'] : $precio;

if (!$producto) {
    echo "Producto no existe";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $producto['nombre']; ?> - SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/global.css" rel="stylesheet">
    <link href="assets/css/public.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/producto.css">
</head>

<body style="background:#f5f5f5;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand" href="index.php">SPORT<span>STORE</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-outline-light">Inicio</a>
            <a href="catalogo.php" class="btn btn-outline-light">Catálogo</a>
            <a href="carrito.php" class="btn btn-outline-light">🛒 Carrito</a>
            <?php if (isset($_SESSION['usuario'])): ?>
                <span class="text-white">👋 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="logout.php" class="btn btn-danger">Salir</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">

        <div class="row bg-white p-4 rounded shadow">

            <!-- Imagen -->
            <div class="col-md-6 text-center">
                <?php
                $carouselImages = [];
                if (!empty($producto['imagen_principal'])) {
                    $carouselImages[] = $producto['imagen_principal'];
                }
                foreach ($imagenes_adicionales as $imagen_extra) {
                    if (!empty($imagen_extra['imagen']) && !in_array($imagen_extra['imagen'], $carouselImages, true)) {
                        $carouselImages[] = $imagen_extra['imagen'];
                    }
                }
                ?>
                <div id="productoCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php if (!empty($carouselImages)): ?>
                            <?php foreach ($carouselImages as $index => $imagen): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/productos/<?php echo htmlspecialchars($imagen); ?>" class="d-block mx-auto img-fluid rounded carousel-producto-img">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/400" class="d-block mx-auto img-fluid rounded carousel-producto-img">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($carouselImages) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información -->
            <div class="col-md-6">
                <h2 class="fw-bold"><?php echo $producto['nombre']; ?></h2>

                <p class="text-muted"><?php echo $producto['descripcion'] ?? 'Sin descripción'; ?></p>

                <h3 class="text-success fw-bold mb-3" id="productoPrecio">
                    <?php if ($mostrar_precio !== null): ?>
                        $<?php echo number_format($mostrar_precio, 0, ',', '.'); ?>
                    <?php else: ?>
                        <span class="text-muted">Precio no disponible</span>
                    <?php endif; ?>
                </h3>

                <p>
                    <strong>Stock:</strong>
                    <span id="productoStock">
                        <?php if ($mostrar_stock > 0): ?>
                            <span class="text-success">Disponible (<?php echo $mostrar_stock; ?>)</span>
                        <?php else: ?>
                            <span class="text-danger">Agotado</span>
                        <?php endif; ?>
                    </span>
                </p>

                <hr>

                <?php if ($mostrar_stock > 0): ?>
                    <form action="carrito.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                        <input type="hidden" name="variante_id" id="variante_id" value="<?php echo $selected_variant['id'] ?? ''; ?>">

                        <?php if (!empty($tallas)): ?>
                            <div class="mb-3">
                                <label class="form-label">Talla</label>
                                <select id="selectTalla" name="talla" class="form-select">
                                    <?php foreach ($tallas as $talla): ?>
                                        <option value="<?= htmlspecialchars($talla['talla']) ?>" <?= ($selected_variant && $selected_variant['talla'] == $talla['talla']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($talla['talla']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($colores)): ?>
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <select id="selectColor" name="color" class="form-select">
                                    <?php foreach ($colores as $color): ?>
                                        <option value="<?= htmlspecialchars($color['color']) ?>" <?= ($selected_variant && $selected_variant['color'] == $color['color']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($color['color']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" value="1" min="1" max="<?php echo max(1, $mostrar_stock); ?>" class="form-control" style="width:120px;" id="cantidadInput">
                        </div>

                        <button type="submit" name="agregar" class="btn btn-dark w-100">
                            Agregar al carrito
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>
                        Producto agotado
                    </button>
                <?php endif; ?>

                <br><br>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="wishlist_add.php?producto_id=<?= $producto['id'] ?>" class="btn btn-outline-danger w-100 mb-2">
                        ♡ Agregar a Wishlist
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-danger w-100 mb-2">
                        ♡ Wishlist
                    </a>
                <?php endif; ?>

                <a href="catalogo.php" class="btn btn-outline-secondary w-100">
                     Volver al catálogo
                </a>
            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const variantes = <?php echo json_encode($variantes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const selectTalla = document.getElementById('selectTalla');
        const selectColor = document.getElementById('selectColor');
        const varianteIdField = document.getElementById('variante_id');
        const precioField = document.getElementById('productoPrecio');
        const stockField = document.getElementById('productoStock');
        const cantidadInput = document.getElementById('cantidadInput');

        function actualizarColores() {
            if (!selectTalla || !selectColor) return;
            const talla = selectTalla.value;
            const opciones = variantes.filter(v => v.talla === talla && v.stock > 0);
            selectColor.innerHTML = opciones.map(v => {
                return `<option value="${v.color}">${v.color}</option>`;
            }).join('');
            if (opciones.length > 0) {
                selectColor.disabled = false;
                selectColor.value = opciones[0].color;
            } else {
                selectColor.disabled = true;
                selectColor.innerHTML = '<option value="">Sin colores disponibles</option>';
            }
            actualizarVariante();
        }

        function actualizarVariante() {
            if (!varianteIdField) return;
            const talla = selectTalla ? selectTalla.value : null;
            const color = selectColor ? selectColor.value : null;
            let variante = null;

            if (talla && color) {
                variante = variantes.find(v => v.talla === talla && v.color === color);
            }

            if (!variante) {
                variante = variantes.find(v => v.talla === talla && v.stock > 0) || variantes[0] || null;
            }

            if (variante) {
                varianteIdField.value = variante.id;
                if (precioField) {
                    precioField.textContent = '$' + Number(variante.precio).toLocaleString('es-CO', { minimumFractionDigits: 0 });
                }
                if (stockField) {
                    stockField.innerHTML = variante.stock > 0 ? `<span class="text-success">Disponible (${variante.stock})</span>` : `<span class="text-danger">Agotado</span>`;
                }
                if (cantidadInput) {
                    cantidadInput.max = variante.stock;
                    if (Number(cantidadInput.value) > variante.stock) {
                        cantidadInput.value = variante.stock > 0 ? variante.stock : 1;
                    }
                }
            }
        }

        if (selectTalla) {
            selectTalla.addEventListener('change', actualizarColores);
        }
        if (selectColor) {
            selectColor.addEventListener('change', actualizarVariante);
        }

        document.addEventListener('DOMContentLoaded', () => {
            actualizarColores();
        });
    </script>
</body>

</html>