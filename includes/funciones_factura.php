<?php
// ============================================
// FUNCIONES DE FACTURA
// includes/funciones_factura.php
// ============================================

require_once 'conexion.php';
require_once 'funciones_orden.php';

// ============================================
// OBTENER FACTURA POR ORDEN ID
// ============================================
function obtenerFacturaPorOrden($orden_id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT f.*, o.subtotal, o.descuento, o.total,
               o.estado, o.fecha, o.direccion_envio,
               u.nombre AS cliente, u.email, u.telefono,
               c.codigo AS cupon_codigo
        FROM facturas f
        JOIN ordenes o ON f.orden_id = o.id
        JOIN usuarios u ON o.usuario_id = u.id
        LEFT JOIN cupones c ON o.cupon_id = c.id
        WHERE f.orden_id = ?
    ");
    $stmt->execute([$orden_id]);
    return $stmt->fetch();
}

// ============================================
// OBTENER FACTURA POR NÚMERO
// ============================================
function obtenerFacturaPorNumero($numero_factura) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT f.*, o.subtotal, o.descuento, o.total,
               o.estado, o.fecha, o.direccion_envio,
               u.nombre AS cliente, u.email, u.telefono
        FROM facturas f
        JOIN ordenes o ON f.orden_id = o.id
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE f.numero_factura = ?
    ");
    $stmt->execute([$numero_factura]);
    return $stmt->fetch();
}

