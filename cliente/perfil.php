<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/conexion.php';

$conexion = conectar();
$usuario_id = $_SESSION['usuario_id'];

// ============================================
// ACTUALIZAR PERFIL
// ============================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    $stmt = $conexion->prepare("
        UPDATE usuarios_roles 
        SET nombre = ?, email = ?, telefono = ?
        WHERE id = ?
    ");

    $stmt->execute([$nombre, $email, $telefono, $usuario_id]);

    $mensaje = "Perfil actualizado correctamente";
}

// ============================================
// OBTENER DATOS DEL USUARIO
// ============================================
$stmt = $conexion->prepare("
    SELECT nombre, email, telefono 
    FROM usuarios_roles 
    WHERE id = ?
");
$stmt->execute([$usuario_id]);

$usuario = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/variables.css" rel="stylesheet">
    <link href="../assets/css/global.css" rel="stylesheet">
    <link href="../assets/css/public.css" rel="stylesheet">
</head>

<body>
    <?php require_once __DIR__ . '/../includes/header_public.php'; ?>

    <div class="container mt-5">

    <!--  MENÚ DEL CLIENTE -->
    <div class="mb-4 d-flex justify-content-between">
        <h2> Mi Perfil</h2>

        <div>
            <a href="../catalogo.php" class="btn btn-dark"> Inicio</a>
            <a href="mis_pedidos.php" class="btn btn-dark"> Mis pedidos</a>
            <a href="wishlist.php" class="btn btn-dark"> Wishlist</a>
            <a href="../logout.php" class="btn btn-dark">Cerrar sesión</a>
        </div>
    </div>

    <!-- MENSAJE -->
    <?php if(isset($mensaje)): ?>
        <div class="alert alert-success">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO PERFIL -->
    <div class="card p-4 shadow">

        <form method="POST">

            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control"
                    value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control"
                    value="<?= htmlspecialchars($usuario['telefono']) ?>">
            </div>

            <button class="btn btn-dark w-100">
                 Guardar cambios
            </button>

        </form>

    </div>

</div>

    <?php require_once __DIR__ . '/../includes/footer_public.php'; ?>

</body>
</html>