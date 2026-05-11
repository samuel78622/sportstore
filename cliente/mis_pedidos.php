<?php
require '../includes/auth.php';
require '../includes/conexion.php';
require '../includes/funciones_factura.php';

soloLogueados();

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];

// CONSULTA PEDIDOS
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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- CSS PERSONALIZADO -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
        }

        /* TITULO */
        .page-title {
            font-weight: 700;
            color: #111827;
        }

        /* CARDS */
        .card-custom {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        /* RESUMEN */
        .resumen-card {
            border-radius: 20px;
            padding: 25px;
            color: white;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .bg-total {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        .bg-pedidos {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .bg-pendientes {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        /* TABLA */
        .table {
            border-radius: 15px;
            overflow: hidden;
        }

        /* BOTONES */
        .btn-custom {
            background: #111827;
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background: #2563eb;
            color: white;
            transform: scale(1.03);
        }

        /* BADGES */
        .badge {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
        }
    </style>

</head>

<body>

    <div class="container py-5">

        <!-- TITULO -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

            <div>
                <h1 class="page-title">
                    <i class="fas fa-box-open"></i> Mis Pedidos
                </h1>

                <p class="text-muted">
                    Bienvenido <?= htmlspecialchars($usuario['nombre']) ?>
                </p>
            </div>

            <a href="../index.php" class="btn btn-custom">
                <i class="fas fa-store"></i> Volver a la tienda
            </a>

        </div>

        <!-- RESUMEN -->
        <div class="row mb-4">

            <!-- TOTAL PEDIDOS -->
            <div class="col-md-4 mb-3">

                <div class="resumen-card bg-pedidos">

                    <h5>Total de Pedidos</h5>

                    <h2>
                        <?= count($pedidos) ?>
                    </h2>

                </div>

            </div>

            <!-- TOTAL COMPRADO -->
            <div class="col-md-4 mb-3">

                <div class="resumen-card bg-total">

                    <h5>Total Comprado</h5>

                    <h2>
                        $<?= number_format(array_sum(array_column($pedidos, 'total')), 0, ',', '.') ?>
                    </h2>

                </div>

            </div>

            <!-- PENDIENTES -->
            <div class="col-md-4 mb-3">

                <div class="resumen-card bg-pendientes">

                    <h5>Pedidos Pendientes</h5>

                    <h2>
                        <?= count(array_filter($pedidos, function ($p) {
                            return $p['estado'] == 'pendiente';
                        })) ?>
                    </h2>

                </div>

            </div>

        </div>

        <!-- TABLA -->
        <div class="card-custom p-4">

            <?php if (empty($pedidos)): ?>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Aún no tienes pedidos realizados.
                    <a href="../catalogo.php">Ir al catálogo</a>
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">

                            <tr>
                                <th># Pedido</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($pedidos as $pedido): ?>

                                <tr>

                                    <!-- ID -->
                                    <td>

                                        <strong>
                                            #<?= str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) ?>
                                        </strong>

                                    </td>

                                    <!-- FECHA -->
                                    <td>

                                        <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?>

                                    </td>

                                    <!-- TOTAL -->
                                    <td>

                                        <strong class="text-success">
                                            $<?= number_format($pedido['total'], 0, ',', '.') ?>
                                        </strong>

                                    </td>

                                    <!-- ESTADO -->
                                    <td>

                                        <?php

                                        $estado = $pedido['estado'];

                                        $clases = [
                                            'pendiente' => 'bg-warning text-dark',
                                            'empacado' => 'bg-info text-white',
                                            'enviado' => 'bg-primary text-white',
                                            'entregado' => 'bg-success text-white',
                                            'cancelado' => 'bg-danger text-white'
                                        ];

                                        $clase = $clases[$estado] ?? 'bg-secondary text-white';

                                        ?>

                                        <span class="badge <?= $clase ?>">

                                            <?= ucfirst($estado) ?>

                                        </span>

                                    </td>

                                    <!-- BOTONES -->
                                    <td>

                                        <div class="d-flex gap-2">

                                            <!-- VER DETALLES -->
                                            <a href="detalle_pedido.php?id=<?= $pedido['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">

                                                <i class="fas fa-eye"></i>

                                            </a>

                                            <!-- FACTURA -->
                                            <?php if ($pedido['numero_factura']): ?>

                                                <a href="descargar_factura.php?orden_id=<?= $pedido['id'] ?>"
                                                    class="btn btn-sm btn-outline-success">

                                                    <i class="fas fa-file-pdf"></i>

                                                </a>

                                            <?php else: ?>

                                                <button class="btn btn-sm btn-outline-secondary" disabled>

                                                    <i class="fas fa-file-pdf"></i>

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

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>