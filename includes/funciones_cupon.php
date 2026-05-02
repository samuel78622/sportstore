<?php
// ============================================
// FUNCIONES DE CUPONES
// includes/funciones_cupon.php
// ============================================

require_once 'conexion.php';

// ============================================
// LISTAR TODOS LOS CUPONES (ADMIN)
// ============================================
function listarCupones() {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT *,
               CASE
                   WHEN activo = 0 THEN 'Inactivo'
                   WHEN fecha_vencimiento < CURDATE() THEN 'Vencido'
                   WHEN usos_actuales >= usos_maximos THEN 'Agotado'
                   ELSE 'Activo'
               END AS estado_cupon
        FROM cupones
        ORDER BY fecha_creacion DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ============================================
// OBTENER UN CUPÓN POR ID
// ============================================
function obtenerCupon($id) {
    $db = conectar();

    $stmt = $db->prepare("SELECT * FROM cupones WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================
// CREAR CUPÓN (ADMIN)
// ============================================
function crearCupon($codigo, $tipo, $descuento, $usos_maximos, $fecha_vencimiento) {
    $db = conectar();

    // Verificar que el código no exista
    $stmt = $db->prepare("SELECT id FROM cupones WHERE codigo = ?");
    $stmt->execute([$codigo]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'Ya existe un cupón con ese código'
        ];
    }

    // Validar tipo
    if (!in_array($tipo, ['porcentaje', 'monto_fijo'])) {
        return [
            'exito'   => false,
            'mensaje' => 'Tipo de cupón no válido'
        ];
    }

    // Validar descuento
    if ($descuento <= 0) {
        return [
            'exito'   => false,
            'mensaje' => 'El descuento debe ser mayor a 0'
        ];
    }

    // Validar porcentaje no mayor a 100
    if ($tipo === 'porcentaje' && $descuento > 100) {
        return [
            'exito'   => false,
            'mensaje' => 'El porcentaje no puede ser mayor a 100'
        ];
    }

    $stmt = $db->prepare("
        INSERT INTO cupones (codigo, tipo, descuento, usos_maximos, fecha_vencimiento)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        strtoupper($codigo),
        $tipo,
        $descuento,
        $usos_maximos,
        $fecha_vencimiento ?: null
    ]);

    return [
        'exito'   => true,
        'mensaje' => 'Cupón creado correctamente'
    ];
}

// ============================================
// EDITAR CUPÓN (ADMIN)
// ============================================
function editarCupon($id, $codigo, $tipo, $descuento, $usos_maximos, $fecha_vencimiento) {
    $db = conectar();

    // Verificar que el código no lo use otro cupón
    $stmt = $db->prepare("SELECT id FROM cupones WHERE codigo = ? AND id != ?");
    $stmt->execute([$codigo, $id]);

    if ($stmt->fetch()) {
        return [
            'exito'   => false,
            'mensaje' => 'Ya existe otro cupón con ese código'
        ];
    }

    $stmt = $db->prepare("
        UPDATE cupones
        SET codigo = ?, tipo = ?, descuento = ?, usos_maximos = ?, fecha_vencimiento = ?
        WHERE id = ?
    ");
    $stmt->execute([
        strtoupper($codigo),
        $tipo,
        $descuento,
        $usos_maximos,
        $fecha_vencimiento ?: null,
        $id
    ]);

    return [
        'exito'   => true,
        'mensaje' => 'Cupón actualizado correctamente'
    ];
}

// ============================================
// ACTIVAR O DESACTIVAR CUPÓN (ADMIN)
// ============================================
function toggleCupon($id) {
    $db = conectar();

    $stmt = $db->prepare("
        UPDATE cupones SET activo = IF(activo = 1, 0, 1) WHERE id = ?
    ");
    $stmt->execute([$id]);

    return [
        'exito'   => true,
        'mensaje' => 'Estado del cupón actualizado'
    ];
}

// ============================================
// ELIMINAR CUPÓN (ADMIN)
// ============================================
function eliminarCupon($id) {
    $db = conectar();

    // Verificar que no esté siendo usado en órdenes
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM ordenes WHERE cupon_id = ?");
    $stmt->execute([$id]);
    $total = $stmt->fetch()['total'];

    if ($total > 0) {
        return [
            'exito'   => false,
            'mensaje' => 'No se puede eliminar, el cupón tiene ' . $total . ' órdenes asociadas'
        ];
    }

    $stmt = $db->prepare("DELETE FROM cupones WHERE id = ?");
    $stmt->execute([$id]);

    return [
        'exito'   => true,
        'mensaje' => 'Cupón eliminado correctamente'
    ];
}

// ============================================
// VALIDAR CUPÓN (ANTES DE APLICARLO)
// ============================================
function validarCupon($codigo) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT * FROM cupones
        WHERE codigo = ?
        AND activo = 1
        AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
        AND usos_actuales < usos_maximos
    ");
    $stmt->execute([strtoupper($codigo)]);
    $cupon = $stmt->fetch();

    if (!$cupon) {
        return [
            'exito'   => false,
            'mensaje' => 'El cupón es inválido, está vencido o ya fue agotado'
        ];
    }

    return [
        'exito' => true,
        'cupon' => $cupon
    ];
}

// ============================================
// CALCULAR DESCUENTO DE UN CUPÓN
// ============================================
function calcularDescuentoCupon($cupon, $subtotal) {
    if ($cupon['tipo'] === 'porcentaje') {
        $descuento = $subtotal * ($cupon['descuento'] / 100);
    } else {
        $descuento = $cupon['descuento'];
    }

    // El descuento no puede ser mayor al subtotal
    return min($descuento, $subtotal);
}

// ============================================
// ESTADÍSTICAS DE CUPONES (ADMIN)
// ============================================
function estadisticasCupones() {
    $db = conectar();

    // Total de cupones activos
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM cupones WHERE activo = 1
    ");
    $stmt->execute();
    $activos = $stmt->fetch()['total'];

    // Total de cupones vencidos
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total FROM cupones
        WHERE fecha_vencimiento < CURDATE()
    ");
    $stmt->execute();
    $vencidos = $stmt->fetch()['total'];

    // Cupón más usado
    $stmt = $db->prepare("
        SELECT codigo, usos_actuales FROM cupones
        ORDER BY usos_actuales DESC
        LIMIT 1
    ");
    $stmt->execute();
    $mas_usado = $stmt->fetch();

    // Total de descuentos aplicados
    $stmt = $db->prepare("
        SELECT SUM(descuento) AS total FROM ordenes WHERE descuento > 0
    ");
    $stmt->execute();
    $total_descuentos = $stmt->fetch()['total'] ?? 0;

    return [
        'activos'          => $activos,
        'vencidos'         => $vencidos,
        'mas_usado'        => $mas_usado,
        'total_descuentos' => $total_descuentos
    ];
}