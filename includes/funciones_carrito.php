<?php
// ============================================
// FUNCIONES DEL CARRITO
// includes/funciones_carrito.php
// ============================================

require_once 'conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// OBTENER CARRITO COMPLETO
// ============================================
function obtenerCarrito() {
    if (!isset($_SESSION['carrito'])) {
        return [];
    }

    $carrito = &$_SESSION['carrito'];
    $db = conectar();

    foreach ($carrito as $key => $item) {
        if (!is_array($item) && is_numeric($item)) {
            $producto_id = intval($key);
            $cantidad = intval($item);

            $stmt = $db->prepare(
                "SELECT v.*, p.nombre, p.imagen_principal
                 FROM variantes v
                 JOIN productos p ON v.producto_id = p.id
                 WHERE v.producto_id = ? AND v.activo = 1 AND v.stock > 0
                 ORDER BY v.precio ASC
                 LIMIT 1"
            );
            $stmt->execute([$producto_id]);
            $variante = $stmt->fetch();

            if ($variante) {
                $carrito[$key] = [
                    'variante_id'      => $variante['id'],
                    'nombre'           => $variante['nombre'],
                    'talla'            => $variante['talla'],
                    'color'            => $variante['color'],
                    'precio'           => $variante['precio'],
                    'imagen'           => $variante['imagen_principal'],
                    'cantidad'         => $cantidad,
                    'stock_disponible' => $variante['stock']
                ];
            } else {
                unset($carrito[$key]);
            }
        }
    }

    return $_SESSION['carrito'];
}

