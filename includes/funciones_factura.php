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
        JOIN usuarios_roles u ON o.usuario_id = u.id
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
        JOIN usuarios_roles u ON o.usuario_id = u.id
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
        JOIN usuarios_roles u ON o.usuario_id = u.id
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

// ============================================
// GENERAR PDF FACTURA CON mPDF
// ============================================
function generarPDFFactura($orden_id, $descarga = true) {
    try {
        // Incluir autoload de Composer
        require_once __DIR__ . '/../vendor/autoload.php';

        // Obtener datos de la factura
        $factura = obtenerFacturaPorOrden($orden_id);
        $items   = obtenerItemsOrden($orden_id);

        if (!$factura) {
            return [
                'exito'   => false,
                'mensaje' => 'Factura no encontrada'
            ];
        }

        // Crear instancia de mPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4',
            'margin_left' => 15,
            'margin_right'=> 15,
            'margin_top'  => 15,
            'margin_bottom' => 15,
        ]);

        // Construir tabla de productos
        $filas_productos = '';
        foreach ($items as $item) {
            $filas_productos .= "
                <tr>
                    <td style='width:35%'>{$item['nombre']}</td>
                    <td style='width:20%; text-align:center'>{$item['talla']} / {$item['color']}</td>
                    <td style='width:15%; text-align:center'>{$item['cantidad']}</td>
                    <td style='width:15%; text-align:right'>\$" . number_format($item['precio_unitario'], 2) . "</td>
                    <td style='width:15%; text-align:right'>\$" . number_format($item['subtotal'], 2) . "</td>
                </tr>
            ";
        }

        // Fila de descuento si aplica
        $fila_descuento = '';
        if ($factura['descuento'] > 0) {
            $descuento_porcentaje = round(($factura['descuento'] / $factura['subtotal']) * 100);
            $fila_descuento = "
                <tr style='background:#fff9e6'>
                    <td colspan='4' style='text-align:right'><strong>Descuento Cupón ({$descuento_porcentaje}%)</strong></td>
                    <td style='text-align:right; color:#28a745'><strong>-\$" . number_format($factura['descuento'], 2) . "</strong></td>
                </tr>
            ";
        }

        // Generar QR con URL
        $qr_data = 'sportstore.local/verificar-factura?numero=' . $factura['numero_factura'];
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qr_data);

        // HTML para PDF
        $html = "
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Factura {$factura['numero_factura']}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }

                /* Encabezado */
                .factura-header { 
                    display: flex; 
                    justify-content: space-between; 
                    align-items: flex-start;
                    margin-bottom: 20px; 
                    border-bottom: 2px solid #111;
                    padding-bottom: 15px;
                }
                .empresa h1 { font-size: 28px; color: #111; margin-bottom: 5px; }
                .empresa p { color: #666; font-size: 10px; margin: 2px 0; }
                .factura-info { text-align: right; }
                .factura-info h2 { font-size: 18px; color: #111; margin-bottom: 5px; }
                .numero-factura { font-size: 16px; color: #e44d26; font-weight: bold; }
                .fecha { color: #666; font-size: 9px; margin: 3px 0; }
                .estado { 
                    display: inline-block; 
                    padding: 3px 10px; 
                    border-radius: 3px; 
                    font-size: 10px; 
                    font-weight: bold;
                    margin-top: 5px;
                }
                .estado-pendiente  { background: #fff3cd; color: #856404; }
                .estado-empacado   { background: #cce5ff; color: #004085; }
                .estado-enviado    { background: #d4edda; color: #155724; }
                .estado-entregado  { background: #d1ecf1; color: #0c5460; }

                /* Datos del cliente */
                .datos-section {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 15px;
                }
                .datos-box {
                    width: 48%;
                    background: #f5f5f5;
                    padding: 10px;
                    border-radius: 4px;
                    font-size: 10px;
                }
                .datos-box h3 { 
                    margin-bottom: 5px; 
                    font-size: 11px; 
                    color: #555; 
                    text-transform: uppercase;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 3px;
                }
                .datos-box p { margin: 2px 0; }

                /* Tabla de productos */
                table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                thead { background: #111; color: white; }
                thead th { 
                    padding: 8px 5px; 
                    text-align: left; 
                    font-size: 10px;
                    font-weight: bold;
                }
                tbody td { 
                    padding: 6px 5px; 
                    border-bottom: 1px solid #eee; 
                    font-size: 10px;
                }
                tbody tr:nth-child(even) { background: #f9f9f9; }

                /* Totales */
                .totales-section {
                    display: flex;
                    justify-content: flex-end;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                .totales-box {
                    width: 250px;
                }
                .totales-box table {
                    margin-bottom: 0;
                    width: 100%;
                }
                .totales-box td {
                    padding: 5px 8px;
                    border-bottom: 1px solid #ddd;
                    font-size: 10px;
                }
                .total-final {
                    background: #111 !important;
                    color: white !important;
                    font-weight: bold !important;
                    font-size: 12px !important;
                }

                /* Pie con QR */
                .factura-footer {
                    border-top: 2px solid #111;
                    padding-top: 10px;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                }
                .footer-text {
                    flex: 1;
                    font-size: 9px;
                    color: #999;
                    line-height: 1.4;
                }
                .footer-qr {
                    text-align: center;
                }
                .footer-qr img {
                    width: 100px;
                    height: 100px;
                }
                .qr-label {
                    font-size: 8px;
                    color: #666;
                    margin-top: 3px;
                }
            </style>
        </head>
        <body>

            <!-- Encabezado -->
            <div class='factura-header'>
                <div class='empresa'>
                    <h1>⚡ SportStore</h1>
                    <p><strong>Tu tienda deportiva de confianza</strong></p>
                    <p>Email: contacto@sportstore.com</p>
                    <p>Teléfono: +57 (601) 1234567</p>
                    <p>Dirección: Calle 50 #12-34, Bogotá, Colombia</p>
                </div>
                <div class='factura-info'>
                    <h2>FACTURA</h2>
                    <div class='numero-factura'>{$factura['numero_factura']}</div>
                    <div class='fecha'>Fecha: " . date('d/m/Y H:i', strtotime($factura['fecha'])) . "</div>
                    <div class='estado estado-{$factura['estado']}'>" . ucfirst($factura['estado']) . "</div>
                </div>
            </div>

            <!-- Datos del cliente y envío -->
            <div class='datos-section'>
                <div class='datos-box'>
                    <h3>Datos del Cliente</h3>
                    <p><strong>Nombre:</strong> {$factura['cliente']}</p>
                    <p><strong>Email:</strong> {$factura['email']}</p>
                    <p><strong>Teléfono:</strong> {$factura['telefono']}</p>
                </div>
                <div class='datos-box'>
                    <h3>Dirección de Envío</h3>
                    <p>{$factura['direccion_envio']}</p>
                </div>
            </div>

            <!-- Tabla de productos -->
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Talla / Color</th>
                        <th style='text-align:center; width:10%'>Cantidad</th>
                        <th style='text-align:right; width:12%'>Precio unit.</th>
                        <th style='text-align:right; width:12%'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {$filas_productos}
                </tbody>
            </table>

            <!-- Totales -->
            <div class='totales-section'>
                <div class='totales-box'>
                    <table>
                        <tr>
                            <td><strong>Subtotal</strong></td>
                            <td style='text-align:right'>\$" . number_format($factura['subtotal'], 2) . "</td>
                        </tr>
                        {$fila_descuento}
                        <tr class='total-final'>
                            <td><strong>TOTAL</strong></td>
                            <td style='text-align:right'>\$" . number_format($factura['total'], 2) . "</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Pie de página con QR -->
            <div class='factura-footer'>
                <div class='footer-text'>
                    <p><strong>¡Gracias por tu compra!</strong></p>
                    <p>SportStore © " . date('Y') . "</p>
                    <p>Este documento es una factura válida de compra</p>
                    <p style='margin-top:3px'>Número de Orden: #{$factura['orden_id']}</p>
                </div>
                <div class='footer-qr'>
                    <img src='{$qr_url}' alt='QR Factura'>
                    <div class='qr-label'>Verificar factura</div>
                </div>
            </div>

        </body>
        </html>
        ";

        // Escribir HTML en mPDF
        $mpdf->WriteHTML($html);

        // Nombre del archivo
        $nombre_archivo = 'Factura_' . $factura['numero_factura'] . '.pdf';

        if ($descarga) {
            // Descargar PDF
            $mpdf->Output($nombre_archivo, 'D');
        } else {
            // Guardar PDF en el servidor
            $ruta_pdf = __DIR__ . '/../uploads/facturas/' . $nombre_archivo;

            // Crear carpeta si no existe
            if (!is_dir(__DIR__ . '/../uploads/facturas')) {
                mkdir(__DIR__ . '/../uploads/facturas', 0755, true);
            }

            $mpdf->Output($ruta_pdf, 'F');

            return [
                'exito'      => true,
                'mensaje'    => 'PDF generado correctamente',
                'ruta_pdf'   => $ruta_pdf,
                'archivo'    => $nombre_archivo
            ];
        }

    } catch (Exception $e) {
        return [
            'exito'   => false,
            'mensaje' => 'Error al generar PDF: ' . $e->getMessage()
        ];
    }
}