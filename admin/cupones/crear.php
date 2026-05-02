<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_cupon.php';

soloAdmin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo           = trim($_POST['codigo'] ?? '');
    $tipo             = $_POST['tipo'] ?? '';
    $descuento        = $_POST['descuento'] ?? 0;
    $usos_maximos     = $_POST['usos_maximos'] ?? 1;
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

    if (empty($codigo) || empty($tipo) || empty($descuento)) {
        $error = 'El código, tipo y descuento son obligatorios';
    } else {
        $resultado = crearCupon(
            $codigo,
            $tipo,
            $descuento,
            $usos_maximos,
            $fecha_vencimiento
        );

        if ($resultado['exito']) {
            header("Location: index.php?success=cupon_creado");
            exit();
        } else {
            $error = $resultado['mensaje'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cupón — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
     <link rel="stylesheet" href="../assets/css/cupones/crear.css">
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

    <div class="page-header">
        <div>
            <h1><i class="fas fa-plus me-2"></i>Nuevo Cupón</h1>
            <small class="text-muted">Crea un nuevo código de descuento</small>
        </div>
        <a href="index.php" class="btn-cancelar">← Volver</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-circle-exclamation me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row g-4">

            <!-- Columna izquierda -->
            <div class="col-md-8">

                <!-- Tipo de descuento -->
                <div class="card-section">
                    <h5><i class="fas fa-percentage me-2"></i>Tipo de descuento</h5>

                    <div class="tipo-options">
                        <label class="tipo-option selected" id="opt-porcentaje">
                            <input type="radio" name="tipo" value="porcentaje" checked>
                            <div class="tipo-icon">%</div>
                            <div class="tipo-label">Porcentaje</div>
                            <div class="tipo-desc">Descuento en % sobre el total</div>
                        </label>

                        <label class="tipo-option" id="opt-monto">
                            <input type="radio" name="tipo" value="monto_fijo">
                            <div class="tipo-icon">$</div>
                            <div class="tipo-label">Monto fijo</div>
                            <div class="tipo-desc">Descuento en valor fijo</div>
                        </label>
                    </div>
                </div>

                <!-- Datos del cupón -->
                <div class="card-section">
                    <h5><i class="fas fa-tag me-2"></i>Datos del cupón</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Código <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="codigo"
                                id="inputCodigo"
                                class="form-control"
                                placeholder="Ej: VERANO20"
                                value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>"
                                style="text-transform:uppercase; font-weight:700; letter-spacing:2px"
                                oninput="this.value = this.value.toUpperCase(); actualizarPreview()"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" id="label-descuento">
                                Descuento (%) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" id="simbolo-descuento">%</span>
                                <input
                                    type="number"
                                    name="descuento"
                                    id="inputDescuento"
                                    class="form-control"
                                    placeholder="Ej: 20"
                                    min="0"
                                    step="0.01"
                                    value="<?= htmlspecialchars($_POST['descuento'] ?? '') ?>"
                                    oninput="actualizarPreview()"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Máximo de usos <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                name="usos_maximos"
                                class="form-control"
                                placeholder="Ej: 100"
                                min="1"
                                value="<?= htmlspecialchars($_POST['usos_maximos'] ?? '100') ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de vencimiento</label>
                            <input
                                type="date"
                                name="fecha_vencimiento"
                                id="inputFecha"
                                class="form-control"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars($_POST['fecha_vencimiento'] ?? '') ?>"
                                oninput="actualizarPreview()"
                            >
                            <small class="text-muted">Déjalo vacío para que no expire nunca</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna derecha — Preview -->
            <div class="col-md-4">
                <div class="card-section">
                    <h5><i class="fas fa-eye me-2"></i>Vista previa</h5>

                    <div class="cupon-preview">
                        <div class="preview-label">⚡ SportStore</div>
                        <div class="preview-codigo" id="prev-codigo">CODIGO</div>
                        <div class="preview-descuento" id="prev-descuento">0%</div>
                        <div class="preview-tipo" id="prev-tipo">de descuento en tu compra</div>
                        <div class="preview-vence" id="prev-vence">Sin fecha de vencimiento</div>
                    </div>

                    <!-- Tips -->
                    <div style="margin-top:20px">
                        <p style="font-size:13px; font-weight:700; color:#111; margin-bottom:10px">
                            💡 Tips para el código
                        </p>
                        <ul style="font-size:12px; color:#888; padding-left:15px; line-height:1.8">
                            <li>Usa solo letras y números</li>
                            <li>Evita caracteres especiales</li>
                            <li>Máximo 20 caracteres</li>
                            <li>Ej: VERANO20, PROMO10K</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- Botones -->
        <div class="d-flex gap-3 mt-2">
            <button type="submit" class="btn-guardar">
                <i class="fas fa-check me-2"></i>CREAR CUPÓN
            </button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Selección de tipo ──
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.tipo-option').forEach(o => o.classList.remove('selected'));
            this.closest('.tipo-option').classList.add('selected');

            const esPorcentaje = this.value === 'porcentaje';
            document.getElementById('simbolo-descuento').textContent = esPorcentaje ? '%' : '$';
            document.getElementById('label-descuento').innerHTML =
                `Descuento (${esPorcentaje ? '%' : '$'}) <span class="text-danger">*</span>`;

            actualizarPreview();
        });
    });

    // ── Preview en tiempo real ──
    function actualizarPreview() {
        const codigo    = document.getElementById('inputCodigo').value || 'CODIGO';
        const descuento = document.getElementById('inputDescuento').value || '0';
        const fecha     = document.getElementById('inputFecha').value;
        const tipo      = document.querySelector('input[name="tipo"]:checked').value;

        document.getElementById('prev-codigo').textContent = codigo;
        document.getElementById('prev-descuento').textContent =
            tipo === 'porcentaje' ? descuento + '%' : '$' + parseInt(descuento).toLocaleString();
        document.getElementById('prev-tipo').textContent =
            tipo === 'porcentaje' ? 'de descuento en tu compra' : 'de descuento directo';
        document.getElementById('prev-vence').textContent =
            fecha ? 'Válido hasta: ' + new Date(fecha + 'T00:00:00').toLocaleDateString('es-CO') : 'Sin fecha de vencimiento';
    }

    actualizarPreview();
</script>
</body>
</html>