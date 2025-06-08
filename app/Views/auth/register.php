<div class="auth-container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Registrarse'; ?></h2>

    <?php if (isset($mensaje_error) && !empty($mensaje_error)): ?>
        <div class="message error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito) && !empty($mensaje_exito)): ?>
        <div class="message success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>handle_register" method="POST" enctype="multipart/form-data" class="auth-form">
        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo isset($datos_previos['nombre']) ? htmlspecialchars($datos_previos['nombre']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($datos_previos['email']) ? htmlspecialchars($datos_previos['email']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <small>Mínimo 6 caracteres.</small>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label for="fotografia">Foto de Perfil (Opcional):</label>
            <input type="file" id="fotografia" name="fotografia" accept="image/jpeg, image/png, image/gif">
            <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
        </div>
        <button type="submit" class="btn-submit">Registrarse</button>
    </form>
    <div class="auth-links">
        <p>¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>login">Inicia sesión aquí</a></p>
    </div>
</div>