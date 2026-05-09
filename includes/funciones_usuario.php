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
    $stmt = $db->prepare("SELECT id FROM usuarios_roles WHERE email = ?");
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
        INSERT INTO usuarios_roles (nombre, email, password, telefono, rol)
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
        SELECT * FROM usuarios_roles WHERE email = ? AND activo = 1
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
        FROM usuarios_roles
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
        SELECT id FROM usuarios_roles WHERE email = ? AND id != ?
    ");
    $stmt->execute([$email, $usuario_id]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'Ese correo ya está en uso por otra cuenta'
        ];
    }

    $stmt = $db->prepare("
        UPDATE usuarios_roles
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
    $stmt = $db->prepare("SELECT password FROM usuarios_roles WHERE id = ?");
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
        UPDATE usuarios_roles SET password = ? WHERE id = ?
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
        FROM usuarios_roles
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
        UPDATE usuarios_roles
        SET activo = IF(activo = 1, 0, 1)
        WHERE id = ?
    ");
    $stmt->execute([$usuario_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Estado del usuario actualizado'
    ];
}

// ============================================
// OBTENER UN USUARIO POR ID (ADMIN)
// ============================================
function obtenerUsuario($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT id, nombre, email, telefono, rol, activo, fecha_registro
        FROM usuarios_roles
        WHERE id = ?
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetch();
}

// ============================================
// ACTUALIZAR ROL DEL USUARIO (ADMIN)
// ============================================
function actualizarRol($usuario_id, $nuevo_rol) {
    $db = conectar();

    // Roles disponibles
    $rolesValidos = ['cliente', 'admin', 'inventario', 'contador', 'vendedor'];

    if (!in_array($nuevo_rol, $rolesValidos)) {
        return [
            'exito'   => false,
            'mensaje' => 'Rol no válido'
        ];
    }

    $stmt = $db->prepare("
        UPDATE usuarios_roles
        SET rol = ?
        WHERE id = ?
    ");
    $stmt->execute([$nuevo_rol, $usuario_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Rol actualizado correctamente'
    ];
}

// ============================================
// CREAR USUARIO DESDE ADMIN
// ============================================
function crearUsuarioAdmin($nombre, $email, $password, $rol, $telefono = null) {
    $db = conectar();

    // Verificar si el email ya existe
    $stmt = $db->prepare("SELECT id FROM usuarios_roles WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'El correo ya está registrado'
        ];
    }

    // Validar rol
    $rolesValidos = ['cliente', 'admin', 'inventario', 'contador', 'vendedor'];
    if (!in_array($rol, $rolesValidos)) {
        return [
            'exito'   => false,
            'mensaje' => 'Rol no válido'
        ];
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario con rol específico
    $stmt = $db->prepare("
        INSERT INTO usuarios_roles (nombre, email, password, telefono, rol, activo)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$nombre, $email, $passwordHash, $telefono, $rol]);

    return [
        'exito'   => true,
        'mensaje' => 'Usuario creado exitosamente con rol: ' . $rol
    ];
}