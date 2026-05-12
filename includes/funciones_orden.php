<?php
// ============================================
// FUNCIONES DE ÓRDENES
// includes/funciones_orden.php
// ============================================

require_once 'conexion.php';
require_once 'funciones_carrito.php';
require_once 'funciones_producto.php';

// ============================================
// VERIFICAR STOCK
// ============================================
function verificarStock($carrito)
{

    $db = conectar();

    foreach ($carrito as $item) {

        // Validar que el item sea un arreglo válido
        if (
            !is_array($item) ||
            !isset($item['variante_id']) ||
            !isset($item['cantidad'])
        ) {
            continue;
        }

        $stmt = $db->prepare("
            SELECT 
                v.stock,
                v.talla,
                v.color,
                p.nombre
            FROM variantes v
            INNER JOIN productos p 
                ON v.producto_id = p.id
            WHERE v.id = ?
        ");

        $stmt->execute([
            $item['variante_id']
        ]);

        $variante = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar existencia
        if (!$variante) {

            return [
                'exito' => false,
                'mensaje' => 'Producto no encontrado'
            ];
        }

        // Verificar stock
        if ($variante['stock'] < $item['cantidad']) {

            return [
                'exito' => false,
                'mensaje' =>
                    'Stock insuficiente para: ' .
                    $variante['nombre'] .
                    ' | Talla: ' .
                    $variante['talla'] .
                    ' | Color: ' .
                    $variante['color']
            ];
        }
    }

    return [
        'exito' => true
    ];
}

// ============================================
// DESCONTAR STOCK
// ============================================
function descontarStock($carrito, $usuario_id = null)
{

    $db = conectar();

    foreach ($carrito as $item) {

        // Validar item
        if (
            !is_array($item) ||
            !isset($item['variante_id']) ||
            !isset($item['cantidad'])
        ) {
            continue;
        }

        // Actualizar stock
        $stmt = $db->prepare("
            UPDATE variantes
            SET stock = stock - ?
            WHERE id = ?
            AND stock >= ?
        ");

        $stmt->execute([
            $item['cantidad'],
            $item['variante_id'],
            $item['cantidad']
        ]);

        // Registrar movimiento
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
// COMPLETAR COMPRA
// ============================================
function completarCompra($usuario_id, $direccion_envio)
{

    $db = conectar();

    $carrito = obtenerCarrito();

    // Validar carrito
    if (
        empty($carrito) ||
        !is_array($carrito)
    ) {

        return [
            'exito' => false,
            'mensaje' => 'El carrito está vacío'
        ];
    }

    try {

        // Iniciar transacción
        $db->beginTransaction();

        // ====================================
        // VERIFICAR STOCK
        // ====================================
        $validacion = verificarStock($carrito);

        if (!$validacion['exito']) {

            throw new Exception(
                $validacion['mensaje']
            );
        }

        // ====================================
        // CALCULAR SUBTOTAL
        // ====================================
        $subtotal = 0;

        foreach ($carrito as $item) {

            if (
                is_array($item) &&
                isset($item['precio']) &&
                isset($item['cantidad'])
            ) {

                $subtotal +=
                    $item['precio'] *
                    $item['cantidad'];
            }
        }

        // ====================================
        // CUPÓN
        // ====================================
        $descuento =
            $_SESSION['cupon']['descuento'] ?? 0;

        $cupon_id =
            $_SESSION['cupon']['id'] ?? null;

        // ====================================
        // TOTAL
        // ====================================
        $total = $subtotal - $descuento;

        if ($total < 0) {
            $total = 0;
        }

        // ====================================
        // CREAR ORDEN
        // ====================================
        $stmt = $db->prepare("
            INSERT INTO ordenes (
                usuario_id,
                cupon_id,
                subtotal,
                descuento,
                total,
                estado,
                direccion_envio
            )
            VALUES (
                ?, ?, ?, ?, ?, 'pendiente', ?
            )
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

        // ====================================
        // DETALLE DE ORDEN
        // ====================================
        foreach ($carrito as $item) {

            // Validar item
            if (
                !is_array($item) ||
                !isset($item['variante_id']) ||
                !isset($item['precio']) ||
                !isset($item['cantidad'])
            ) {
                continue;
            }

            $subtotal_item =
                $item['precio'] *
                $item['cantidad'];

            $stmt = $db->prepare("
                INSERT INTO detalle_orden (
                    orden_id,
                    variante_id,
                    cantidad,
                    precio_unitario,
                    subtotal
                )
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

        // ====================================
        // DESCONTAR STOCK
        // ====================================
        descontarStock(
            $carrito,
            $usuario_id
        );

        // ====================================
        // ACTUALIZAR CUPÓN
        // ====================================
        if ($cupon_id) {

            $stmt = $db->prepare("
                UPDATE cupones
                SET usos_actuales =
                    usos_actuales + 1
                WHERE id = ?
            ");

            $stmt->execute([
                $cupon_id
            ]);
        }

        // ====================================
        // GENERAR FACTURA
        // ====================================
        generarFactura(
            $orden_id,
            $db
        );

        // ====================================
        // CONFIRMAR TRANSACCIÓN
        // ====================================
        $db->commit();

        // ====================================
        // LIMPIAR CARRITO
        // ====================================
        vaciarCarrito();

        unset($_SESSION['cupon']);

        return [
            'exito' => true,
            'mensaje' =>
                'Compra realizada correctamente',
            'orden_id' => $orden_id
        ];

    } catch (Exception $e) {

        // Revertir
        $db->rollBack();

        return [
            'exito' => false,
            'mensaje' => $e->getMessage()
        ];
    }
}

// ============================================
// GENERAR FACTURA
// ============================================
function generarFactura($orden_id, $db = null)
{

    if (!$db) {
        $db = conectar();
    }

    $numero_factura =
        'FAC-' .
        date('Ymd') .
        '-' .
        str_pad(
            $orden_id,
            5,
            '0',
            STR_PAD_LEFT
        );

    $stmt = $db->prepare("
        INSERT INTO facturas (
            orden_id,
            numero_factura
        )
        VALUES (?, ?)
    ");

    $stmt->execute([
        $orden_id,
        $numero_factura
    ]);

    return $numero_factura;
}

// ============================================
// OBTENER ORDEN
// ============================================
function obtenerOrden($orden_id)
{

    $db = conectar();

    $stmt = $db->prepare("
        SELECT
            o.*,
            u.nombre AS cliente,
            u.email,
            f.numero_factura
        FROM ordenes o
        INNER JOIN usuarios_roles u
            ON o.usuario_id = u.id
        LEFT JOIN facturas f
            ON f.orden_id = o.id
        WHERE o.id = ?
    ");

    $stmt->execute([
        $orden_id
    ]);

    return $stmt->fetch(
        PDO::FETCH_ASSOC
    );
}

// ============================================
// ITEMS ORDEN
// ============================================
if (!function_exists('obtenerItemsOrden')) {
    function obtenerItemsOrden($orden_id)
    {

        $db = conectar();

        $stmt = $db->prepare("
            SELECT
                d.*,
                p.nombre,
                p.imagen_principal,
                v.talla,
                v.color
            FROM detalle_orden d
            INNER JOIN variantes v
                ON d.variante_id = v.id
            INNER JOIN productos p
                ON v.producto_id = p.id
            WHERE d.orden_id = ?
        ");

        $stmt->execute([
            $orden_id
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }
}

// ============================================
// ÓRDENES CLIENTE
// ============================================
function obtenerOrdenesCliente($usuario_id)
{

    $db = conectar();

    $stmt = $db->prepare("
        SELECT
            o.*,
            f.numero_factura
        FROM ordenes o
        LEFT JOIN facturas f
            ON f.orden_id = o.id
        WHERE o.usuario_id = ?
        ORDER BY o.fecha DESC
    ");

    $stmt->execute([
        $usuario_id
    ]);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}

// ============================================
// LISTAR ÓRDENES
// ============================================
function listarOrdenes($estado = null)
{

    $db = conectar();

    $sql = "
        SELECT
            o.*,
            u.nombre AS cliente,
            f.numero_factura
        FROM ordenes o
        INNER JOIN usuarios_roles u
            ON o.usuario_id = u.id
        LEFT JOIN facturas f
            ON f.orden_id = o.id
    ";

    $params = [];

    if ($estado) {

        $sql .= "
            WHERE o.estado = ?
        ";

        $params[] = $estado;
    }

    $sql .= "
        ORDER BY o.fecha DESC
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );
}