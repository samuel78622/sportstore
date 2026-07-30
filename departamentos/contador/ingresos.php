<?php
// ============================================
// INGRESOS — DEPARTAMENTO DE CONTABILIDAD
// ============================================

require_once '../../includes/auth.php';

soloLogueados();

if (!esContador()) {
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
    <title>Ingresos — Contabilidad | SportStore</title>
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
            <a href="index.php" class="menu-item">
                <i class="fas fa-calculator"></i> Dashboard
            </a>

            <div class="menu-label">Reportes</div>
            <a href="ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="facturas.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
            <a href="ingresos.php" class="menu-item active">
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
                <i class="fas fa-money-bill-wave"></i> Reporte de Ingresos
            </h1>
            <div class="user-info">
                <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <!-- ════ CONTENIDO ════ -->
        <div class="container-fluid p-4">

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon income">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Ingresos Este Mes</h6>
                                <h3>$45,230.00</h3>
                                <small class="text-success">+12% desde mes anterior</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Transacciones</h6>
                                <h3>124</h3>
                                <small class="text-info">Procesadas correctamente</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="stat-icon primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h6>Promedio por Venta</h6>
                                <h3>$364.75</h3>
                                <small class="text-info">Valor promedio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Detalle de Ingresos</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Aquí irá el desglose detallado de ingresos por período, categoría y método de pago.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

