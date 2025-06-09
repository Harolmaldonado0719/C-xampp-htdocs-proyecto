
<div class="auth-container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Editar Perfil'; ?></h2>

    <?php // Usar la variable de error que pasa el controlador ?>
    <?php if (isset($mensaje_error_perfil_edit) && !empty($mensaje_error_perfil_edit)): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($mensaje_error_perfil_edit); ?></div>
    <?php endif; ?>
    <?php /* No se suele pasar mensaje de éxito a la vista de edición, se muestra en la vista de perfil tras la redirección
    <?php if (isset($mensaje_exito) && !empty($mensaje_exito)): ?>
        <div class="message success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
    <?php endif; ?>
    */ ?>

    <?php // $userData se usa para la foto actual, $nombre_val y $email_val para repoblar ?>
    <?php if (isset($userData) && $userData && isset($nombre_val) && isset($email_val)): ?>
    <form action="<?php echo BASE_URL; ?>profile/update" method="POST" enctype="multipart/form-data" class="auth-form">
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_val); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" required>
        </div>
        
        <hr style="margin: 20px 0;">
        <h3 style="margin-bottom: 10px; font-size: 1.1em;">Cambiar Contraseña (Opcional)</h3>
        <div class="form-group">
            <label for="current_password">Contraseña Actual:</label>
            <input type="password" id="current_password" name="current_password" placeholder="Deja en blanco si no cambias contraseña">
            <small>Necesaria si quieres establecer una nueva contraseña.</small>
        </div>
        <div class="form-group">
            <label for="new_password">Nueva Contraseña:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres">
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirmar Nueva Contraseña:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password">
        </div>
        <hr style="margin: 20px 0;">
        
        <div class="form-group">
            <label for="fotografia">Cambiar Foto de Perfil (Opcional):</label>
            <?php // Asegurarse que fotografia_url_actual es pasada por UserController dentro de $userData ?>
            <?php if (isset($userData['fotografia_url_actual']) && !empty($userData['fotografia_url_actual'])): ?>
                <p style="margin-bottom: 5px;">Foto actual:</p>
                <img src="<?php echo htmlspecialchars($userData['fotografia_url_actual']); ?>" alt="Foto actual" style="max-width: 100px; max-height: 100px; margin-bottom:10px; border-radius:50%; display:block;">
            <?php else: ?>
                <p style="margin-bottom: 5px;">No hay foto de perfil actual.</p>
            <?php endif; ?>
            <input type="file" id="fotografia" name="fotografia" accept="image/jpeg,image/png,image/gif">
        </div>
        
        <button type="submit" class="btn-submit" style="margin-top:20px;">Guardar Cambios</button>
    </form>
    <div class="auth-links" style="margin-top:15px;">
        <a href="<?php echo BASE_URL; ?>profile">Cancelar y Volver a Mi Perfil</a>
    </div>
    <?php else: ?>
        <p class="message error">No se pudieron cargar los datos para editar. <a href="<?php echo BASE_URL; ?>profile">Volver al perfil</a></p>
    <?php endif; ?>
</div>