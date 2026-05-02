<?php
require '../includes/auth.php';
require '../includes/conexion.php';

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];

// CONSULTA
$stmt = $conexion->prepare("
    SELECT id, fecha, total, estado 
    FROM ordenes 
    WHERE usuario_id = ?
    ORDER BY fecha DESC
");

$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();
?>

<h2>Mis Pedidos</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Fecha</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Detalle</th>
</tr>

<?php foreach($pedidos as $row): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['fecha'] ?></td>
    <td>$<?= $row['total'] ?></td>
    <td><?= $row['estado'] ?></td>
    <td><a href="detalle_pedido.php?id=<?= $row['id'] ?>">Ver</a></td>
</tr>
<?php endforeach; ?>

</table>