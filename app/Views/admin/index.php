
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>
        <p>Bienvenido, <?php echo isset($usuario_nombre) ? htmlspecialchars($usuario_nombre) : 'Admin'; ?>.</p>
    </div>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_dashboard'])): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error_dashboard']); unset($_SESSION['mensaje_error_dashboard']); ?></div>
    <?php endif; ?>


    <div class="dashboard-actions" style="margin-bottom: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px;">
        <h3>Acciones Rápidas</h3>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>admin/users/create">Crear Nuevo Usuario</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/reports">Ver Reportes</a></li>
            <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
            <!-- Puedes añadir más enlaces a acciones de administrador aquí -->
        </ul>
    </div>

    <div class="dashboard-content">
        <h2>Usuarios Registrados</h2>
        <?php if (isset($usuarios) && !empty($usuarios)): ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de Registro</th>
                        <th>Foto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['fecha_registro']))); ?></td>
                            <td>
                                <?php // CORRECCIÓN AQUÍ: Usar $usuario['fotografia_url'] directamente ?>
                                <?php if (isset($usuario['fotografia_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($usuario['fotografia_url']); ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo BASE_URL . 'img/default-avatar.png'; ?>" alt="Sin foto" style="width: 50px; height: 50px; border-radius: 50%;">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL . 'admin/users/edit/' . htmlspecialchars($usuario['id']); ?>" class="btn-edit">Editar</a>
                                <a href="<?php echo BASE_URL . 'admin/users/delete/' . htmlspecialchars($usuario['id']); ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>
</div>

<style>
/* ... (tus estilos existentes) ... */
.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.users-table th, .users-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
.users-table th {
    background-color: #007bff; /* Azul similar al de la imagen */
    color: white;
    font-weight: bold;
}
.users-table tr:nth-child(even) {
    background-color: #f9f9f9;
}
.users-table tr:hover {
    background-color: #f1f1f1;
}
.users-table img {
    vertical-align: middle;
}
.btn-edit, .btn-delete {
    display: inline-block;
    padding: 5px 10px;
    margin-right: 5px;
    border-radius: 4px;
    text-decoration: none;
    color: white;
    font-size: 0.9em;
}
.btn-edit {
    background-color: #28a745; /* Verde */
}
.btn-edit:hover {
    background-color: #218838;
}
.btn-delete {
    background-color: #dc3545; /* Rojo */
}
.btn-delete:hover {
    background-color: #c82333;
}
</style>