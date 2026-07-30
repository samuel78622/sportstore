<?php
require_once '../../includes/auth.php';
require_once '../../includes/funciones_usuario.php';

soloAdmin();

$success = '';
$error = '';

// Cambiar rol de un usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'cambiar_rol') {
        $usuario_id = $_POST['usuario_id'];
        $nuevo_rol = $_POST['nuevo_rol'];
        $resultado = actualizarRol($usuario_id, $nuevo_rol);
        
        if ($resultado['exito']) {
            $success = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    }
    
    // Crear usuario desde admin
    if ($_POST['accion'] === 'crear_usuario') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rol = $_POST['rol'] ?? 'cliente';
        $telefono = $_POST['telefono'] ?? null;
        
        $resultado = crearUsuarioAdmin($nombre, $email, $password, $rol, $telefono);
        
        if ($resultado['exito']) {
            $success = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    }
}

// Obtener todos los usuarios
$usuarios = listarClientes();

// Definir los roles disponibles
$roles_disponibles = [
    'cliente'    => ['label' => 'Cliente', 'badge' => 'secondary'],
    'admin'      => ['label' => 'Administrador', 'badge' => 'danger'],
    'inventario' => ['label' => 'Gestor Inventario', 'badge' => 'warning'],
    'contador'   => ['label' => 'Contador', 'badge' => 'info'],
    'vendedor'   => ['label' => 'Vendedor', 'badge' => 'success']
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles — SportStore Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="../assets/css/usuarios/index.css" rel="stylesheet">

</head>

<body>

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="sidebar">
        <div class="sidebar-logo">⚡ SPORT<span>STORE</span></div>
        <nav class="sidebar-menu">
            <div class="menu-label">Principal</div>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <div class="menu-label">Catálogo</div>
            <a href="../productos/index.php" class="menu-item">
                <i class="fas fa-shirt"></i> Productos
            </a>
            <a href="../productos/crear.php" class="menu-item">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            <div class="menu-label">Inventario</div>
            <a href="../inventario/index.php" class="menu-item">
                <i class="fas fa-boxes-stacked"></i> Stock General
            </a>
            <a href="../inventario/movimientos.php" class="menu-item">
                <i class="fas fa-arrows-up-down"></i> Movimientos
            </a>
            <a href="../inventario/alertas.php" class="menu-item">
                <i class="fas fa-triangle-exclamation"></i> Alertas
            </a>
            <div class="menu-label">Ventas</div>
            <a href="../pedidos/index.php" class="menu-item">
                <i class="fas fa-bag-shopping"></i> Pedidos
            </a>
             <a href="../facturas/index.php" class="menu-item">
                <i class="fas fa-file-invoice"></i> Facturas
            </a>
            <a href="../cupones/index.php" class="menu-item">
                <i class="fas fa-tag"></i> Cupones
            </a>
            <div class="menu-label">Usuarios</div>
            <a href="../clientes/index.php" class="menu-item">
                <i class="fas fa-users"></i> Clientes
            </a>
            <a href="index.php" class="menu-item active">
                <i class="fas fa-shield-halved"></i> Gestionar Roles
            </a>
            <div class="menu-label">Reportes</div>
            <a href="../reportes/ventas.php" class="menu-item">
                <i class="fas fa-chart-line"></i> Ventas
            </a>
            <a href="../reportes/productos.php" class="menu-item">
                <i class="fas fa-star"></i> Más vendidos
            </a>
            <a href="../reportes/inventario.php" class="menu-item">
                <i class="fas fa-warehouse"></i> Inventario
            </a>
        </nav>
        <div class="sidebar-footer">
            <span style="color:#666; font-size:12px">
                👤 <?= htmlspecialchars($_SESSION['nombre']) ?>
            </span><br><br>
            <a href="../../logout.php">
                <i class="fas fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <!-- ════════════ CONTENIDO ════════════ -->
    <div class="main-content">

        <div class="page-header">
            <div>
                <h1><i class="fas fa-shield-halved me-2"></i>Gestión de Roles</h1>
                <small class="text-muted"><?= count($usuarios) ?> usuarios en el sistema</small>
            </div>
            <button class="btn-nuevo" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="fas fa-user-plus me-2"></i>Crear Usuario
            </button>
        </div>

        <!-- Alertas -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-circle-check me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-circle-xmark me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabla de usuarios -->
        <div class="tabla-usuarios">
            <table class="table table-hover" id="tablausuarios">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol Actual</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><strong>#<?= $usuario['id'] ?></strong></td>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['telefono'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge badge-rol bg-<?= $roles_disponibles[$usuario['rol']]['badge'] ?? 'secondary' ?>">
                                    <?= $roles_disponibles[$usuario['rol']]['label'] ?? $usuario['rol'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['activo'] == 1): ?>
                                    <span class="estado-activo"><i class="fas fa-check-circle"></i> Activo</span>
                                <?php else: ?>
                                    <span class="estado-inactivo"><i class="fas fa-ban"></i> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                            <td>
                                <div class="acciones">
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCambiarRol" onclick="prepararCambioRol(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>', '<?= $usuario['rol'] ?>')">
                                        <i class="fas fa-edit"></i> Cambiar Rol
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ════════════ MODAL CAMBIAR ROL ════════════ -->
    <div class="modal fade" id="modalCambiarRol" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>Cambiar Rol de Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="cambiar_rol">
                        <input type="hidden" name="usuario_id" id="usuarioId" value="">

                        <div class="form-group">
                            <label>Usuario:</label>
                            <p id="usuarioNombre" style="font-weight: 600; color: #667eea;"></p>
                        </div>

                        <div class="form-group">
                            <label for="nuevoRol">Seleccionar Nuevo Rol:</label>
                            <select name="nuevo_rol" id="nuevoRol" class="form-control" required>
                                <option value="">-- Seleccione un rol --</option>
                                <?php foreach ($roles_disponibles as $rol => $datos): ?>
                                    <option value="<?= $rol ?>">
                                        <?= $datos['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Al cambiar el rol, el usuario tendrá acceso a las nuevas funcionalidades en su próximo inicio de sesión.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Cambiar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ════════════ MODAL CREAR USUARIO ════════════ -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="crear_usuario">

                        <div class="form-group">
                            <label for="nombre">Nombre Completo:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono (Opcional):</label>
                            <input type="tel" name="telefono" id="telefono" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="rolNuevo">Rol:</label>
                            <select name="rol" id="rolNuevo" class="form-control" required>
                                <option value="">-- Seleccione un rol --</option>
                                <?php foreach ($roles_disponibles as $rol => $datos): ?>
                                    <option value="<?= $rol ?>">
                                        <?= $datos['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tablausuarios').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });

        // Preparar modal para cambiar rol
        function prepararCambioRol(usuarioId, usuarioNombre, rolActual) {
            document.getElementById('usuarioId').value = usuarioId;
            document.getElementById('usuarioNombre').textContent = usuarioNombre + ' (Rol actual: ' + rolActual + ')';
            document.getElementById('nuevoRol').value = '';
        }
    </script>

</body>

</html>
