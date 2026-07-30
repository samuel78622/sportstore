<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_factura.php';

soloAdmin();

$usuario = usuarioActual();
$facturas = [];

// Obtener parámetros de filtro
$dia = isset($_GET['dia']) && $_GET['dia'] !== '' ? intval($_GET['dia']) : null;
$mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? intval($_GET['mes']) : null;
$ano = isset($_GET['ano']) && $_GET['ano'] !== '' ? intval($_GET['ano']) : null;
$hora = isset($_GET['hora']) && $_GET['hora'] !== '' ? intval($_GET['hora']) : null;

// Si hay filtros, obtener facturas filtradas
if ($dia !== null || $mes !== null || $ano !== null) {
    $facturas = listarFacturasFiltradasPorFecha($dia, $mes, $ano, $hora);
} else {
    // Por defecto, mostrar facturas del mes actual
    $mes = date('m');
    $ano = date('Y');
    $facturas = listarFacturasFiltradasPorFecha(null, $mes, $ano, null);
}

// Si solicitan descargar una factura
if (isset($_GET['accion']) && $_GET['accion'] === 'descargar' && isset($_GET['orden_id'])) {
    $orden_id = intval($_GET['orden_id']);
    generarPDFFactura($orden_id, true);
    exit();
}

// Calcular totales
$total_facturas = count($facturas);
$monto_total = array_sum(array_column($facturas, 'total'));
$entregadas = count(array_filter($facturas, fn($f) => $f['estado'] === 'entregado'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Facturas — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .tabla-facturas { font-size: 0.9rem; }
        .tabla-facturas th { background: #111; color: white; }
        .estado-badge { font-size: 0.75rem; padding: 4px 8px; }
        .btn-pequeño { padding: 4px 10px; font-size: 0.85rem; }
        .acciones-celda { display: flex; gap: 5px; flex-wrap: wrap; }
        .filtros-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .filtro-grupo { margin-bottom: 15px; }
        .filtro-grupo label { font-weight: 600; margin-bottom: 5px; display: block; }
        .filtro-grupo input, .filtro-grupo select { width: 100%; }
    </style>
</head>

<body>

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>

        <nav class="sidebar-menu">
            <div class="menu-label">Principal</div>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="menu-label">Catálogo</div>
            <a href="../productos/index.php" class="menu-item">
                <i class="fas fa-shirt"></i> Productos
            </a>
            <a href="../productos/crear.php" class="menu-item">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>

            <div class="menu-label">Inventario</div>
            <a href="../inventario/index.php" class="menu-item">
                <i class="fas fa-boxes-stacked"></i> Stock General
            </a>
            <a href="../inventario/movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="../inventario/alertas.php" class="menu-item">
                <i class="fas fa-triangle-exclamation"></i> Alertas
            </a>

            <div class="menu-label">Ventas</div>
            <a href="../pedidos/index.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Pedidos
            </a>
            <a href="index.php" class="menu-item active">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
            <a href="../cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>

            <div class="menu-label">Usuarios</div>
            <a href="../clientes/index.php" class="menu-item">
                <i class="fas fa-users"></i> Clientes
            </a>

            <div class="menu-label">Reportes</div>
            <a href="../reportes/ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="../reportes/productos.php" class="menu-item">
                <i class="fas fa-star"></i> Más vendidos
            </a>
            <a href="../reportes/inventario.php" class="menu-item">
                <i class="fas fa-warehouse"></i> Inventario
            </a>

            <a href="../usuarios/index.php" class="menu-item">
                <i class="fas fa-warehouse"></i> Roles y permisos
            </a>
        </nav>

        <div class="sidebar-footer">
            <span style="color:#666; font-size:12px">
                👤 <?= htmlspecialchars($_SESSION['nombre']) ?>
            </span><br><br>
            <a href="../../logout.php">
                <i class="fas fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <!-- ════════════ CONTENIDO ════════════ -->
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
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?= $total_facturas ?></h3>
                            <p class="text-muted mb-0">Facturas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3>$<?= number_format($monto_total, 2) ?></h3>
                            <p class="text-muted mb-0">Monto Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?= $entregadas ?></h3>
                            <p class="text-muted mb-0">Entregadas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3><?= count(array_filter($facturas, fn($f) => $f['estado'] === 'pendiente')) ?></h3>
                            <p class="text-muted mb-0">Pendientes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════ FILTROS ════ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtrar Facturas</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="filtro-grupo">
                                <label>Año</label>
                                <select name="ano" class="form-control">
                                    <option value="">Todos los años</option>
                                    <?php
                                    $ano_actual = date('Y');
                                    for ($y = $ano_actual; $y >= $ano_actual - 5; $y--) {
                                        $selected = $ano == $y ? 'selected' : '';
                                        echo "<option value='$y' $selected>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="filtro-grupo">
                                <label>Mes</label>
                                <select name="mes" class="form-control">
                                    <option value="">Todos los meses</option>
                                    <?php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                                        4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                                        10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    foreach ($meses as $m => $nombre) {
                                        $selected = $mes == $m ? 'selected' : '';
                                        echo "<option value='$m' $selected>$nombre</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="filtro-grupo">
                                <label>Día</label>
                                <select name="dia" class="form-control">
                                    <option value="">Todos</option>
                                    <?php
                                    for ($d = 1; $d <= 31; $d++) {
                                        $selected = $dia == $d ? 'selected' : '';
                                        echo "<option value='$d' $selected>$d</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="filtro-grupo">
                                <label>Hora</label>
                                <select name="hora" class="form-control">
                                    <option value="">Todas</option>
                                    <?php
                                    for ($h = 0; $h < 24; $h++) {
                                        $selected = $hora == $h ? 'selected' : '';
                                        $h_format = str_pad($h, 2, '0', STR_PAD_LEFT);
                                        echo "<option value='$h' $selected>{$h_format}:00 - {$h_format}:59</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="filtro-grupo">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3">
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Limpiar filtros
                        </a>
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
                            <i class="fas fa-info-circle"></i> No hay facturas que coincidan con los filtros especificados.
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
                                        <th>Fecha y Hora</th>
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
                                            <td><?= date('d/m/Y H:i', strtotime($factura['fecha'])) ?></td>
                                            <td>
                                                <div class="acciones-celda">
                                                    <a href="?accion=descargar&orden_id=<?= $factura['orden_id'] ?>&<?= http_build_query(['dia' => $dia, 'mes' => $mes, 'ano' => $ano, 'hora' => $hora]) ?>"
                                                       class="btn btn-sm btn-primary btn-pequeño"
                                                       title="Descargar PDF">
                                                        <i class="fas fa-download"></i> PDF
                                                    </a>
                                                    <a href="../pedidos/detalle.php?id=<?= $factura['orden_id'] ?>"
                                                       class="btn btn-sm btn-info btn-pequeño"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i> Ver
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
