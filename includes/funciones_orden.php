<?php
// ============================================
// FUNCIONES DE ÓRDENES
// includes/funciones_orden.php
// ============================================

require_once 'conexion.php';
require_once 'funciones_carrito.php';
require_once 'funciones_producto.php'; // ← aquí vive registrarMovimiento()

// ============================================
// VERIFICAR STOCK ANTES DE COMPRAR
// ============================================
function verificarStock($carrito) {
    $db = conectar();

    foreach ($carrito as $item) {
        $stmt = $db->prepare("
            SELECT v.stock, p.nombre, v.talla, v.color
            FROM variantes v
            JOIN productos p ON v.producto_id = p.id
            WHERE v.id = ?
        ");
        $stmt->execute([$item['variante_id']]);
        $variante = $stmt->fetch();

        if (!$variante || $variante['stock'] < $item['cantidad']) {
            return [
                'exito'   => false,
                'mensaje' => 'Stock insuficiente para: ' . $variante['nombre'] .
                             ' (Talla: ' . $variante['talla'] .
                             ' - Color: ' . $variante['color'] . ')'
            ];
        }
    }

    return ['exito' => true];
}

// ============================================
// DESCONTAR STOCK AL COMPLETAR COMPRA
// ============================================
function descontarStock($carrito, $usuario_id = null) {
    $db = conectar();

    foreach ($carrito as $item) {
        // Descontar stock
        $stmt = $db->prepare("
            UPDATE variantes
            SET stock = stock - ?
            WHERE id = ? AND stock >= ?
        ");
        $stmt->execute([
            $item['cantidad'],
            $item['variante_id'],
            $item['cantidad']
        ]);

        // Registrar movimiento de salida
        registrarMovimiento(
            $item['variante_id'],
            'salida',
            $item['cantidad'],
            'Venta realizada',
            $usuario_id
        );
    }
}

// ============================================
// COMPLETAR COMPRA — FUNCIÓN PRINCIPAL
// ============================================
function completarCompra($usuario_id, $direccion_envio) {
    $db      = conectar();
    $carrito = obtenerCarrito();

    // Verificar que el carrito no esté vacío
    if (empty($carrito)) {
        return [
            'exito'   => false,
            'mensaje' => 'El carrito está vacío'
        ];
    }

    try {
        // Iniciar transacción (todo o nada)
        $db->beginTransaction();

        // Paso 1: Verificar stock de todos los productos
        $validacion = verificarStock($carrito);
        if (!$validacion['exito']) {
            throw new Exception($validacion['mensaje']);
        }

        // Paso 2: Calcular totales
        $subtotal  = calcularSubtotal();
        $descuento = $_SESSION['cupon']['descuento'] ?? 0;
        $cupon_id  = $_SESSION['cupon']['id'] ?? null;
        $total     = calcularTotal();

        // Paso 3: Crear la orden
        $stmt = $db->prepare("
            INSERT INTO ordenes (usuario_id, cupon_id, subtotal, descuento, total, estado, direccion_envio)
            VALUES (?, ?, ?, ?, ?, 'pendiente', ?)
        ");
        $stmt->execute([
            $usuario_id,
            $cupon_id,
            $subtotal,
            $descuento,
            $total,
            $direccion_envio
        ]);
        $orden_id = $db->lastInsertId();

        // Paso 4: Guardar detalle de la orden
        foreach ($carrito as $item) {
            $subtotal_item = $item['precio'] * $item['cantidad'];

            $stmt = $db->prepare("
                INSERT INTO detalle_orden (orden_id, variante_id, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orden_id,
                $item['variante_id'],
                $item['cantidad'],
                $item['precio'],
                $subtotal_item
            ]);
        }

        // Paso 5: Descontar el stock de cada producto
        descontarStock($carrito, $usuario_id);

        // Paso 6: Actualizar usos del cupón si se usó uno
        if ($cupon_id) {
            $stmt = $db->prepare("
                UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE id = ?
            ");
            $stmt->execute([$cupon_id]);
        }

        // Paso 7: Generar número de factura
        generarFactura($orden_id, $db);

        // Todo salió bien — confirmar transacción
        $db->commit();

        // Limpiar carrito y cupón de la sesión
        vaciarCarrito();
        unset($_SESSION['cupon']);

        return [
            'exito'    => true,
            'mensaje'  => '¡Compra realizada exitosamente!',
            'orden_id' => $orden_id
        ];

    } catch (Exception $e) {
        // Algo falló — revertir todo
        $db->rollBack();

        return [
            'exito'   => false,
            'mensaje' => $e->getMessage()
        ];
    }
}

// ============================================
// GENERAR NÚMERO DE FACTURA
// ============================================
function generarFactura($orden_id, $db = null) {
    if (!$db) $db = conectar();

    $numero_factura = 'FAC-' . date('Ymd') . '-' . str_pad($orden_id, 5, '0', STR_PAD_LEFT);

    $stmt = $db->prepare("
        INSERT INTO facturas (orden_id, numero_factura)
        VALUES (?, ?)
    ");
    $stmt->execute([$orden_id, $numero_factura]);

    return $numero_factura;
}

// ============================================
// OBTENER DETALLE DE UNA ORDEN
// ============================================
function obtenerOrden($orden_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT o.*, u.nombre AS cliente, u.email,
               f.numero_factura
        FROM ordenes o
        JOIN usuarios_roles u ON o.usuario_id = u.id
        LEFT JOIN facturas f ON f.orden_id = o.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orden_id]);
    return $stmt->fetch();
}

// ============================================
// OBTENER ITEMS DE UNA ORDEN
// ============================================
function obtenerItemsOrden($orden_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT d.*, p.nombre, v.talla, v.color, p.imagen_principal
        FROM detalle_orden d
        JOIN variantes v ON d.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        WHERE d.orden_id = ?
    ");
    $stmt->execute([$orden_id]);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER ÓRDENES DE UN CLIENTE
// ============================================
function obtenerOrdenesCliente($usuario_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT o.*, f.numero_factura
        FROM ordenes o
        LEFT JOIN facturas f ON f.orden_id = o.id
        WHERE o.usuario_id = ?
        ORDER BY o.fecha DESC
    ");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

// ============================================
// LISTAR TODAS LAS ÓRDENES (ADMIN)
// ============================================
function listarOrdenes($estado = null) {
    $db = conectar();

    $sql = "
        SELECT o.*, u.nombre AS cliente, f.numero_factura
        FROM ordenes o
        JOIN usuarios_roles u ON o.usuario_id = u.id
        LEFT JOIN facturas f ON f.orden_id = o.id
    ";

    $params = [];

    if ($estado) {
        $sql .= " WHERE o.estado = ?";
        $params[] = $estado;
    }

    $sql .= " ORDER BY o.fecha DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================
// CAMBIAR ESTADO DE UNA ORDEN (ADMIN)
// ============================================
function cambiarEstadoOrden($orden_id, $nuevo_estado) {
    $db = conectar();

    $estados_validos = ['pendiente', 'empacado', 'enviado', 'entregado', 'cancelado'];

    if (!in_array($nuevo_estado, $estados_validos)) {
        return [
            'exito'   => false,
            'mensaje' => 'Estado no válido'
        ];
    }

    // Si se cancela la orden, devolver el stock
    if ($nuevo_estado === 'cancelado') {
        devolverStock($orden_id);
    }

    $stmt = $db->prepare("
        UPDATE ordenes SET estado = ? WHERE id = ?
    ");
    $stmt->execute([$nuevo_estado, $orden_id]);

    return [
        'exito'   => true,
        'mensaje' => 'Estado actualizado a: ' . $nuevo_estado
    ];
}

// ============================================
// DEVOLVER STOCK SI SE CANCELA LA ORDEN
// ============================================
function devolverStock($orden_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT variante_id, cantidad FROM detalle_orden WHERE orden_id = ?
    ");
    $stmt->execute([$orden_id]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        // Devolver stock
        $stmt = $db->prepare("
            UPDATE variantes SET stock = stock + ? WHERE id = ?
        ");
        $stmt->execute([$item['cantidad'], $item['variante_id']]);

        // Registrar movimiento de entrada por cancelación ✅ nombre correcto
        registrarMovimiento(
            $item['variante_id'],
            'entrada',
            $item['cantidad'],
            'Devolución por cancelación de orden #' . $orden_id
        );
    }
}

// ============================================
// ESTADÍSTICAS PARA EL DASHBOARD ADMIN
// ============================================
function estadisticasDashboard() {
    $db = conectar();

    // Total de órdenes hoy
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM ordenes
        WHERE DATE(fecha) = CURDATE()
    ");
    $stmt->execute();
    $ordenes_hoy = $stmt->fetch()['total'];

    // Ingresos del mes
    $stmt = $db->prepare("
        SELECT SUM(total) AS ingresos FROM ordenes
        WHERE MONTH(fecha) = MONTH(NOW())
        AND estado != 'cancelado'
    ");
    $stmt->execute();
    $ingresos_mes = $stmt->fetch()['ingresos'] ?? 0;

    // Total de clientes
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM usuarios_roles WHERE rol = 'cliente'
    ");
    $stmt->execute();
    $total_clientes = $stmt->fetch()['total'];

    // Productos con stock bajo
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM variantes WHERE stock <= 5 AND activo = 1
    ");
    $stmt->execute();
    $stock_bajo = $stmt->fetch()['total'];

    return [
        'ordenes_hoy'    => $ordenes_hoy,
        'ingresos_mes'   => $ingresos_mes,
        'total_clientes' => $total_clientes,
        'stock_bajo'     => $stock_bajo
    ];
}