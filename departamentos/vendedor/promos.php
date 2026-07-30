<?php
// ============================================
// PROMOS — DEPARTAMENTO DE VENTAS
// ============================================

require_once '../../includes/auth.php';

soloLogueados();

if (!esVendedor()) {
    header("Location: ../../index.php?error=sin_permiso");
    exit();
}

$usuario = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promos — Ventas | SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../admin/assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>
        <nav class="sidebar-menu">
            <div class="menu-label">Bienvenido</div>
            <a href="index.php" class="menu-item">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
            <div class="menu-label">Ventas</div>
            <a href="mis_pedidos.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Mis Pedidos
            </a>
            <a href="mis_ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Mis Ventas
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-user-tie"></i> Clientes
            </a>
            <div class="menu-label">Herramientas</div>
            <a href="cupones.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>
            <a href="promos.php" class="menu-item active">
                <i class="fas fa-target"></i> Promos
            </a>
            <a href="../../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </nav>
    </div>
    <div class="main-content">
        <div class="admin-header">
            <h1><i class="fas fa-target"></i> Gestión de Promociones</h1>
            <div class="user-info">
                <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Promociones Vigentes</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Aquí podrás ver las promociones activas que puedes ofertar a tus clientes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

