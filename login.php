<?php
require_once 'includes/auth.php';
require_once 'includes/funciones_usuario.php';

if (estaLogueado()) {
    if (esAdmin()) {
        header("Location: admin/index.php");
    } elseif (esCliente()) {
        header("Location: index.php");
    } else {
        // Redirigir a contador, vendedor, inventario, etc.
        header("Location: departamentos/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso') {
    $success = 'Cuenta creada exitosamente Ya puedes iniciar sesión.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $resultado = loginUsuario($email, $password);

        if ($resultado['exito']) {
            if ($resultado['rol'] === 'admin') {
                header("Location: admin/index.php");
            } elseif ($resultado['rol'] === 'cliente') {
                header("Location: index.php");
            } else {
                // Redirigir a departamentos (contador, vendedor, inventario, etc.)
                header("Location: departamentos/dashboard.php");
            }
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
    <title>Iniciar Sesión — SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
   <link href="assets/css/login.css" rel="stylesheet">
</head>

<body>

    <div class="login-wrapper">

        <!-- Banner izquierdo -->
        <div class="login-banner">
            <div class="icon-sport"></div>
            <div class="logo">SPORT<span>STORE</span></div>
            <p>La mejor ropa deportiva<br>al mejor precio.<br>¡Muévete con estilo!</p>
        </div>

        <!-- Formulario -->
        <div class="login-form">
            <h2>Bienvenido </h2>
            <p class="subtitle">Inicia sesión para continuar</p>

            <!-- Alertas -->
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-circle-check"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Tu contraseña" required>
                        <span class="input-group-text" onclick="togglePassword()">
                            <i class="fas fa-eye" id="iconPassword"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    INICIAR SESIÓN
                </button>
            </form>

            <div class="divider">o</div>

            <div class="link-registro">
                ¿No tienes cuenta?
                <a href="registro.php">Regístrate gratis</a>
            </div>
            <div class="link-registro">
                ¿Te equivocaste?
                <a href="index.php">vuelve a inicio </a>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('iconPassword');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>


</html>