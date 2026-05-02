<?php
// ============================================
// FUNCIONES DE USUARIO
// includes/funciones_usuario.php
// ============================================

require_once 'conexion.php';
require_once 'auth.php';

// ============================================
// REGISTRAR NUEVO USUARIO
// ============================================
function registrarUsuario($nombre, $email, $password, $telefono = null) {
    $db = conectar();

    // Verificar si el email ya existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'El correo ya está registrado'
        ];
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, email, password, telefono, rol)
        VALUES (?, ?, ?, ?, 'cliente')
    ");
    $stmt->execute([$nombre, $email, $passwordHash, $telefono]);

    return [
        'exito'   => true,
        'mensaje' => 'Cuenta creada exitosamente'
    ];
}

// ============================================
// INICIAR SESIÓN
// ============================================
function loginUsuario($email, $password) {
    $db = conectar();

    // Buscar usuario por email
    $stmt = $db->prepare("
        SELECT * FROM usuarios WHERE email = ? AND activo = 1
    ");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificar si existe y la contraseña es correcta
    if (!$usuario || !password_verify($password, $usuario['password'])) {
        return [
            'exito'   => false,
            'mensaje' => 'Correo o contraseña incorrectos'
        ];
    }

    // Guardar en sesión
    iniciarSesion($usuario);

    return [
        'exito'   => true,
        'mensaje' => 'Bienvenido ' . $usuario['nombre'],
        'rol'     => $usuario['rol']
    ];
}

// ============================================
// OBTENER PERFIL COMPLETO DEL USUARIO
// ============================================
function obtenerPerfil($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT id, nombre, email, telefono, rol, fecha_registro
        FROM usuarios
        WHERE id = ?
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetch();
}

// ============================================
// ACTUALIZAR PERFIL
// ============================================
function actualizarPerfil($usuario_id, $nombre, $email, $telefono) {
    $db = conectar();

    // Verificar que el email no lo use otro usuario
    $stmt = $db->prepare("
        SELECT id FROM usuarios WHERE email = ? AND id != ?
    ");
    $stmt->execute([$email, $usuario_id]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'Ese correo ya está en uso por otra cuenta'
        ];
    }

    $stmt = $db->prepare("
        UPDATE usuarios
        SET nombre = ?, email = ?, telefono = ?
        WHERE id = ?
    ");
    $stmt->execute([$nombre, $email, $telefono, $usuario_id]);

    // Actualizar sesión
    $_SESSION['nombre'] = $nombre;
    $_SESSION['email']  = $email;

    return [
        'exito'   => true,
        'mensaje' => 'Perfil actualizado correctamente'
    ];
}

// ============================================
// CAMBIAR CONTRASEÑA
// ============================================
function cambiarPassword($usuario_id, $password_actual, $password_nueva) {
    $db = conectar();

    // Obtener contraseña actual
    $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();

    // Verificar contraseña actual
    if (!password_verify($password_actual, $usuario['password'])) {
        return [
            'exito'   => false,
            'mensaje' => 'La contraseña actual es incorrecta'
        ];
    }

    // Guardar nueva contraseña
    $nuevaHash = password_hash($password_nueva, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        UPDATE usuarios SET password = ? WHERE id = ?
    ");
    $stmt->execute([$nuevaHash, $usuario_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Contraseña actualizada correctamente'
    ];
}

// ============================================
// GUARDAR DIRECCIÓN
// ============================================
function guardarDireccion($usuario_id, $direccion, $ciudad, $departamento, $codigo_postal = null) {
    $db = conectar();

    // Si es la primera dirección, marcarla como principal
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM direcciones WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $total = $stmt->fetch()['total'];
    $principal = ($total === 0) ? 1 : 0;

    $stmt = $db->prepare("
        INSERT INTO direcciones (usuario_id, direccion, ciudad, departamento, codigo_postal, principal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $direccion, $ciudad, $departamento, $codigo_postal, $principal]);

    return [
        'exito'   => true,
        'mensaje' => 'Dirección guardada correctamente'
    ];
}

// ============================================
// OBTENER DIRECCIONES DEL USUARIO
// ============================================
function obtenerDirecciones($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT * FROM direcciones
        WHERE usuario_id = ?
        ORDER BY principal DESC
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

// ============================================
// LISTAR TODOS LOS CLIENTES (ADMIN)
// ============================================
function listarClientes() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT id, nombre, email, telefono, rol, activo, fecha_registro
        FROM usuarios
        ORDER BY fecha_registro DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// ACTIVAR O DESACTIVAR USUARIO (ADMIN)
// ============================================
function toggleUsuario($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        UPDATE usuarios
        SET activo = IF(activo = 1, 0, 1)
        WHERE id = ?
    ");
    $stmt->execute([$usuario_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Estado del usuario actualizado'
    ];
}