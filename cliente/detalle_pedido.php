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

// JOIN
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
?>

<h2>Detalle del Pedido</h2>

<table border="1">
<tr>
    <th>Producto</th>
    <th>Talla</th>
    <th>Color</th>
    <th>Cantidad</th>
    <th>Precio</th>
    <th>Subtotal</th>
</tr>

<?php foreach($items as $item): ?>
<tr>
    <td><?= $item['nombre'] ?></td>
    <td><?= $item['talla'] ?></td>
    <td><?= $item['color'] ?></td>
    <td><?= $item['cantidad'] ?></td>
    <td>$<?= $item['precio_unitario'] ?></td>
    <td>$<?= $item['subtotal'] ?></td>
</tr>
<?php endforeach; ?>

</table>