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
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body style="background:#f5f5f5;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand" href="index.php">SPORT<span>STORE</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
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
                <?php if (!empty($producto['imagen_principal'])): ?>
                    <img src="uploads/productos/<?php echo htmlspecialchars($producto['imagen_principal']); ?>" class="img-fluid rounded"
                        style="max-height:400px;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/400" class="img-fluid">
                <?php endif; ?>
            </div>

            <!-- Información -->
            <div class="col-md-6">
                <h2 class="fw-bold"><?php echo $producto['nombre']; ?></h2>

                <p class="text-muted"><?php echo $producto['descripcion'] ?? 'Sin descripción'; ?></p>

                <h3 class="text-success fw-bold mb-3">
                    <?php if ($precio !== null): ?>
                        $<?php echo number_format($precio, 0, ',', '.'); ?>
                    <?php else: ?>
                        <span class="text-muted">Precio no disponible</span>
                    <?php endif; ?>
                </h3>

                <p>
                    <strong>Stock:</strong>
                    <?php if ($stock > 0): ?>
                        <span class="text-success">Disponible (<?php echo $stock; ?>)</span>
                    <?php else: ?>
                        <span class="text-danger">Agotado</span>
                    <?php endif; ?>
                </p>

                <hr>

                <?php if ($stock > 0): ?>
                    <form action="carrito.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" value="1" min="1" max="<?php echo $stock; ?>"
                                class="form-control" style="width:120px;">
                        </div>

                        <button type="submit" name="agregar" class="btn btn-dark w-100">
                            🛒 Agregar al carrito
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>
                        Producto agotado
                    </button>
                <?php endif; ?>

                <br><br>

                <a href="catalogo.php" class="btn btn-outline-secondary w-100">
                    ⬅ Volver al catálogo
                </a>
            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>