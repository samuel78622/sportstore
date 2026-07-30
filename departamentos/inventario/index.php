<?php
// ============================================
// DASHBOARD — DEPARTAMENTO DE INVENTARIO
// ============================================

require_once '../../includes/auth.php';
require_once '../../includes/funciones_inventario.php';

soloLogueados();

// Verificar que sea inventario
if (!esInventario()) {
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
    <title>Dashboard — Inventario | SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../admin/assets/css/admin.css">
</head>

<body>

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>

        <nav class="sidebar-menu">
            <div class="menu-label">Bienvenido</div>
            <a href="index.php" class="menu-item active">
                <i class="fas fa-boxes-stacked"></i> Dashboard
            </a>

            <div class="menu-label">Inventario</div>
            <a href="stock_general.php" class="menu-item">
                <i class="fas fa-warehouse"></i> Stock General
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="alertas.php" class="menu-item">
                <i class="fas fa-triangle-exclamation"></i> Alertas
            </a>
            <a href="nuevo_movimiento.php" class="menu-item">
                <i class="fas fa-plus-circle"></i> Nuevo Movimiento
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
                <i class="fas fa-boxes-stacked"></i> Dashboard de Inventario
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
                            <div class="stat-icon info">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Stock Total</h6>
                                <h3>1,245</h3>
                                <small class="text-info">Unidades en bodega</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Stock Bajo</h6>
                                <h3>12</h3>
                                <small class="text-danger">Requieren reorden</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon success">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Movimientos Hoy</h6>
                                <h3>34</h3>
                                <small class="text-success">Entradas/Salidas</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon warning">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Últimas Auditorías</h6>
                                <h3>3</h3>
                                <small class="text-warning">Esta semana</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ MENSAJE DE BIENVENIDA ════ -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-primary d-flex align-items-center gap-2" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Bienvenido al Departamento de Inventario</strong><br>
                            Desde aquí podrás gestionar el stock, ver movimientos, alertas de productos con bajo stock y realizar auditorías de inventario.
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