// ============================================
// AGREGAR PRODUCTO AL CARRITO
// ============================================
function agregarAlCarrito($variante_id, $cantidad = 1) {
    $db = conectar();

    // Verificar que la variante existe y tiene stock
    $stmt = $db->prepare("
        SELECT v.*, p.nombre, p.imagen_principal
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        WHERE v.id = ? AND v.activo = 1 AND v.stock > 0
    ");
    $stmt->execute([$variante_id]);
    $variante = $stmt->fetch();

    if (!$variante) {
        return [
            'exito'   => false,
            'mensaje' => 'Producto no disponible'
        ];
    }

    // Verificar stock suficiente
    if ($variante['stock'] < $cantidad) {
        return [
            'exito'   => false,
            'mensaje' => 'Stock insuficiente, solo hay ' . $variante['stock'] . ' unidades'
        ];
    }

    // Iniciar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Si ya está en el carrito, sumar cantidad
    if (isset($_SESSION['carrito'][$variante_id])) {
        $nueva_cantidad = $_SESSION['carrito'][$variante_id]['cantidad'] + $cantidad;

        // Verificar que no supere el stock
        if ($nueva_cantidad > $variante['stock']) {
            return [
                'exito'   => false,
                'mensaje' => 'No hay suficiente stock para agregar más unidades'
            ];
        }

        $_SESSION['carrito'][$variante_id]['cantidad'] = $nueva_cantidad;
    } else {
        // Agregar nuevo item al carrito
        $_SESSION['carrito'][$variante_id] = [
            'variante_id'     => $variante_id,
            'nombre'          => $variante['nombre'],
            'talla'           => $variante['talla'],
            'color'           => $variante['color'],
            'precio'          => $variante['precio'],
            'imagen'          => $variante['imagen_principal'],
            'cantidad'        => $cantidad,
            'stock_disponible' => $variante['stock']
        ];
    }

    return [
        'exito'   => true,
        'mensaje' => 'Producto agregado al carrito',
        'total_items' => contarItemsCarrito()
    ];
}

// ============================================
// ACTUALIZAR CANTIDAD DE UN ITEM
// ============================================
function actualizarCantidad($variante_id, $cantidad) {
    $db = conectar();

    if ($cantidad <= 0) {
        return eliminarDelCarrito($variante_id);
    }

    // Verificar stock disponible
    $stmt = $db->prepare("SELECT stock FROM variantes WHERE id = ?");
    $stmt->execute([$variante_id]);
    $variante = $stmt->fetch();

    if ($cantidad > $variante['stock']) {
        return [
            'exito'   => false,
            'mensaje' => 'Solo hay ' . $variante['stock'] . ' unidades disponibles'
        ];
    }

    if (!isset($_SESSION['carrito'][$variante_id])) {
        return [
            'exito'   => false,
            'mensaje' => 'Producto no está en el carrito'
        ];
    }

    $_SESSION['carrito'][$variante_id]['cantidad'] = $cantidad;

    return [
        'exito'   => true,
        'mensaje' => 'Cantidad actualizada',
        'nuevo_subtotal' => $_SESSION['carrito'][$variante_id]['precio'] * $cantidad
    ];
}

// ============================================
// ELIMINAR PRODUCTO DEL CARRITO
// ============================================
function eliminarDelCarrito($variante_id) {
    if (isset($_SESSION['carrito'][$variante_id])) {
        unset($_SESSION['carrito'][$variante_id]);
    }

    return [
        'exito'       => true,
        'mensaje'     => 'Producto eliminado del carrito',
        'total_items' => contarItemsCarrito()
    ];
}

// ============================================
// VACIAR CARRITO COMPLETO
// ============================================
function vaciarCarrito() {
    $_SESSION['carrito'] = [];

    return [
        'exito'   => true,
        'mensaje' => 'Carrito vaciado'
    ];
}

// ============================================
// CONTAR ITEMS EN EL CARRITO
// ============================================
function contarItemsCarrito() {
    $carrito = obtenerCarrito();
    $total   = 0;

    foreach ($carrito as $item) {
        $total += $item['cantidad'];
    }

    return $total;
}

// ============================================
// CALCULAR SUBTOTAL DEL CARRITO
// ============================================
function calcularSubtotal() {
    $carrito  = obtenerCarrito();
    $subtotal = 0;

    foreach ($carrito as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }

    return $subtotal;
}

// ============================================
// APLICAR CUPÓN DE DESCUENTO
// ============================================
function aplicarCupon($codigo) {
    $db = conectar();

    // Buscar cupón válido
    $stmt = $db->prepare("
        SELECT * FROM cupones
        WHERE codigo = ?
        AND activo = 1
        AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
        AND usos_actuales < usos_maximos
    ");
    $stmt->execute([$codigo]);
    $cupon = $stmt->fetch();

    if (!$cupon) {
        return [
            'exito'   => false,
            'mensaje' => 'Cupón inválido o expirado'
        ];
    }

    $subtotal  = calcularSubtotal();
    $descuento = 0;

    if ($cupon['tipo'] === 'porcentaje') {
        $descuento = $subtotal * ($cupon['descuento'] / 100);
    } else {
        $descuento = $cupon['descuento'];
    }

    // Guardar cupón en sesión
    $_SESSION['cupon'] = [
        'id'        => $cupon['id'],
        'codigo'    => $cupon['codigo'],
        'descuento' => $descuento,
        'tipo'      => $cupon['tipo']
    ];

    return [
        'exito'     => true,
        'mensaje'   => '¡Cupón aplicado! Descuento: $' . number_format($descuento, 2),
        'descuento' => $descuento
    ];
}

// ============================================
// CALCULAR TOTAL FINAL (CON DESCUENTO)
// ============================================
function calcularTotal() {
    $subtotal  = calcularSubtotal();
    $descuento = $_SESSION['cupon']['descuento'] ?? 0;
    $total     = $subtotal - $descuento;

    return max(0, $total); // Nunca negativo
}

// ============================================
// OBTENER RESUMEN DEL CARRITO
// ============================================
function resumenCarrito() {
    return [
        'items'     => obtenerCarrito(),
        'subtotal'  => calcularSubtotal(),
        'descuento' => $_SESSION['cupon']['descuento'] ?? 0,
        'cupon'     => $_SESSION['cupon']['codigo'] ?? null,
        'total'     => calcularTotal(),
        'cantidad'  => contarItemsCarrito()
    ];
}

// ============================================
// WISHLIST — AGREGAR A FAVORITOS
// ============================================
function agregarWishlist($usuario_id, $variante_id) {
    $db = conectar();

    // Verificar si ya está en wishlist
    $stmt = $db->prepare("
        SELECT id FROM wishlist WHERE usuario_id = ? AND variante_id = ?
    ");
    $stmt->execute([$usuario_id, $variante_id]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'El producto ya está en tu lista de deseos'
        ];
    }

    $stmt = $db->prepare("
        INSERT INTO wishlist (usuario_id, variante_id) VALUES (?, ?)
    ");
    $stmt->execute([$usuario_id, $variante_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Producto agregado a tu lista de deseos'
    ];
}

// ============================================
// WISHLIST — OBTENER LISTA DE DESEOS
// ============================================
function obtenerWishlist($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT w.id, p.nombre, p.imagen_principal,
               v.talla, v.color, v.precio, v.stock, v.id AS variante_id
        FROM wishlist w
        JOIN variantes v ON w.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        WHERE w.usuario_id = ?
        ORDER BY w.fecha DESC
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

// ============================================
// WISHLIST — ELIMINAR DE FAVORITOS
// ============================================
function eliminarWishlist($usuario_id, $variante_id) {
    $db = conectar();

    $stmt = $db->prepare("
        DELETE FROM wishlist WHERE usuario_id = ? AND variante_id = ?
    ");
    $stmt->execute([$usuario_id, $variante_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Producto eliminado de tu lista de deseos'
    ];
}