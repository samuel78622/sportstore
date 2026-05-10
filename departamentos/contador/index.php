<?php
// ============================================
// DASHBOARD — DEPARTAMENTO DE CONTABILIDAD
// ============================================

require_once '../../includes/auth.php';
require_once '../../includes/funciones_reportes.php';

soloLogueados();

// Verificar que sea contador
if (!esContador()) {
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
    <title>Dashboard — Contabilidad | SportStore</title>
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
                <i class="fas fa-calculator"></i> Dashboard
            </a>

            <div class="menu-label">Reportes</div>
            <a href="ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="facturas.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
            <a href="ingresos.php" class="menu-item">
                <i class="fas fa-money-bill-wave"></i> Ingresos
            </a>
            <a href="gastos.php" class="menu-item">
                <i class="fas fa-chart-pie"></i> Gastos
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
                <i class="fas fa-calculator"></i> Dashboard de Contabilidad
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
                            <div class="stat-icon income">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Ingresos Totales</h6>
                                <h3>$45,230.00</h3>
                                <small class="text-success">+12% este mes</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon expense">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Gastos Totales</h6>
                                <h3>$12,450.00</h3>
                                <small class="text-danger">+5% este mes</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon profit">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Ganancia Neta</h6>
                                <h3>$32,780.00</h3>
                                <small class="text-success">+18% este mes</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon pending">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Pendientes</h6>
                                <h3>8</h3>
                                <small class="text-warning">Requieren atención</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ MENSAJE DE BIENVENIDA ════ -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Bienvenido al Departamento de Contabilidad</strong><br>
                            Desde aquí podrás ver reportes financieros, facturas, ingresos y gastos de la tienda.
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
