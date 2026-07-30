<?php
session_start();
require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/funciones_producto.php";

// 🔌 Conexión PDO
$conexion = conectar();

// Buscador
$buscar = $_GET['buscar'] ?? '';

if (!empty($buscar)) {
    $sql = "SELECT * FROM productos WHERE nombre LIKE :buscar AND activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute(['buscar' => "%$buscar%"]);
} else {
    $sql = "SELECT * FROM productos WHERE activo = 1";
    $stmt = $conexion->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Catálogo - SportStore</title>
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

    <!-- BUSCADOR -->
    <div class="container mt-4">
        <form method="GET" class="d-flex mb-4">
            <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar productos..."
                value="<?= htmlspecialchars($buscar) ?>">
            <button class="btn btn-dark">Buscar</button>
        </form>

        <div class="row g-4">

            <?php if ($stmt->rowCount() > 0): ?>

                <?php while ($producto = $stmt->fetch()):
                    $precio = obtenerPrecioMinimoProducto($producto['id']);
                    $stock = obtenerStockProducto($producto['id']);
                    ?>

                    <div class="col-md-3">
                        <div class="producto-card">

                            <!-- Imagen -->
                            <img src="uploads/productos/<?= !empty($producto['imagen_principal']) ? htmlspecialchars($producto['imagen_principal']) : 'placeholder.png' ?>"
                                class="producto-img">

                            <!-- Info -->
                            <h5 class="mt-3"><?= htmlspecialchars($producto['nombre']) ?></h5>

                            <p class="text-success fw-bold">
                                <?= $precio !== null ? '$' . number_format($precio, 0, ',', '.') : '<span class="text-muted">Precio no disponible</span>' ?>
                            </p>

                            <!-- Stock -->
                            <?php if ($stock > 0): ?>
                                <p class="text-success">Disponible</p>
                            <?php else: ?>
                                <p class="text-danger">Agotado</p>
                            <?php endif; ?>

                            <!-- Botones -->
                            <div class="d-grid gap-2">

                                <a href="producto.php?id=<?= $producto['id'] ?>" class="btn btn-outline-dark">
                                    Ver producto
                                </a>

                                <?php if ($stock > 0): ?>
                                    <form action="carrito.php" method="POST">
                                        <input type="hidden" name="id_producto" value="<?= $producto['id'] ?>">
                                        <input type="hidden" name="cantidad" value="1">
                                        <button type="submit" name="agregar" class="btn btn-custom">
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

                <?php endwhile; ?>

            <?php else: ?>
                <p>No hay productos disponibles</p>
            <?php endif; ?>

        </div>
    </div>

    <?php require_once __DIR__ . "/includes/footer_public.php"; ?>

</body>

</html>