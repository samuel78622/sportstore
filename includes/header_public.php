<?php require_once 'auth.php'; ?>
<!-- PUBLIC HEADER/NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-4 navbar-sportstore">
    <a class="navbar-brand" href="/sportstore/index.php">SPORT<span>STORE</span></a>

    <div class="ms-auto d-flex align-items-center gap-3">
        <!-- Navigation links -->
        <a href="/sportstore/index.php" class="btn btn-outline-light">Inicio</a>
        <a href="/sportstore/catalogo.php" class="btn btn-outline-light">Catálogo</a>
        <a href="/sportstore/carrito.php" class="btn btn-outline-light">Carrito</a>

        <!-- User section -->
        <?php if (isset($_SESSION['usuario_id'])): ?>
                <span class="text-white"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <?php if (obtenerRol() === 'cliente'): ?>
                <a href="/sportstore/cliente/perfil.php" class="btn btn-dark btn-sm">Mi perfil</a>
            <?php else: ?>
                <a href="/sportstore/departamentos/dashboard.php" class="btn btn-dark btn-sm">Volver al panel</a>
            <?php endif; ?>
            <a href="/sportstore/logout.php" class="btn btn-dark btn-sm">Salir</a>
        <?php else: ?>
            <a href="/sportstore/login.php" class="btn btn-light btn-sm">Login</a>
            <a href="/sportstore/registro.php" class="btn btn-outline-light btn-sm">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>
