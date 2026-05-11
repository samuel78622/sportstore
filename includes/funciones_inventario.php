<?php
// ============================================
// FUNCIONES DE INVENTARIO
// includes/funciones_inventario.php
// ============================================

require_once 'conexion.php';
require_once 'funciones_producto.php';

// ============================================
// OBTENER STOCK GENERAL DE TODOS LOS PRODUCTOS
// ============================================
function obtenerStockGeneral()
{
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.id AS producto_id, p.nombre, p.imagen_principal,
               c.nombre AS categoria,
               v.id AS variante_id, v.talla, v.color,
               v.precio, v.stock
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        WHERE v.activo = 1 AND p.activo = 1
        ORDER BY p.nombre, v.talla, v.color
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// OBTENER PRODUCTOS CON STOCK BAJO
// ============================================
function obtenerStockBajo($limite = 5)
{
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre, p.imagen_principal,
               v.id AS variante_id, v.talla, v.color, v.stock
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        WHERE v.stock <= ? AND v.activo = 1 AND p.activo = 1
        ORDER BY v.stock ASC
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER PRODUCTOS AGOTADOS
// ============================================
function obtenerProductosAgotados()
{
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre, p.imagen_principal,
               v.id AS variante_id, v.talla, v.color
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        WHERE v.stock = 0 AND v.activo = 1 AND p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// MOVER STOCK DE UNA VARIANTE (ADMIN)
// ============================================
function moverStock($variante_id, $cantidad, $motivo, $usuario_id)
{
    $db = conectar();

    if ($cantidad <= 0) {
        return [
            'exito' => false,
            'mensaje' => 'La cantidad debe ser mayor a 0'
        ];
    }

    // Sumar stock a la variante
    $stmt = $db->prepare("
        UPDATE variantes SET stock = stock + ? WHERE id = ?
    ");
    $stmt->execute([$cantidad, $variante_id]);

    // Registrar movimiento de entrada
    registrarMovimiento(
        $variante_id,
        'entrada',
        $cantidad,
        $motivo,
        $usuario_id
    );

    return [
        'exito' => true,
        'mensaje' => 'Stock actualizado correctamente'
    ];
}

// ============================================
// AJUSTAR STOCK MANUALMENTE (ADMIN)
// ============================================
function ajustarStock($variante_id, $nuevo_stock, $motivo, $usuario_id)
{
    $db = conectar();

    if ($nuevo_stock < 0) {
        return [
            'exito' => false,
            'mensaje' => 'El stock no puede ser negativo'
        ];
    }

    // Obtener stock actual para calcular diferencia
    $stmt = $db->prepare("SELECT stock FROM variantes WHERE id = ?");
    $stmt->execute([$variante_id]);
    $variante = $stmt->fetch();
    $stock_actual = $variante['stock'];
    $diferencia = $nuevo_stock - $stock_actual;

    // Actualizar stock
    $stmt = $db->prepare("UPDATE variantes SET stock = ? WHERE id = ?");
    $stmt->execute([$nuevo_stock, $variante_id]);

    // Registrar movimiento según si fue entrada o salida
    if ($diferencia != 0) {
        $tipo = $diferencia > 0 ? 'entrada' : 'salida';
        registrarMovimiento(
            $variante_id,
            $tipo,
            abs($diferencia),
            'Ajuste manual: ' . $motivo,
            $usuario_id
        );
    }

    return [
        'exito' => true,
        'mensaje' => 'Stock ajustado correctamente'
    ];
}

// ============================================
// OBTENER HISTORIAL DE MOVIMIENTOS
// ============================================
function obtenerMovimientos($variante_id = null, $limite = 50)
{
    $db = conectar();

    $sql = "
        SELECT m.*, p.nombre AS producto,
               v.talla, v.color,
               u.nombre AS usuario
        FROM movimientos_inventario m
        JOIN variantes v ON m.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        LEFT JOIN usuarios_roles u ON m.usuario_id = u.id
    ";

    $params = [];

    if ($variante_id) {
        $sql .= " WHERE m.variante_id = ?";
        $params[] = $variante_id;
    }

    $sql .= " ORDER BY m.fecha DESC LIMIT ?";
    $params[] = $limite;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER MOVIMIENTOS POR FECHA
// ============================================
function obtenerMovimientosPorFecha($fecha_inicio, $fecha_fin)
{
    $db = conectar();

    $stmt = $db->prepare("
        SELECT m.*, p.nombre AS producto,
               v.talla, v.color,
               u.nombre AS usuario
        FROM movimientos_inventario m
        JOIN variantes v ON m.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        LEFT JOIN usuarios_roles u ON m.usuario_id = u.id
        WHERE DATE(m.fecha) BETWEEN ? AND ?
        ORDER BY m.fecha DESC
    ");
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    return $stmt->fetchAll();
}

// ============================================
// RESUMEN DE INVENTARIO PARA DASHBOARD
// ============================================
function resumenInventario()
{
    $db = conectar();

    // Total de productos activos
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM productos WHERE activo = 1
    ");
    $stmt->execute();
    $total_productos = $stmt->fetch()['total'];

    // Total de variantes
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM variantes WHERE activo = 1
    ");
    $stmt->execute();
    $total_variantes = $stmt->fetch()['total'];

    // Productos agotados
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM variantes WHERE stock = 0 AND activo = 1
    ");
    $stmt->execute();
    $agotados = $stmt->fetch()['total'];

    // Productos con stock bajo (menor o igual a 5)
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM variantes WHERE stock <= 5 AND stock > 0 AND activo = 1
    ");
    $stmt->execute();
    $stock_bajo = $stmt->fetch()['total'];

    // Valor total del inventario
    $stmt = $db->prepare("
        SELECT SUM(stock * precio) AS valor FROM variantes WHERE activo = 1
    ");
    $stmt->execute();
    $valor_inventario = $stmt->fetch()['valor'] ?? 0;

    return [
        'total_productos' => $total_productos,
        'total_variantes' => $total_variantes,
        'agotados' => $agotados,
        'stock_bajo' => $stock_bajo,
        'valor_inventario' => $valor_inventario
    ];
}

// ============================================
// REPORTE DE INVENTARIO COMPLETO
// ============================================
function reporteInventario()
{
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre AS producto,
               c.nombre AS categoria,
               v.talla, v.color,
               v.precio, v.stock,
               (v.stock * v.precio) AS valor_total,
               CASE
                   WHEN v.stock = 0 THEN 'Agotado'
                   WHEN v.stock <= 5 THEN 'Stock bajo'
                   ELSE 'Disponible'
               END AS estado_stock
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        WHERE v.activo = 1 AND p.activo = 1
        ORDER BY v.stock ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}