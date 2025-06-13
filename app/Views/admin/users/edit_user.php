
<h1><?php echo htmlspecialchars($pageTitle ?? "Editar Usuario"); ?></h1>

<?php if (!empty($form_errors)): ?>
    <div class="alert alert-danger">
        <strong>Por favor corrige los siguientes errores:</strong>
        <ul>
            <?php foreach ($form_errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_error_global'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_exito_global'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
<?php endif; ?>


<?php
// Cargar datos del formulario si existen (por un error previo), sino usar los del usuario
$nombre_val = $form_data['nombre'] ?? ($usuario['nombre'] ?? '');
$apellido_val = $form_data['apellido'] ?? ($usuario['apellido'] ?? ''); // Añadido
$email_val = $form_data['email'] ?? ($usuario['email'] ?? '');
$telefono_val = $form_data['telefono'] ?? ($usuario['telefono'] ?? ''); // Añadido
$rol_id_val = $form_data['rol_id'] ?? ($usuario['rol_id'] ?? '');
// La contraseña no se pre-rellena por seguridad
?>

<form action="<?php echo BASE_URL . 'admin/users/update/' . htmlspecialchars($usuario['id'] ?? ''); ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre:</label>
        <input type="text" class="form-control <?php echo isset($form_errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_val); ?>" required>
        <?php if (isset($form_errors['nombre'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['nombre']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="apellido" class="form-label">Apellido:</label>
        <input type="text" class="form-control <?php echo isset($form_errors['apellido']) ? 'is-invalid' : ''; ?>" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido_val); ?>" required>
        <?php if (isset($form_errors['apellido'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['apellido']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Correo Electrónico:</label>
        <input type="email" class="form-control <?php echo isset($form_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" required>
        <?php if (isset($form_errors['email'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['email']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="telefono" class="form-label">Teléfono (Opcional):</label>
        <input type="tel" class="form-control <?php echo isset($form_errors['telefono']) ? 'is-invalid' : ''; ?>" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono_val); ?>">
        <?php if (isset($form_errors['telefono'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['telefono']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar):</label>
        <input type="password" class="form-control <?php echo isset($form_errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password">
        <?php if (isset($form_errors['password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['password']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña:</label>
        <input type="password" class="form-control <?php echo isset($form_errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password">
        <?php if (isset($form_errors['confirm_password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['confirm_password']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="rol_id" class="form-label">Rol:</label>
        <select class="form-select <?php echo isset($form_errors['rol_id']) ? 'is-invalid' : ''; ?>" id="rol_id" name="rol_id" required>
            <option value="" <?php echo ($rol_id_val == '') ? 'selected' : ''; ?>>Selecciona un rol</option>
            <option value="<?php echo ID_ROL_CLIENTE; ?>" <?php echo ($rol_id_val == ID_ROL_CLIENTE) ? 'selected' : ''; ?>>Cliente</option>
            <option value="<?php echo ID_ROL_EMPLEADO; ?>" <?php echo ($rol_id_val == ID_ROL_EMPLEADO) ? 'selected' : ''; ?>>Empleado</option>
            <option value="<?php echo ID_ROL_ADMIN; ?>" <?php echo ($rol_id_val == ID_ROL_ADMIN) ? 'selected' : ''; ?>>Administrador</option>
        </select>
        <?php if (isset($form_errors['rol_id'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['rol_id']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="fotografia" class="form-label">Fotografía (opcional, dejar en blanco para no cambiar):</label>
        <input type="file" class="form-control <?php echo isset($form_errors['fotografia']) ? 'is-invalid' : ''; ?>" id="fotografia" name="fotografia" accept="image/jpeg, image/png, image/gif">
        <?php if (isset($usuario['fotografia']) && !empty($usuario['fotografia'])): ?>
            <p class="mt-2">Foto actual: 
                <img src="<?php echo BASE_URL . (defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/') . htmlspecialchars($usuario['fotografia']); ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>" style="max-width: 100px; max-height: 100px; border-radius: 50%;">
            </p>
        <?php endif; ?>
        <?php if (isset($form_errors['fotografia'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['fotografia']); ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
    <a href="<?php echo BASE_URL . 'admin/usuarios'; ?>" class="btn btn-secondary">Cancelar y Volver a Usuarios</a>
</form>