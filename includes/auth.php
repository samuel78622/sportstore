<?php
// ============================================
// AUTENTICACIÓN Y ROLES
// includes/auth.php
// ============================================

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// VERIFICAR SI EL USUARIO ESTÁ LOGUEADO
// ============================================
function estaLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// ============================================
// OBTENER ROL DEL USUARIO ACTUAL
// ============================================
function obtenerRol() {
    return $_SESSION['rol'] ?? null;
}

// ============================================
// VERIFICAR SI ES ADMIN
// ============================================
function esAdmin() {
    return estaLogueado() && $_SESSION['rol'] === 'admin';
}

// ============================================
// VERIFICAR SI ES CLIENTE
// ============================================
function esCliente() {
    return estaLogueado() && $_SESSION['rol'] === 'cliente';
}

// ============================================
// VERIFICAR SI ES GESTOR DE INVENTARIO
// ============================================
function esInventario() {
    return estaLogueado() && $_SESSION['rol'] === 'inventario';
}

// ============================================
// PROTEGER PÁGINAS — SOLO LOGUEADOS
// ============================================
function soloLogueados() {
    if (!estaLogueado()) {
        header("Location: /ecommerce/login.php?error=debes_iniciar_sesion");
        exit();
    }
}

// ============================================
// PROTEGER PÁGINAS — SOLO ADMIN
// ============================================
function soloAdmin() {
    if (!estaLogueado()) {
        header("Location: /ecommerce/login.php?error=debes_iniciar_sesion");
        exit();
    }

    if (!esAdmin()) {
        header("Location: /ecommerce/index.php?error=sin_permiso");
        exit();
    }
}

// ============================================
// PROTEGER PÁGINAS — SOLO ADMIN E INVENTARIO
// ============================================
function soloAdminOInventario() {
    if (!estaLogueado()) {
        header("Location: /ecommerce/login.php?error=debes_iniciar_sesion");
        exit();
    }

    if (!esAdmin() && !esInventario()) {
        header("Location: /ecommerce/index.php?error=sin_permiso");
        exit();
    }
}

// ============================================
// INICIAR SESIÓN — GUARDAR DATOS EN SESSION
// ============================================
function iniciarSesion($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['email']      = $usuario['email'];
    $_SESSION['rol']        = $usuario['rol'];
}

// ============================================
// CERRAR SESIÓN
// ============================================
function cerrarSesion() {
    session_unset();
    session_destroy();
    header("Location: /ecommerce/login.php");
    exit();
}

// ============================================
// OBTENER DATOS DEL USUARIO LOGUEADO
// ============================================
function usuarioActual() {
    if (!estaLogueado()) return null;

    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'],
        'email'  => $_SESSION['email'],
        'rol'    => $_SESSION['rol']
    ];
}