<?php
// ============================================
// FUNCIONES DE REPORTES
// includes/funciones_reportes.php
// ============================================

require_once 'conexion.php';

// ============================================
// REPORTE DE VENTAS POR DÍA
// ============================================
function ventasPorDia($limite = 30) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT DATE(fecha) AS dia,
               COUNT(*) AS total_ordenes,
               SUM(total) AS ingresos
        FROM ordenes
        WHERE estado != 'cancelado'
        AND fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(fecha)
        ORDER BY dia DESC
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE VENTAS POR MES
// ============================================
function ventasPorMes($anio = null) {
    $db  = conectar();
    $anio = $anio ?? date('Y');

    $stmt = $db->prepare("
        SELECT MONTH(fecha) AS mes,
               MONTHNAME(fecha) AS nombre_mes,
               COUNT(*) AS total_ordenes,
               SUM(total) AS ingresos
        FROM ordenes
        WHERE YEAR(fecha) = ?
        AND estado != 'cancelado'
        GROUP BY MONTH(fecha)
        ORDER BY mes ASC
    ");
    $stmt->execute([$anio]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE VENTAS POR CATEGORÍA
// ============================================
function ventasPorCategoria() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT c.nombre AS categoria,
               SUM(d.cantidad) AS unidades_vendidas,
               SUM(d.subtotal) AS ingresos
        FROM detalle_orden d
        JOIN variantes v ON d.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        JOIN ordenes o ON d.orden_id = o.id
        WHERE o.estado != 'cancelado'
        GROUP BY c.id
        ORDER BY ingresos DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE PRODUCTOS MÁS VENDIDOS
// ============================================
function reporteProductosMasVendidos($limite = 10) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre AS producto,
               p.imagen_principal,
               c.nombre AS categoria,
               SUM(d.cantidad) AS unidades_vendidas,
               SUM(d.subtotal) AS ingresos_generados
        FROM detalle_orden d
        JOIN variantes v ON d.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        JOIN ordenes o ON d.orden_id = o.id
        WHERE o.estado != 'cancelado'
        GROUP BY p.id
        ORDER BY unidades_vendidas DESC
        LIMIT ?
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE TALLAS MÁS VENDIDAS
// ============================================
function tallasMasVendidas() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT v.talla,
               SUM(d.cantidad) AS unidades_vendidas
        FROM detalle_orden d
        JOIN variantes v ON d.variante_id = v.id
        JOIN ordenes o ON d.orden_id = o.id
        WHERE o.estado != 'cancelado'
        GROUP BY v.talla
        ORDER BY unidades_vendidas DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE CLIENTES MÁS ACTIVOS
// ============================================
function clientesMasActivos($limite = 10) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT u.nombre, u.email,
               COUNT(o.id) AS total_ordenes,
               SUM(o.total) AS total_gastado
        FROM ordenes o
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE o.estado != 'cancelado'
        GROUP BY u.id
        ORDER BY total_gastado DESC
        LIMIT ?
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE ÓRDENES POR ESTADO
// ============================================
function ordenesPorEstado() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT estado,
               COUNT(*) AS total,
               SUM(total) AS monto_total
        FROM ordenes
        GROUP BY estado
        ORDER BY total DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE STOCK CRÍTICO
// ============================================
function reporteStockCritico($limite = 5) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre AS producto,
               c.nombre AS categoria,
               v.talla, v.color, v.stock,
               CASE
                   WHEN v.stock = 0 THEN 'Agotado'
                   ELSE 'Stock bajo'
               END AS estado
        FROM variantes v
        JOIN productos p ON v.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        WHERE v.stock <= ? AND v.activo = 1 AND p.activo = 1
        ORDER BY v.stock ASC
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE DE INGRESOS VS META MENSUAL
// ============================================
function ingresosMensuales($meses = 6) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT DATE_FORMAT(fecha, '%Y-%m') AS periodo,
               DATE_FORMAT(fecha, '%M %Y') AS nombre_periodo,
               COUNT(*) AS total_ordenes,
               SUM(total) AS ingresos,
               SUM(descuento) AS total_descuentos
        FROM ordenes
        WHERE estado != 'cancelado'
        AND fecha >= DATE_SUB(NOW(), INTERVAL ? MONTH)
        GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY periodo ASC
    ");
    $stmt->execute([$meses]);
    return $stmt->fetchAll();
}

// ============================================
// REPORTE GENERAL PARA DASHBOARD
// ============================================
function reporteGeneral() {
    $db = conectar();

    // Ventas de hoy
    $stmt = $db->prepare("
        SELECT COUNT(*) AS ordenes, SUM(total) AS ingresos
        FROM ordenes
        WHERE DATE(fecha) = CURDATE()
        AND estado != 'cancelado'
    ");
    $stmt->execute();
    $hoy = $stmt->fetch();

    // Ventas de ayer
    $stmt = $db->prepare("
        SELECT COUNT(*) AS ordenes, SUM(total) AS ingresos
        FROM ordenes
        WHERE DATE(fecha) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        AND estado != 'cancelado'
    ");
    $stmt->execute();
    $ayer = $stmt->fetch();

    // Ventas del mes actual
    $stmt = $db->prepare("
        SELECT COUNT(*) AS ordenes, SUM(total) AS ingresos
        FROM ordenes
        WHERE MONTH(fecha) = MONTH(NOW())
        AND YEAR(fecha) = YEAR(NOW())
        AND estado != 'cancelado'
    ");
    $stmt->execute();
    $mes = $stmt->fetch();

    // Calcular variación respecto a ayer
    $variacion = 0;
    if ($ayer['ingresos'] > 0) {
        $variacion = (($hoy['ingresos'] - $ayer['ingresos']) / $ayer['ingresos']) * 100;
    }

    return [
        'hoy'      => $hoy,
        'ayer'     => $ayer,
        'mes'      => $mes,
        'variacion' => round($variacion, 2)
    ];
}

// ============================================
// EXPORTAR REPORTE A CSV
// ============================================
function exportarCSV($datos, $nombre_archivo, $encabezados) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '.csv"');

    $output = fopen('php://output', 'w');

    // BOM para que Excel reconozca tildes
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Escribir encabezados
    fputcsv($output, $encabezados);

    // Escribir datos
    foreach ($datos as $fila) {
        fputcsv($output, $fila);
    }

    fclose($output);
    exit();
}