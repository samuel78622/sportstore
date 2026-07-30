<?php
require_once 'includes/auth.php';
require_once 'includes/funciones_usuario.php';

// Si ya está logueado redirigir
if (estaLogueado()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos obligatorios';
    } elseif (strlen($nombre) < 3) {
        $error = 'El nombre debe tener al menos 3 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden';
    } else {
        $resultado = registrarUsuario($nombre, $email, $password, $telefono);

        if ($resultado['exito']) {
            header("Location: login.php?registro=exitoso");
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
    <title>Crear Cuenta — SportStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/registro.css" rel="stylesheet">
</head>

<body>

    <div class="registro-wrapper">

        <!-- Banner izquierdo -->
        <div class="registro-banner">
            <div class="icon-sport">⚡</div>
            <div class="logo">SPORT<span>STORE</span></div>
            <p>Únete a nuestra comunidad deportiva</p>
            <ul class="beneficios">
                <li><i class="fas fa-check-circle"></i> Historial de pedidos</li>
                <li><i class="fas fa-check-circle"></i> Lista de deseos</li>
                <li><i class="fas fa-check-circle"></i> Cupones exclusivos</li>
                <li><i class="fas fa-check-circle"></i> Seguimiento de envíos</li>
                <li><i class="fas fa-check-circle"></i> Ofertas personalizadas</li>
            </ul>
        </div>

        <!-- Formulario -->
        <div class="registro-form">
            <h2>Crear cuenta 🚀</h2>
            <p class="subtitle">Completa los datos para registrarte</p>

            <!-- Error -->
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <!-- Nombre -->
                <div class="mb-3">
                    <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" placeholder="Tu nombre completo"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <!-- Teléfono -->
                <div class="mb-3">
                    <label class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
                    <input type="tel" name="telefono" class="form-control" placeholder="3001234567"
                        value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Mínimo 6 caracteres" oninput="medirFuerza(this.value)" required>
                        <span class="input-group-text" onclick="togglePass('password', 'iconPass1')">
                            <i class="fas fa-eye" id="iconPass1"></i>
                        </span>
                    </div>
                    <div class="password-strength" id="strengthBar"></div>
                    <small class="strength-text" id="strengthText"></small>
                </div>

                <!-- Confirmar contraseña -->
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password2" id="password2" class="form-control"
                            placeholder="Repite tu contraseña" required>
                        <span class="input-group-text" onclick="togglePass('password2', 'iconPass2')">
                            <i class="fas fa-eye" id="iconPass2"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-registro">
                    CREAR CUENTA
                </button>
            </form>

            <div class="link-login">
                ¿Ya tienes cuenta?
                <a href="login.php">Inicia sesión</a>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar contraseña
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Medir fuerza de contraseña
        function medirFuerza(password) {
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');

            let fuerza = 0;
            if (password.length >= 6) fuerza++;
            if (password.length >= 10) fuerza++;
            if (/[A-Z]/.test(password)) fuerza++;
            if (/[0-9]/.test(password)) fuerza++;
            if (/[^A-Za-z0-9]/.test(password)) fuerza++;

            const niveles = [
                { color: '#eee', label: '', width: '0%' },
                { color: '#e44d26', label: 'Muy débil', width: '20%' },
                { color: '#f39c12', label: 'Débil', width: '40%' },
                { color: '#f1c40f', label: 'Regular', width: '60%' },
                { color: '#2ecc71', label: 'Fuerte', width: '80%' },
                { color: '#27ae60', label: 'Muy fuerte', width: '100%' },
            ];

            const nivel = niveles[fuerza] || niveles[0];
            bar.style.background = nivel.color;
            bar.style.width = nivel.width;
            text.textContent = nivel.label;
            text.style.color = nivel.color;
        }
    </script>
</body>

</html>