// ============================================
// LISTAR TODAS LAS FACTURAS (ADMIN)
// ============================================
function listarFacturas($limite = 50) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT f.*, u.nombre AS cliente,
               o.total, o.estado
        FROM facturas f
        JOIN ordenes o ON f.orden_id = o.id
        JOIN usuarios u ON o.usuario_id = u.id
        ORDER BY f.fecha DESC
        LIMIT ?
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// GENERAR HTML DE LA FACTURA
// ============================================
function generarHTMLFactura($orden_id) {
    $factura = obtenerFacturaPorOrden($orden_id);
    $items   = obtenerItemsOrden($orden_id);

    if (!$factura) {
        return '<p>Factura no encontrada</p>';
    }

    // Construir tabla de productos
    $filas_productos = '';
    foreach ($items as $item) {
        $filas_productos .= "
            <tr>
                <td>{$item['nombre']}</td>
                <td>{$item['talla']} / {$item['color']}</td>
                <td style='text-align:center'>{$item['cantidad']}</td>
                <td style='text-align:right'>$" . number_format($item['precio_unitario'], 2) . "</td>
                <td style='text-align:right'>$" . number_format($item['subtotal'], 2) . "</td>
            </tr>
        ";
    }

    // Fila de descuento si aplica
    $fila_descuento = '';
    if ($factura['descuento'] > 0) {
        $fila_descuento = "
            <tr>
                <td colspan='4' style='text-align:right'><strong>Descuento (Cupón: {$factura['cupon_codigo']})</strong></td>
                <td style='text-align:right; color:green'>- $" . number_format($factura['descuento'], 2) . "</td>
            </tr>
        ";
    }

    $html = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Factura {$factura['numero_factura']}</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; font-size: 14px; color: #333; padding: 30px; }
            .factura-container { max-width: 800px; margin: 0 auto; }

            /* Encabezado */
            .factura-header { display: flex; justify-content: space-between; margin-bottom: 30px; }
            .empresa h1 { font-size: 24px; color: #111; }
            .empresa p { color: #666; font-size: 13px; }
            .factura-info { text-align: right; }
            .factura-info h2 { font-size: 20px; color: #111; }
            .numero-factura { font-size: 16px; color: #e44d26; font-weight: bold; }
            .fecha { color: #666; font-size: 13px; margin-top: 5px; }

            /* Datos del cliente */
            .datos-cliente { background: #f9f9f9; padding: 15px; border-radius: 6px; margin-bottom: 25px; }
            .datos-cliente h3 { margin-bottom: 8px; font-size: 14px; color: #555; text-transform: uppercase; }
            .datos-cliente p { margin: 3px 0; }

            /* Tabla de productos */
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            thead { background: #111; color: white; }
            thead th { padding: 10px 12px; text-align: left; font-size: 13px; }
            tbody td { padding: 10px 12px; border-bottom: 1px solid #eee; }
            tbody tr:hover { background: #fafafa; }

            /* Totales */
            .totales { width: 280px; margin-left: auto; }
            .totales table { margin-bottom: 0; }
            .totales td { padding: 8px 12px; }
            .totales .total-final { background: #111; color: white; font-weight: bold; font-size: 16px; }

            /* Estado */
            .estado { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
            .estado-pendiente  { background: #fff3cd; color: #856404; }
            .estado-empacado   { background: #cce5ff; color: #004085; }
            .estado-enviado    { background: #d4edda; color: #155724; }
            .estado-entregado  { background: #d1ecf1; color: #0c5460; }
            .estado-cancelado  { background: #f8d7da; color: #721c24; }

            /* Pie */
            .factura-footer { text-align: center; margin-top: 40px; color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; }

            /* Botones solo para pantalla */
            .acciones { text-align: center; margin: 20px 0; }
            .btn-imprimir { background: #111; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-size: 14px; }
            @media print { .acciones { display: none; } }
        </style>
    </head>
    <body>
    <div class='factura-container'>

        <!-- Botón imprimir -->
        <div class='acciones'>
            <button class='btn-imprimir' onclick='window.print()'>🖨️ Imprimir Factura</button>
        </div>

        <!-- Encabezado -->
        <div class='factura-header'>
            <div class='empresa'>
                <h1>⚡ SportStore</h1>
                <p>Tu tienda deportiva de confianza</p>
                <p>contacto@sportstore.com</p>
            </div>
            <div class='factura-info'>
                <h2>FACTURA</h2>
                <div class='numero-factura'>{$factura['numero_factura']}</div>
                <div class='fecha'>Fecha: " . date('d/m/Y', strtotime($factura['fecha'])) . "</div>
                <div style='margin-top:8px'>
                    <span class='estado estado-{$factura['estado']}'>" . ucfirst($factura['estado']) . "</span>
                </div>
            </div>
        </div>

        <!-- Datos del cliente -->
        <div class='datos-cliente'>
            <h3>Datos del cliente</h3>
            <p><strong>Nombre:</strong> {$factura['cliente']}</p>
            <p><strong>Email:</strong> {$factura['email']}</p>
            <p><strong>Teléfono:</strong> {$factura['telefono']}</p>
            <p><strong>Dirección de envío:</strong> {$factura['direccion_envio']}</p>
        </div>

        <!-- Tabla de productos -->
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Talla / Color</th>
                    <th style='text-align:center'>Cantidad</th>
                    <th style='text-align:right'>Precio unit.</th>
                    <th style='text-align:right'>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {$filas_productos}
            </tbody>
        </table>

        <!-- Totales -->
        <div class='totales'>
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td style='text-align:right'>$" . number_format($factura['subtotal'], 2) . "</td>
                </tr>
                {$fila_descuento}
                <tr class='total-final'>
                    <td>TOTAL</td>
                    <td style='text-align:right'>$" . number_format($factura['total'], 2) . "</td>
                </tr>
            </table>
        </div>

        <!-- Pie de página -->
        <div class='factura-footer'>
            <p>¡Gracias por tu compra! — SportStore © " . date('Y') . "</p>
            <p>Este documento es una factura válida de compra</p>
        </div>

    </div>
    </body>
    </html>
    ";

    return $html;
}

// ============================================
// ESTADÍSTICAS DE FACTURACIÓN (ADMIN)
// ============================================
function estadisticasFacturacion() {
    $db = conectar();

    // Ingresos del día
    $stmt = $db->prepare("
        SELECT SUM(o.total) AS total
        FROM ordenes o
        WHERE DATE(o.fecha) = CURDATE()
        AND o.estado != 'cancelado'
    ");
    $stmt->execute();
    $ingresos_hoy = $stmt->fetch()['total'] ?? 0;

    // Ingresos del mes
    $stmt = $db->prepare("
        SELECT SUM(o.total) AS total
        FROM ordenes o
        WHERE MONTH(o.fecha) = MONTH(NOW())
        AND YEAR(o.fecha) = YEAR(NOW())
        AND o.estado != 'cancelado'
    ");
    $stmt->execute();
    $ingresos_mes = $stmt->fetch()['total'] ?? 0;

    // Ingresos del año
    $stmt = $db->prepare("
        SELECT SUM(o.total) AS total
        FROM ordenes o
        WHERE YEAR(o.fecha) = YEAR(NOW())
        AND o.estado != 'cancelado'
    ");
    $stmt->execute();
    $ingresos_anio = $stmt->fetch()['total'] ?? 0;

    // Total de facturas emitidas
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM facturas");
    $stmt->execute();
    $total_facturas = $stmt->fetch()['total'];

    return [
        'ingresos_hoy'   => $ingresos_hoy,
        'ingresos_mes'   => $ingresos_mes,
        'ingresos_anio'  => $ingresos_anio,
        'total_facturas' => $total_facturas
    ];
}