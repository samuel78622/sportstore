<?php
session_start();
require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/funciones_producto.php";

$conexion = conectar();

$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC LIMIT 4";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SportStore - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="assets/css/inicio.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark px-4">
        <a class="navbar-brand" href="#">SPORT<span>STORE</span></a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <!-- Botones siempre visibles -->
            <a href="catalogo.php" class="btn btn-outline-light">Catálogo</a>
            <a href="carrito.php" class="btn btn-outline-light">Carrito</a>

            <!-- Usuario logueado -->
            <?php if (isset($_SESSION['usuario_id'])): ?>

                <!-- Nombre del usuario -->
                <span class="text-white">
                     <?= htmlspecialchars($_SESSION['nombre']) ?>
                </span>

                <!-- Perfil -->
                <a href="cliente/perfil.php" class="btn btn-dark">
                     Mi perfil
                </a>

                <!-- Logout -->
                <a href="logout.php" class="btn btn-dark">
                    Salir
                </a>

            <?php else: ?>

                <!-- Login -->
                <a href="login.php" class="btn btn-light">Login</a>

            <?php endif; ?>

        </div>
    </nav>

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
    PRODUCTOS DESTACADOS
    ============================================ -->
    <div class="container mt-5">
        <h2 class="mb-4 fw-bold">Productos destacados</h2>

        <div class="row g-4">

            <?php while ($producto = $resultado->fetch()): 
                // Obtener precio mínimo del producto
                $precio = obtenerPrecioMinimoProducto($producto['id']);
            ?>

                <div class="col-md-3">
                    <div class="producto-card">

                        <!-- Imagen -->
                        <img src="uploads/productos/<?= !empty($producto['imagen_principal']) ? htmlspecialchars($producto['imagen_principal']) : 'placeholder.png' ?>"
                            class="producto-img">

                        <!-- Nombre -->
                        <h5 class="mt-3">
                            <?= htmlspecialchars($producto['nombre']) ?>
                        </h5>

                        <!-- Precio -->
                        <p class="text-success fw-bold">
                            <?= $precio !== null 
                                ? '$' . number_format($precio, 0, ',', '.') 
                                : '<span class="text-muted">Precio no disponible</span>' ?>
                        </p>

                        <!-- Botones -->
                        <div class="d-grid gap-2">

                            <a href="producto.php?id=<?= $producto['id'] ?>" 
                               class="btn btn-outline-dark">
                                Ver producto
                            </a>

                            <form action="carrito.php" method="POST">
                                <input type="hidden" name="id_producto" value="<?= $producto['id'] ?>">
                                <input type="hidden" name="cantidad" value="1">

                                <button type="submit" name="agregar" class="btn btn-dark">
                                   Agregar
                                </button>
                            </form>

                        </div>

                    </div>
                </div>

            <?php endwhile; ?>

        </div>
    </div>

    <!-- ============================================
    FOOTER
    ============================================ -->
    <footer class="footer-custom text-center mt-5 p-4">
        <p>&copy; <?= date("Y") ?> SportStore - Todos los derechos reservados</p>
    </footer>

</body>

</html>