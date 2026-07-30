<?php
require '../includes/auth.php';
require '../includes/conexion.php';

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];
$orden_id = $_GET['id'];

// VALIDAR QUE LA ORDEN ES DEL USUARIO
$stmt = $conexion->prepare("
    SELECT id FROM ordenes WHERE id = ? AND usuario_id = ?
");
$stmt->execute([$orden_id, $usuario_id]);

if ($stmt->rowCount() == 0) {
    die("Acceso denegado");
}

// OBTENER DETALLE DE LA ORDEN
$stmt = $conexion->prepare("
    SELECT 
        p.nombre,
        v.talla,
        v.color,
        d.cantidad,
        d.precio_unitario,
        d.subtotal
    FROM detalle_orden d
    INNER JOIN variantes v ON d.variante_id = v.id
    INNER JOIN productos p ON v.producto_id = p.id
    WHERE d.orden_id = ?
");

$stmt->execute([$orden_id]);
$items = $stmt->fetchAll();

// TOTAL
$total = 0;
foreach ($items as $item) {
    $total += $item['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido</title>

    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
        }

        /* TITULO */
        .page-title {
            font-weight: 700;
            color: #111827;
            margin-bottom: 25px;
        }

        /* CARD */
        .card-custom {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
        }

        /* RESUMEN */
        .resumen-card {
            border-radius: 20px;
            padding: 25px;
            color: white;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .bg-total {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        /* TABLA */
        .table {
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead {
            background: #111827;
            color: white;
        }

        .table tbody tr:hover {
            background: #f1f5f9;
            transition: 0.3s;
        }

        /* BOTONES */
        .btn-custom {
            background: #111827;
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-custom:hover {
            background: #2563eb;
            color: white;
            transform: scale(1.03);
        }

        /* BADGES */
        .badge-custom {
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            background: #2563eb;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2 class="page-title">
            Detalle del Pedido #<?= $orden_id ?>
        </h2>

        <!-- RESUMEN -->
        <div class="resumen-card bg-total">
            <h4>Total del Pedido</h4>
            <h2>$<?= number_format($total, 0, ',', '.') ?></h2>
        </div>

        <!-- CARD -->
        <div class="card-custom">

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= $item['nombre'] ?></strong>
                                </td>

                                <td>
                                    <span class="badge-custom">
                                        <?= $item['talla'] ?>
                                    </span>
                                </td>

                                <td><?= $item['color'] ?></td>

                                <td><?= $item['cantidad'] ?></td>

                                <td>
                                    $<?= number_format($item['precio_unitario'], 0, ',', '.') ?>
                                </td>

                                <td>
                                    <strong>
                                        $<?= number_format($item['subtotal'], 0, ',', '.') ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

            <div class="mt-4">
                <a href="mis_pedidos.php" class="btn-custom">
                    ← Volver a Mis Pedidos
                </a>

                <a href="../index.php" class="btn btn-custom">
                    <i class="fas fa-store"></i> Volver a la tienda
                </a>
            </div>

        </div>

    </div>

</body>

</html>