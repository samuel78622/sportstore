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

<h2>Mi Wishlist</h2>

<table border="1">
<tr>
    <th>Producto</th>
    <th>Talla</th>
    <th>Color</th>
    <th>Precio</th>
    <th>Acción</th>
</tr>

<?php foreach($items as $item): ?>
<tr>
    <td><?= $item['nombre'] ?></td>
    <td><?= $item['talla'] ?></td>
    <td><?= $item['color'] ?></td>
    <td>$<?= $item['precio'] ?></td>
    <td><a href="?eliminar=<?= $item['id'] ?>">Eliminar</a></td>
</tr>
<?php endforeach; ?>

</table>