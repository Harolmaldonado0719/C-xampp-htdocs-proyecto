
<div class="auth-container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Editar Perfil'; ?></h2>

    <?php if (isset($mensaje_error_perfil_edit) && !empty($mensaje_error_perfil_edit)): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($mensaje_error_perfil_edit); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['mensaje_exito_perfil_edit']) && !empty($_SESSION['mensaje_exito_perfil_edit'])): ?>
        <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito_perfil_edit']); unset($_SESSION['mensaje_exito_perfil_edit']); ?></div>
    <?php endif; ?>

    <?php 
        // $userData se usa para la foto actual.
        // $nombre_val, $apellido_val, $email_val, $telefono_val para repoblar los campos del formulario.
        // Estas variables deben ser pasadas por UserController desde el método editProfileForm.
    ?>
    <?php if (isset($userData) && $userData && isset($nombre_val) && isset($apellido_val) && isset($email_val) && isset($telefono_val)): ?>
    <form action="<?php echo BASE_URL; ?>profile/update" method="POST" enctype="multipart/form-data" class="auth-form">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_val); ?>" required>
        </div>

        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido_val); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" required>
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono (Opcional):</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono_val); ?>">
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
        <?php 
            // Para depuración, si los datos no se cargan:
            // error_log("edit_profile.php: Faltan datos para el formulario. userData: " . print_r($userData ?? null, true) . 
            //           ", nombre_val: " . print_r($nombre_val ?? null, true) . 
            //           ", apellido_val: " . print_r($apellido_val ?? null, true) . 
            //           ", email_val: " . print_r($email_val ?? null, true) .
            //           ", telefono_val: " . print_r($telefono_val ?? null, true));
        ?>
    <?php endif; ?>
</div>

<style>
    .auth-container { max-width: 500px; margin: 30px auto; padding: 25px; background-color: #fff; border: 1px solid #ddd; box-shadow: 0 0 15px rgba(0,0,0,0.05); border-radius: 8px; }
    .auth-container h2 { text-align: center; margin-bottom: 20px; color: #333; }
    .auth-form .form-group { margin-bottom: 18px; }
    .auth-form label { display: block; margin-bottom: 6px; font-weight: 600; color: #555; }
    .auth-form input[type="text"],
    .auth-form input[type="email"],
    .auth-form input[type="tel"],
    .auth-form input[type="password"],
    .auth-form input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }
    .auth-form input:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); outline: none;}
    .auth-form .btn-submit {
        width: 100%;
        padding: 12px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1.1em;
        transition: background-color 0.2s;
    }
    .auth-form .btn-submit:hover { background-color: #0056b3; }
    .auth-links { text-align: center; margin-top: 20px; }
    .auth-links a { color: #007bff; text-decoration: none; }
    .auth-links a:hover { text-decoration: underline; }
    .message.error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
    .message.success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
    .form-group small { font-size: 0.85em; color: #555; display: block; margin-top: 3px;}
</style>