<?php
session_start();
require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/funciones_producto.php";

$conexion = conectar();

$categorias = $conexion->query(
    "SELECT * FROM categorias WHERE activo = 1 AND nombre IN ('Camisetas', 'Zapatos', 'Pantalonetas', 'Accesorios') ORDER BY FIELD(nombre, 'Camisetas', 'Zapatos', 'Pantalonetas', 'Accesorios')"
)->fetchAll();

$productos_por_categoria = [];
foreach ($categorias as $categoria) {
    $stmt = $conexion->prepare(
        "SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY fecha_creacion DESC"
    );
    $stmt->execute([$categoria['id']]);
    $productos_por_categoria[$categoria['id']] = $stmt->fetchAll();
}

$categoryLabelOverrides = [
    'Zapatos' => 'Zapatillas'
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SportStore - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Consolidated Styles -->
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/global.css" rel="stylesheet">
    <link href="assets/css/public.css" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . "/includes/header_public.php"; ?>

    <!-- ============================================
    HERO
    ============================================ -->
    <section class="hero">
        <div>
            <h1>ENTRENA SIN LÍMITES</h1>
            <p>Descubre la mejor ropa deportiva para potenciar tu rendimiento</p>
            <a href="catalogo.php" class="btn btn-main btn-lg">Comprar ahora</a>
        </div>
    </section>

    <!-- ============================================
    PRODUCTOS POR CATEGORÍA
    ============================================ -->
    <div class="container mt-5">
        <?php foreach ($categorias as $categoria):
            $productos = $productos_por_categoria[$categoria['id']] ?? [];
            $titulo_categoria = $categoryLabelOverrides[$categoria['nombre']] ?? $categoria['nombre'];
        ?>
            <section class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="fw-bold mb-0"><?= htmlspecialchars($titulo_categoria) ?></h2>
                        <small class="text-muted">Productos de <?= htmlspecialchars(mb_strtolower($titulo_categoria)) ?></small>
                    </div>
                    <a href="catalogo.php?categoria=<?= urlencode($categoria['id']) ?>" class="btn btn-outline-dark">
                        Ver todo
                    </a>
                </div>

                <?php if (!empty($productos)): ?>
                    <div class="row g-4">
                        <?php foreach ($productos as $producto):
                            $precio = obtenerPrecioMinimoProducto($producto['id']);
                            $stock = obtenerStockProducto($producto['id']);
                        ?>
                            <div class="col-md-3">
                                <div class="producto-card">
                                    <img src="uploads/productos/<?= !empty($producto['imagen_principal']) ? htmlspecialchars($producto['imagen_principal']) : 'placeholder.png' ?>"
                                        class="producto-img">

                                    <h5 class="mt-3"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                    <p class="text-success fw-bold">
                                        <?= $precio !== null
                                            ? '$' . number_format($precio, 0, ',', '.')
                                            : '<span class="text-muted">Precio no disponible</span>' ?>
                                    </p>

                                    <div class="d-grid gap-2">
                                        <a href="producto.php?id=<?= $producto['id'] ?>" class="btn btn-outline-dark">
                                            Ver producto
                                        </a>

                                        <?php if ($stock > 0): ?>
                                            <form action="carrito.php" method="POST">
                                                <input type="hidden" name="id_producto" value="<?= $producto['id'] ?>">
                                                <input type="hidden" name="cantidad" value="1">

                                                <button type="submit" name="agregar" class="btn btn-dark">
                                                    Agregar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-dark" disabled>
                                                Sin stock
                                            </button>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['usuario_id'])): ?>
                                            <a href="wishlist_add.php?producto_id=<?= $producto['id'] ?>" class="btn btn-outline-danger">
                                                ♡ Wishlist
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-outline-danger">
                                                ♡ Wishlist
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        No hay productos disponibles en esta categoría.
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- ============================================
    FOOTER
    ============================================ -->
    <?php require_once __DIR__ . "/includes/footer_public.php"; ?>

</body>

</html>