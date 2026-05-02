<?php
// ============================================
// FUNCIONES DE VARIANTES
// includes/funciones_variantes.php
// ============================================

require_once 'conexion.php';

// ============================================
// OBTENER UNA VARIANTE POR ID
// ============================================
function obtenerVariante($id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT v.*, p.nombre AS producto
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        WHERE v.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================
// OBTENER VARIANTES DE UN PRODUCTO
// ============================================
function obtenerVariantesPorProducto($producto_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT * FROM variantes
        WHERE producto_id = ? AND activo = 1
        ORDER BY talla, color
    ");
    $stmt->execute([$producto_id]);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER TALLAS DISPONIBLES DE UN PRODUCTO
// ============================================
function obtenerTallasDisponibles($producto_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT DISTINCT talla FROM variantes
        WHERE producto_id = ? AND stock > 0 AND activo = 1
        ORDER BY talla
    ");
    $stmt->execute([$producto_id]);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER COLORES DISPONIBLES POR TALLA
// ============================================
function obtenerColoresDisponibles($producto_id, $talla) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT id, color, precio, stock FROM variantes
        WHERE producto_id = ? AND talla = ?
        AND stock > 0 AND activo = 1
    ");
    $stmt->execute([$producto_id, $talla]);
    return $stmt->fetchAll();
}

// ============================================
// CREAR VARIANTE
// ============================================
function crearVariante($producto_id, $talla, $color, $precio, $stock) {
    $db = conectar();

    // Verificar que no exista esa combinación
    $stmt = $db->prepare("
        SELECT id FROM variantes
        WHERE producto_id = ? AND talla = ? AND color = ?
    ");
    $stmt->execute([$producto_id, $talla, $color]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'Ya existe una variante con esa talla y color'
        ];
    }

    // Validar precio y stock
    if ($precio <= 0) {
        return [
            'exito'   => false,
            'mensaje' => 'El precio debe ser mayor a 0'
        ];
    }

    if ($stock < 0) {
        return [
            'exito'   => false,
            'mensaje' => 'El stock no puede ser negativo'
        ];
    }

    $stmt = $db->prepare("
        INSERT INTO variantes (producto_id, talla, color, precio, stock)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$producto_id, $talla, $color, $precio, $stock]);

    $variante_id = $db->lastInsertId();

    // Registrar entrada inicial en movimientos
    require_once 'funciones_producto.php';
    registrarMovimiento(
        $variante_id,
        'entrada',
        $stock,
        'Stock inicial de variante',
        null
    );

    return [
        'exito'       => true,
        'mensaje'     => 'Variante creada correctamente',
        'variante_id' => $variante_id
    ];
}

// ============================================
// EDITAR VARIANTE
// ============================================
function editarVariante($id, $talla, $color, $precio, $stock) {
    $db = conectar();

    // Obtener datos actuales
    $stmt = $db->prepare("SELECT * FROM variantes WHERE id = ?");
    $stmt->execute([$id]);
    $variante_actual = $stmt->fetch();

    if (!$variante_actual) {
        return [
            'exito'   => false,
            'mensaje' => 'Variante no encontrada'
        ];
    }

    // Validaciones
    if ($precio <= 0) {
        return [
            'exito'   => false,
            'mensaje' => 'El precio debe ser mayor a 0'
        ];
    }

    if ($stock < 0) {
        return [
            'exito'   => false,
            'mensaje' => 'El stock no puede ser negativo'
        ];
    }

    // Registrar movimiento si el stock cambió
    if ($stock != $variante_actual['stock']) {
        $diferencia = $stock - $variante_actual['stock'];
        $tipo       = $diferencia > 0 ? 'entrada' : 'salida';

        require_once 'funciones_producto.php';
        registrarMovimiento(
            $id,
            $tipo,
            abs($diferencia),
            'Ajuste por edición de variante'
        );
    }

    $stmt = $db->prepare("
        UPDATE variantes
        SET talla = ?, color = ?, precio = ?, stock = ?
        WHERE id = ?
    ");
    $stmt->execute([$talla, $color, $precio, $stock, $id]);

    return [
        'exito'   => true,
        'mensaje' => 'Variante actualizada correctamente'
    ];
}

// ============================================
// ELIMINAR VARIANTE (DESACTIVAR)
// ============================================
function eliminarVariante($id) {
    $db = conectar();

    // Verificar que no tenga órdenes activas
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM detalle_orden d
        JOIN ordenes o ON d.orden_id = o.id
        WHERE d.variante_id = ?
        AND o.estado NOT IN ('entregado', 'cancelado')
    ");
    $stmt->execute([$id]);
    $total = $stmt->fetch()['total'];

    if ($total > 0) {
        return [
            'exito'   => false,
            'mensaje' => 'No se puede eliminar, tiene órdenes activas asociadas'
        ];
    }

    $stmt = $db->prepare("UPDATE variantes SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);

    return [
        'exito'   => true,
        'mensaje' => 'Variante eliminada correctamente'
    ];
}

// ============================================
// VERIFICAR STOCK DE UNA VARIANTE
// ============================================
function verificarStockVariante($variante_id, $cantidad) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT stock FROM variantes WHERE id = ? AND activo = 1
    ");
    $stmt->execute([$variante_id]);
    $variante = $stmt->fetch();

    if (!$variante) {
        return [
            'disponible' => false,
            'mensaje'    => 'Variante no disponible'
        ];
    }

    if ($variante['stock'] < $cantidad) {
        return [
            'disponible' => false,
            'mensaje'    => 'Solo hay ' . $variante['stock'] . ' unidades disponibles',
            'stock'      => $variante['stock']
        ];
    }

    return [
        'disponible' => true,
        'stock'      => $variante['stock']
    ];
}

// ============================================
// OBTENER PRECIO DE UNA VARIANTE
// ============================================
function obtenerPrecioVariante($variante_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT precio FROM variantes WHERE id = ? AND activo = 1
    ");
    $stmt->execute([$variante_id]);
    $variante = $stmt->fetch();

    return $variante ? $variante['precio'] : 0;
}

// ============================================
// LISTAR TODAS LAS VARIANTES (ADMIN)
// ============================================
function listarTodasVariantes() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT v.*, p.nombre AS producto,
               c.nombre AS categoria
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        WHERE v.activo = 1
        ORDER BY p.nombre, v.talla, v.color
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}