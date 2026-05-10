<?php
// ============================================
// DASHBOARD — DEPARTAMENTO DE VENTAS
// ============================================

require_once '../../includes/auth.php';
require_once '../../includes/funciones_orden.php';

soloLogueados();

// Verificar que sea vendedor
if (!esVendedor()) {
    header("Location: ../../index.php?error=sin_permiso");
    exit();
}

// Obtener datos del usuario actual
$usuario = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Ventas | SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>

        <nav class="sidebar-menu">
            <div class="menu-label">Bienvenido</div>
            <a href="index.php" class="menu-item active">
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
            <a href="promos.php" class="menu-item">
                <i class="fas fa-target"></i> Promos
            </a>
            <a href="../../logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </nav>
    </div>

    <!-- ════════════ CONTENIDO PRINCIPAL ════════════ -->
    <div class="main-content">

        <!-- ════ HEADER ════ -->
        <div class="admin-header">
            <h1>
                <i class="fas fa-chart-bar"></i> Dashboard de Ventas
            </h1>
            <div class="user-info">
                <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <!-- ════ CONTENIDO ════ -->
        <div class="container-fluid p-4">

            <!-- ════ TARJETAS RESUMEN ════ -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon success">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Ventas Realizadas</h6>
                                <h3>24</h3>
                                <small class="text-success">+5 desde ayer</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Monto Vendido</h6>
                                <h3>$8,450.00</h3>
                                <small class="text-info">Este mes</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Pedidos Pendientes</h6>
                                <h3>5</h3>
                                <small class="text-warning">Por procesar</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Clientes Atendidos</h6>
                                <h3>18</h3>
                                <small class="text-info">Este mes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ MENSAJE DE BIENVENIDA ════ -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Bienvenido al Departamento de Ventas</strong><br>
                            Aquí puedes gestionar tus pedidos, ver tus ventas, administrar clientes y aplicar promociones.
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
