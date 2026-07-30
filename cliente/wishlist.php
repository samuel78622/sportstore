<?php
require '../includes/auth.php';
require '../includes/conexion.php';

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];

// AGREGAR
if (isset($_GET['agregar'])) {
    $variante_id = $_GET['agregar'];

    $stmt = $conexion->prepare("
        SELECT id FROM wishlist WHERE usuario_id = ? AND variante_id = ?
    ");
    $stmt->execute([$usuario_id, $variante_id]);

    if ($stmt->rowCount() == 0) {
        $stmt = $conexion->prepare("
            INSERT INTO wishlist (usuario_id, variante_id) VALUES (?, ?)
        ");
        $stmt->execute([$usuario_id, $variante_id]);
    }
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    $stmt = $conexion->prepare("
        DELETE FROM wishlist WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$id, $usuario_id]);
}

// MOSTRAR
$stmt = $conexion->prepare("
    SELECT 
        w.id,
        p.nombre,
        v.talla,
        v.color,
        v.precio
    FROM wishlist w
    INNER JOIN variantes v ON w.variante_id = v.id
    INNER JOIN productos p ON v.producto_id = p.id
    WHERE w.usuario_id = ?
");

$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/variables.css" rel="stylesheet">
    <link href="../assets/css/global.css" rel="stylesheet">
    <link href="../assets/css/public.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header_public.php'; ?>

    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Mi Wishlist</h2>
            <div>
                <a href="../cliente/perfil.php" class="btn btn-dark">Mi perfil</a>
                <a href="../index.php" class="btn btn-dark">Inicio</a>
            </div>
        </div>

        <div class="card p-3 shadow">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th>Precio</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nombre']) ?></td>
                                <td><?= htmlspecialchars($item['talla']) ?></td>
                                <td><?= htmlspecialchars($item['color']) ?></td>
                                <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                                <td><a href="?eliminar=<?= $item['id'] ?>" class="text-danger">Eliminar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . '/../includes/footer_public.php'; ?>

</body>
</html>