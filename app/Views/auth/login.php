<div class="auth-container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Iniciar Sesión'; ?></h2>

    <?php if (isset($mensaje_error) && !empty($mensaje_error)): ?>
        <div class="message error"><?php echo htmlspecialchars($mensaje_error); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito) && !empty($mensaje_exito)): ?>
        <div class="message success"><?php echo $mensaje_exito; /* Ya se usa urlencode para pasar, aquí se puede mostrar directamente si confías en la fuente o usar htmlspecialchars si no. */ ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_info) && !empty($mensaje_info)): ?>
        <div class="message info"><?php echo htmlspecialchars($mensaje_info); ?></div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>handle_login" method="POST" class="auth-form">
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email_val) ? htmlspecialchars($email_val) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Ingresar</button>
    </form>
    <div class="auth-links">
        <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>register">Regístrate aquí</a></p>
    </div>
</div>