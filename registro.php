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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }

        .registro-wrapper {
            display: flex;
            width: 900px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        /* Banner */
        .registro-banner {
            background: #e44d26;
            color: white;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .registro-banner .logo {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .registro-banner .logo span {
            color: #111;
        }

        .registro-banner .icon-sport {
            font-size: 70px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .registro-banner p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            line-height: 1.7;
            margin-top: 10px;
        }

        .beneficios {
            list-style: none;
            margin-top: 20px;
            text-align: left;
            width: 100%;
        }

        .beneficios li {
            padding: 6px 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }

        .beneficios li i {
            margin-right: 8px;
            color: #fff;
        }

        /* Formulario */
        .registro-form {
            background: white;
            flex: 1.2;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .registro-form h2 {
            font-size: 24px;
            font-weight: 800;
            color: #111;
            margin-bottom: 5px;
        }

        .registro-form p.subtitle {
            color: #888;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        .form-control {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 11px 15px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #e44d26;
            box-shadow: none;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group-text {
            background: white;
            border: 2px solid #eee;
            border-left: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            color: #888;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            transition: all 0.3s;
            background: #eee;
        }

        .strength-text {
            font-size: 11px;
            margin-top: 3px;
        }

        .btn-registro {
            background: #e44d26;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-weight: 700;
            font-size: 15px;
            width: 100%;
            margin-top: 10px;
            transition: background 0.3s;
            letter-spacing: 1px;
        }

        .btn-registro:hover {
            background: #c43d1e;
        }

        .link-login {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 15px;
        }

        .link-login a {
            color: #111;
            font-weight: 700;
            text-decoration: none;
        }

        .link-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .registro-banner {
                display: none;
            }

            .registro-wrapper {
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>

<body>

    <div class="registro-wrapper">

        <!-- Banner izquierdo -->
        <div class="registro-banner">
            <div class="icon-sport">🏃</div>
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