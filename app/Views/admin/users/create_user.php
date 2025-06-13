
<h1><?php echo htmlspecialchars($pageTitle ?? "Crear Nuevo Usuario"); ?></h1>

<?php 
    $errors = $_SESSION['form_errors_user_create'] ?? [];
    $formData = $_SESSION['form_data_user_create'] ?? [];
    unset($_SESSION['form_errors_user_create'], $_SESSION['form_data_user_create']);
?>

<?php if (isset($_SESSION['mensaje_error_global'])): ?>
    <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_exito_global'])): ?>
    <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>admin/users/store" method="POST" class="form-admin-user" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nombre">Nombre: <span class="required">*</span></label>
        <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($formData['nombre'] ?? ''); ?>" required>
        <?php if (isset($errors['nombre'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['nombre']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="apellido">Apellido: <span class="required">*</span></label>
        <input type="text" name="apellido" id="apellido" value="<?php echo htmlspecialchars($formData['apellido'] ?? ''); ?>" required>
        <?php if (isset($errors['apellido'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['apellido']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email">Email: <span class="required">*</span></label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
        <?php if (isset($errors['email'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="telefono">Teléfono (Opcional):</label>
        <input type="tel" name="telefono" id="telefono" value="<?php echo htmlspecialchars($formData['telefono'] ?? ''); ?>">
        <?php if (isset($errors['telefono'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['telefono']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Contraseña: <span class="required">*</span></label>
        <input type="password" name="password" id="password" required>
        <small>Mínimo 6 caracteres.</small>
        <?php if (isset($errors['password'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirmar Contraseña: <span class="required">*</span></label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <?php if (isset($errors['confirm_password'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="rol_id">Rol del Usuario: <span class="required">*</span></label>
        <select name="rol_id" id="rol_id" required>
            <option value="">Selecciona un rol...</option>
            <option value="<?php echo ID_ROL_CLIENTE; ?>" <?php echo (isset($formData['rol_id']) && $formData['rol_id'] == ID_ROL_CLIENTE) ? 'selected' : ''; ?>>Cliente</option>
            <option value="<?php echo ID_ROL_EMPLEADO; ?>" <?php echo (isset($formData['rol_id']) && $formData['rol_id'] == ID_ROL_EMPLEADO) ? 'selected' : ''; ?>>Empleado</option>
            <option value="<?php echo ID_ROL_ADMIN; ?>" <?php echo (isset($formData['rol_id']) && $formData['rol_id'] == ID_ROL_ADMIN) ? 'selected' : ''; ?>>Administrador</option>
        </select>
        <?php if (isset($errors['rol_id'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['rol_id']); ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="fotografia">Fotografía (Opcional):</label>
        <input type="file" name="fotografia" id="fotografia" accept="image/jpeg, image/png, image/gif">
        <?php if (isset($errors['fotografia'])): ?><span class="error-message"><?php echo htmlspecialchars($errors['fotografia']); ?></span><?php endif; ?>
    </div>
    
    <button type="submit" class="btn btn-primary">Crear Usuario</button>
    <a href="<?php echo BASE_URL; ?>admin/usuarios" class="btn btn-secondary">Cancelar y Volver a Usuarios</a>
</form>

<style>
    .form-admin-user { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;}
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="tel"],
    .form-group input[type="password"],
    .form-group input[type="file"],
    .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .form-group .required { color: red; }
    .form-group small { font-size: 0.85em; color: #555; display: block; margin-top: 3px;}
    .error-message { color: red; font-size: 0.85em; display: block; margin-top: 3px; }
    .message.error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
    .message.success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
    .btn { display: inline-block; padding: 8px 15px; margin: 5px 2px; border: 1px solid transparent; border-radius: 4px; text-decoration: none; color: white; text-align: center; cursor: pointer; }
    .btn-primary { background-color: #007bff; border-color: #007bff; }
    .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
</style>