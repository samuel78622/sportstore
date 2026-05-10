<?php
// ============================================
// GESTIÓN DE FACTURAS — DEPARTAMENTO CONTABILIDAD
// ============================================

require_once '../../includes/auth.php';
require_once '../../includes/funciones_factura.php';

soloLogueados();

if (!esContador()) {
    header("Location: ../../index.php?error=sin_permiso");
    exit();
}

$usuario = usuarioActual();
$facturas = listarFacturas(100);

// Si solicitan descargar una factura
if (isset($_GET['accion']) && $_GET['accion'] === 'descargar' && isset($_GET['orden_id'])) {
    $orden_id = intval($_GET['orden_id']);
    generarPDFFactura($orden_id, true);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Facturas — Contabilidad | SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .tabla-facturas { font-size: 0.9rem; }
        .tabla-facturas th { background: #111; color: white; }
        .estado-badge { font-size: 0.75rem; padding: 4px 8px; }
        .btn-pequeño { padding: 4px 10px; font-size: 0.85rem; }
        .acciones-celda { display: flex; gap: 5px; }
    </style>
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
            <a href="facturas.php" class="menu-item active">
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
                <i class="fas fa-file-invoice"></i> Gestión de Facturas
            </h1>
            <div class="user-info">
                <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <!-- ════ CONTENIDO ════ -->
        <div class="container-fluid p-4">

            <!-- ════ RESUMEN RÁPIDO ════ -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?= count($facturas) ?></h3>
                            <p class="text-muted mb-0">Facturas Emitidas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?php
                                $total = 0;
                                foreach ($facturas as $f) {
                                    $total += $f['total'];
                                }
                                echo '$' . number_format($total, 2);
                            ?></h3>
                            <p class="text-muted mb-0">Monto Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?php
                                $entregadas = array_filter($facturas, fn($f) => $f['estado'] === 'entregado');
                                echo count($entregadas);
                            ?></h3>
                            <p class="text-muted mb-0">Órdenes Entregadas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ TABLA DE FACTURAS ════ -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Listado de Facturas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($facturas)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay facturas registradas aún.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover tabla-facturas">
                                <thead>
                                    <tr>
                                        <th>Número de Factura</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($facturas as $factura): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($factura['numero_factura']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($factura['cliente']) ?></td>
                                            <td>
                                                <strong>$<?= number_format($factura['total'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $estado = $factura['estado'];
                                                $clases = [
                                                    'pendiente' => 'bg-warning text-dark',
                                                    'empacado' => 'bg-info text-white',
                                                    'enviado' => 'bg-success text-white',
                                                    'entregado' => 'bg-primary text-white',
                                                    'cancelado' => 'bg-danger text-white'
                                                ];
                                                $clase = $clases[$estado] ?? 'bg-secondary text-white';
                                                ?>
                                                <span class="badge <?= $clase ?> estado-badge">
                                                    <?= ucfirst($estado) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($factura['fecha'])) ?></td>
                                            <td>
                                                <div class="acciones-celda">
                                                    <a href="?accion=descargar&orden_id=<?= $factura['orden_id'] ?>" 
                                                       class="btn btn-sm btn-primary btn-pequeño" 
                                                       title="Descargar PDF">
                                                        <i class="fas fa-download"></i> PDF
                                                    </a>
                                                    <a href="detalle.php?factura=<?= htmlspecialchars($factura['numero_factura']) ?>" 
                                                       class="btn btn-sm btn-info btn-pequeño"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
