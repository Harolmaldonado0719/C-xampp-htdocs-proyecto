<div class="auth-container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Editar Perfil'; ?></h2>

    <?php if (isset($mensaje_error) && !empty($mensaje_error)): ?>
        <div class="message error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito) && !empty($mensaje_exito)): ?>
        <div class="message success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
    <?php endif; ?>

    <?php if (isset($user) && $user): ?>
    <form action="<?php echo BASE_URL; ?>profile/update" method="POST" enctype="multipart/form-data" class="auth-form">
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="fotografia">Cambiar Foto de Perfil (Opcional):</label>
            <?php if (!empty($user['fotografia_url_actual'])): ?>
                <p style="margin-bottom: 5px;">Foto actual:</p>
                <img src="<?php echo htmlspecialchars($user['fotografia_url_actual']); ?>" alt="Foto actual" style="max-width: 100px; max-height: 100px; margin-bottom:10px; border-radius:50%; display:block;">
            <?php else: ?>
                <p style="margin-bottom: 5px;">No hay foto de perfil actual.</p>
            <?php endif; ?>
            <input type="file" id="fotografia" name="fotografia" accept="image/*">
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