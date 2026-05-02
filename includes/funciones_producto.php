<?php
// ============================================
// FUNCIONES DE PRODUCTOS
// includes/funciones_producto.php
// ============================================

require_once 'conexion.php';

// ============================================
// LISTAR TODOS LOS PRODUCTOS (CATÁLOGO)
// ============================================
function listarProductos($categoria_id = null, $coleccion = null, $busqueda = null) {
    $db = conectar();

    $sql = "
        SELECT p.*, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.activo = 1
    ";

    $params = [];

    if ($categoria_id) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoria_id;
    }

    if ($coleccion) {
        $sql .= " AND p.coleccion = ?";
        $params[] = $coleccion;
    }

    if ($busqueda) {
        $sql .= " AND p.nombre LIKE ?";
        $params[] = "%" . $busqueda . "%";
    }

    $sql .= " ORDER BY p.fecha_creacion DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================
// OBTENER UN PRODUCTO POR ID
// ============================================
function obtenerProducto($id) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.*, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id = ? AND p.activo = 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================
// OBTENER VARIANTES DE UN PRODUCTO
// ============================================
function obtenerVariantes($producto_id) {
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
function obtenerTallas($producto_id) {
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
function obtenerColoresPorTalla($producto_id, $talla) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT * FROM variantes
        WHERE producto_id = ? AND talla = ? AND stock > 0 AND activo = 1
    ");
    $stmt->execute([$producto_id, $talla]);
    return $stmt->fetchAll();
}

// ============================================
// CREAR PRODUCTO (ADMIN)
// ============================================
function crearProducto($nombre, $descripcion, $categoria_id, $coleccion, $imagen = null) {
    $db = conectar();

    $stmt = $db->prepare("
        INSERT INTO productos (nombre, descripcion, categoria_id, coleccion, imagen_principal)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $descripcion, $categoria_id, $coleccion, $imagen]);

    return [
        'exito'      => true,
        'mensaje'    => 'Producto creado correctamente',
        'producto_id' => $db->lastInsertId()
    ];
}

// ============================================
// EDITAR PRODUCTO (ADMIN)
// ============================================
function editarProducto($id, $nombre, $descripcion, $categoria_id, $coleccion, $imagen = null) {
    $db = conectar();

    if ($imagen) {
        $stmt = $db->prepare("
            UPDATE productos
            SET nombre = ?, descripcion = ?, categoria_id = ?, coleccion = ?, imagen_principal = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $descripcion, $categoria_id, $coleccion, $imagen, $id]);
    } else {
        $stmt = $db->prepare("
            UPDATE productos
            SET nombre = ?, descripcion = ?, categoria_id = ?, coleccion = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $descripcion, $categoria_id, $coleccion, $id]);
    }

    return [
        'exito'   => true,
        'mensaje' => 'Producto actualizado correctamente'
    ];
}

// ============================================
// ELIMINAR PRODUCTO (DESACTIVAR)
// ============================================
function eliminarProducto($id) {
    $db = conectar();

    $stmt = $db->prepare("
        UPDATE productos SET activo = 0 WHERE id = ?
    ");
    $stmt->execute([$id]);

    return [
        'exito'   => true,
        'mensaje' => 'Producto eliminado correctamente'
    ];
}

// ============================================
// AGREGAR VARIANTE A UN PRODUCTO
// ============================================
function agregarVariante($producto_id, $talla, $color, $precio, $stock) {
    $db = conectar();

    // Verificar si ya existe esa variante
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

    $stmt = $db->prepare("
        INSERT INTO variantes (producto_id, talla, color, precio, stock)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$producto_id, $talla, $color, $precio, $stock]);

    // Registrar entrada en movimientos
    registrarMovimiento($db->lastInsertId(), 'entrada', $stock, 'Stock inicial');

    return [
        'exito'   => true,
        'mensaje' => 'Variante agregada correctamente'
    ];
}

// ============================================
// SUBIR IMAGEN DE PRODUCTO
// ============================================
function subirImagenProducto($archivo) {
    $ruta_destino = __DIR__ . '/../uploads/productos/';
    $extension    = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $permitidas   = ['jpg', 'jpeg', 'png', 'webp'];

    // Validar extensión
    if (!in_array($extension, $permitidas)) {
        return [
            'exito'   => false,
            'mensaje' => 'Solo se permiten imágenes JPG, PNG o WEBP'
        ];
    }

    // Validar tamaño (máx 2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return [
            'exito'   => false,
            'mensaje' => 'La imagen no debe superar 2MB'
        ];
    }

    // Crear carpeta si no existe
    if (!is_dir($ruta_destino)) {
        if (!mkdir($ruta_destino, 0777, true)) {
            return [
                'exito'   => false,
                'mensaje' => 'No se pudo crear la carpeta de destino para la imagen'
            ];
        }
    }

    // Nombre único para evitar duplicados
    $nombre_nuevo = uniqid('prod_') . '.' . $extension;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino . $nombre_nuevo)) {
        return [
            'exito'  => true,
            'imagen' => $nombre_nuevo
        ];
    }

    return [
        'exito'   => false,
        'mensaje' => 'Error al subir la imagen'
    ];
}

// ============================================
// LISTAR CATEGORÍAS
// ============================================
function listarCategorias() {
    $db = conectar();

    $stmt = $db->prepare("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerPrecioMinimoProducto($producto_id) {
    $db = conectar();

    $stmt = $db->prepare("SELECT MIN(precio) AS precio FROM variantes WHERE producto_id = ? AND activo = 1");
    $stmt->execute([$producto_id]);
    $resultado = $stmt->fetch();

    return $resultado ? $resultado['precio'] : null;
}

function obtenerStockProducto($producto_id) {
    $db = conectar();

    $stmt = $db->prepare("SELECT SUM(stock) AS stock FROM variantes WHERE producto_id = ? AND activo = 1");
    $stmt->execute([$producto_id]);
    $resultado = $stmt->fetch();

    return $resultado && $resultado['stock'] !== null ? (int) $resultado['stock'] : 0;
}

// ============================================
// PRODUCTOS MÁS VENDIDOS (REPORTES)
// ============================================
function productosMasVendidos($limite = 10) {
    $db = conectar();

    $stmt = $db->prepare("
        SELECT p.nombre, p.imagen_principal,
               SUM(d.cantidad) AS total_vendido,
               SUM(d.subtotal) AS total_ingresos
        FROM detalle_orden d
        JOIN variantes v ON d.variante_id = v.id
        JOIN productos p ON v.producto_id = p.id
        GROUP BY p.id
        ORDER BY total_vendido DESC
        LIMIT ?
    ");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

// ============================================
// REGISTRAR MOVIMIENTO DE INVENTARIO
// ============================================
function registrarMovimiento($variante_id, $tipo, $cantidad, $motivo, $usuario_id = null) {
    $db = conectar();

    $stmt = $db->prepare("
        INSERT INTO movimientos_inventario (variante_id, tipo, cantidad, motivo, usuario_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$variante_id, $tipo, $cantidad, $motivo, $usuario_id]);
}