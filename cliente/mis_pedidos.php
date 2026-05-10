<?php
require '../includes/auth.php';
require '../includes/conexion.php';
require '../includes/funciones_factura.php';

soloLogueados();

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];

// CONSULTA
$stmt = $conexion->prepare("
    SELECT o.id, o.fecha, o.total, o.estado, f.numero_factura
    FROM ordenes o
    LEFT JOIN facturas f ON o.id = f.orden_id
    WHERE o.usuario_id = ?
    ORDER BY o.fecha DESC
");

$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();

$usuario = usuarioActual();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos — SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 mb-5">
    <h1 class="mb-4">
        <i class="fas fa-box"></i> Mis Pedidos
    </h1>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Aún no tienes pedidos realizados. <a href="../catalogo.php">Visita nuestro catálogo</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Número de Pedido</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>
                                <strong>#<?= str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                            <td>
                                <strong>$<?= number_format($pedido['total'], 2) ?></strong>
                            </td>
                            <td>
                                <?php
                                $estado = $pedido['estado'];
                                $clases = [
                                    'pendiente' => 'bg-warning text-dark',
                                    'empacado' => 'bg-info text-white',
                                    'enviado' => 'bg-success text-white',
                                    'entregado' => 'bg-primary text-white',
                                    'cancelado' => 'bg-danger text-white'
                                ];
                                $clase = $clases[$estado] ?? 'bg-secondary text-white';
                                ?>
                                <span class="badge <?= $clase ?>">
                                    <?= ucfirst($estado) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="detalle_pedido.php?id=<?= $pedido['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i> Detalles
                                    </a>
                                    <?php if ($pedido['numero_factura']): ?>
                                        <a href="descargar_factura.php?orden_id=<?= $pedido['id'] ?>" 
                                           class="btn btn-sm btn-outline-success"
                                           title="Descargar factura en PDF">
                                            <i class="fas fa-file-pdf"></i> Factura
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Factura no disponible aún">
                                            <i class="fas fa-file-pdf"></i> Factura
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="../index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Tienda
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>