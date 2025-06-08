<?php
// Este archivo es incluido por main_layout.php
// Las variables como $pageTitle, $usuario_nombre, $usuarios
// son pasadas por el DashboardController a través de extract().
?>
<div class="dashboard-container"> 
    
    <div class="dashboard-header">
        <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>
    </div>

    <?php if (isset($_SESSION['mensaje_exito_dashboard'])): ?>
        <div class="message success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_dashboard']); unset($_SESSION['mensaje_exito_dashboard']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_dashboard'])): ?>
        <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_dashboard']); unset($_SESSION['mensaje_error_dashboard']); ?></div>
    <?php endif; ?>

    <p class="dashboard-welcome">Bienvenido, <?php echo isset($usuario_nombre) ? htmlspecialchars($usuario_nombre) : 'Usuario'; ?>.</p>

    <div class="dashboard-card">
        <h3>Usuarios Registrados</h3>
        <?php if (!empty($usuarios)): ?>
            <div class="table-responsive"> 
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha de Registro</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($usuario['fecha_registro']))); ?></td>
                            <td>
                                <?php 
                                if (!empty($usuario['fotografia']) && $usuario['fotografia_path_exists']): ?>
                                    <img src="<?php echo htmlspecialchars($usuario['fotografia_url']); ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>" class="foto-usuario-tabla">
                                <?php else: ?>
                                    <img src="<?php echo BASE_URL . 'img/default-avatar.png'; ?>" alt="Sin foto" class="foto-usuario-tabla" title="Foto no disponible o no encontrada">
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay usuarios registrados para mostrar.</p>
        <?php endif; ?>
    </div>
</div>