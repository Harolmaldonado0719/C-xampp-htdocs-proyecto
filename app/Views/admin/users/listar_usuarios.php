
<?php
// Variables esperadas: $pageTitle, $active_page, $trabajadores, $clientes, $usuarios_recientes
?>
<div class="container mt-4">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Gestionar Usuarios'; ?></h2>

    <?php include __DIR__ . '/../../partials/mensajes.php'; ?>

    <div class="mb-3">
        <a href="<?php echo BASE_URL; ?>admin/users/create" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Usuario
        </a>
    </div>

    <!-- Sección de Trabajadores (Administradores y Empleados) -->
    <div class="dashboard-section mb-4">
        <h3><i class="fas fa-user-shield"></i> Trabajadores (Administradores y Empleados)</h3>
        <?php if (isset($trabajadores) && !empty($trabajadores)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajadores as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td>
                                    <?php 
                                    $fotografiaUsuarioUrlListar = '';
                                    if (!empty($usuario['fotografia'])) {
                                        $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                                        $fotografiaUsuarioUrlListar = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($usuario['fotografia']);
                                    } else {
                                        $fotografiaUsuarioUrlListar = BASE_URL . 'img/default-avatar.png'; 
                                    }
                                    ?>
                                    <img src="<?php echo $fotografiaUsuarioUrlListar; ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <?php 
                                        if (defined('ID_ROL_ADMIN') && $usuario['rol_id'] == ID_ROL_ADMIN) {
                                            echo '<span class="badge bg-danger">Administrador</span>';
                                        } elseif (defined('ID_ROL_EMPLEADO') && $usuario['rol_id'] == ID_ROL_EMPLEADO) {
                                            echo '<span class="badge bg-info text-dark">Empleado</span>';
                                        } else { 
                                            echo '<span class="badge bg-light text-dark">Rol Desconocido (' . htmlspecialchars($usuario['rol_id']) . ')</span>';
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'N/D'); ?></td>
                                <td>
                                    <?php if (isset($usuario['activo']) && $usuario['activo'] == 1): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/users/edit/<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary mb-1" title="Editar Usuario">
                                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Editar</span>
                                    </a>
                                    <?php if (defined('ID_ROL_EMPLEADO') && isset($usuario['rol_id']) && $usuario['rol_id'] == ID_ROL_EMPLEADO): ?>
                                        <a href="<?php echo BASE_URL; ?>admin/empleado/<?php echo $usuario['id']; ?>/servicios" class="btn btn-sm btn-warning ms-1 mb-1" title="Gestionar Servicios del Empleado">
                                            <i class="fas fa-concierge-bell"></i> <span class="d-none d-md-inline">Servicios</span>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/empleado/<?php echo $usuario['id']; ?>/horario" class="btn btn-sm btn-info ms-1 mb-1" title="Gestionar Horario del Empleado">
                                            <i class="fas fa-calendar-alt"></i> <span class="d-none d-md-inline">Horario</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user_id']) && $usuario['id'] != $_SESSION['user_id']): ?>
                                        <?php if (isset($usuario['activo']) && $usuario['activo'] == 1): ?>
                                            <form action="<?php echo BASE_URL; ?>admin/users/delete/<?php echo $usuario['id']; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres DESACTIVAR a este usuario?');">
                                                <button type="submit" class="btn btn-sm btn-danger ms-1 mb-1" title="Desactivar Usuario">
                                                    <i class="fas fa-user-slash"></i> <span class="d-none d-md-inline">Desactivar</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo BASE_URL; ?>admin/users/activate/<?php echo $usuario['id']; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres ACTIVAR a este usuario?');">
                                                <button type="submit" class="btn btn-sm btn-success ms-1 mb-1" title="Activar Usuario">
                                                    <i class="fas fa-user-check"></i> <span class="d-none d-md-inline">Activar</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay trabajadores (administradores o empleados) registrados.
            </div>
        <?php endif; ?>
    </div>
    <!-- Fin Sección de Trabajadores -->

    <!-- Sección de Clientes -->
    <div class="dashboard-section mb-4">
        <h3><i class="fas fa-users"></i> Clientes</h3>
        <?php if (isset($clientes) && !empty($clientes)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td>
                                    <?php 
                                    $fotografiaUsuarioUrlListar = '';
                                    if (!empty($usuario['fotografia'])) {
                                        $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                                        $fotografiaUsuarioUrlListar = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($usuario['fotografia']);
                                    } else {
                                        $fotografiaUsuarioUrlListar = BASE_URL . 'img/default-avatar.png'; 
                                    }
                                    ?>
                                    <img src="<?php echo $fotografiaUsuarioUrlListar; ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <?php 
                                        if (defined('ID_ROL_CLIENTE') && $usuario['rol_id'] == ID_ROL_CLIENTE) {
                                            echo '<span class="badge bg-secondary">Cliente</span>';
                                        } else { 
                                            echo '<span class="badge bg-light text-dark">Rol Desconocido (' . htmlspecialchars($usuario['rol_id']) . ')</span>';
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'N/D'); ?></td>
                                <td>
                                    <?php if (isset($usuario['activo']) && $usuario['activo'] == 1): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>admin/users/edit/<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary mb-1" title="Editar Usuario">
                                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Editar</span>
                                    </a>
                                    <?php /* Los clientes no tienen botones de "Servicios" o "Horario" */ ?>
                                    <?php if (isset($_SESSION['user_id']) && $usuario['id'] != $_SESSION['user_id']): ?>
                                        <?php if (isset($usuario['activo']) && $usuario['activo'] == 1): ?>
                                            <form action="<?php echo BASE_URL; ?>admin/users/delete/<?php echo $usuario['id']; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres DESACTIVAR a este usuario?');">
                                                <button type="submit" class="btn btn-sm btn-danger ms-1 mb-1" title="Desactivar Usuario">
                                                    <i class="fas fa-user-slash"></i> <span class="d-none d-md-inline">Desactivar</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo BASE_URL; ?>admin/users/activate/<?php echo $usuario['id']; ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres ACTIVAR a este usuario?');">
                                                <button type="submit" class="btn btn-sm btn-success ms-1 mb-1" title="Activar Usuario">
                                                    <i class="fas fa-user-check"></i> <span class="d-none d-md-inline">Activar</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay clientes registrados.
            </div>
        <?php endif; ?>
    </div>
    <!-- Fin Sección de Clientes -->


    <!-- Sección de Usuarios Registrados Recientemente (SIN CAMBIOS) -->
    <div class="dashboard-content dashboard-section mt-5">
        <h2><i class="fas fa-user-clock"></i> Usuarios Registrados Recientemente</h2>
        <?php
        if (isset($usuarios_recientes) && !empty($usuarios_recientes)): ?>
            <div class="table-responsive">
                <table class="users-table-recent table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre (completo)</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_recientes as $usuario_r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario_r['id']); ?></td>
                                <td>
                                    <?php 
                                    $fotografiaUsuarioUrlRecientes = '';
                                    if (!empty($usuario_r['fotografia'])) {
                                        $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                                        $fotografiaUsuarioUrlRecientes = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($usuario_r['fotografia']);
                                    } else {
                                        $fotografiaUsuarioUrlRecientes = BASE_URL . 'img/default-avatar.png';
                                    }
                                    ?>
                                    <img src="<?php echo $fotografiaUsuarioUrlRecientes; ?>" alt="Foto de <?php echo htmlspecialchars($usuario_r['nombre']); ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($usuario_r['nombre'] . ' ' . ($usuario_r['apellido'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($usuario_r['email']); ?></td>
                                <td>
                                    <?php 
                                    if (defined('ID_ROL_ADMIN') && $usuario_r['rol_id'] == ID_ROL_ADMIN) echo '<span class="badge bg-danger">Administrador</span>';
                                    elseif (defined('ID_ROL_EMPLEADO') && $usuario_r['rol_id'] == ID_ROL_EMPLEADO) echo '<span class="badge bg-info text-dark">Empleado</span>';
                                    elseif (defined('ID_ROL_CLIENTE') && $usuario_r['rol_id'] == ID_ROL_CLIENTE) echo '<span class="badge bg-secondary">Cliente</span>';
                                    else echo '<span class="badge bg-light text-dark">Desconocido (' . htmlspecialchars($usuario_r['rol_id']) . ')</span>';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars(isset($usuario_r['fecha_registro']) ? date('d/m/Y H:i', strtotime($usuario_r['fecha_registro'])) : 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay usuarios para mostrar en la sección de recientes.</p>
        <?php endif; ?>
    </div>
    <!-- Fin Sección de Usuarios Registrados Recientemente -->

</div>

