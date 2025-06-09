
<div class="profile-container" style="background: white; padding: 20px; border-radius: 8px; text-align: left; max-width: 600px; margin: auto;">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Mi Perfil'; ?></h2>
    
    <?php // Usar las variables de mensaje que pasa el controlador ?>
    <?php if (isset($mensaje_error_perfil) && !empty($mensaje_error_perfil)): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($mensaje_error_perfil); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito_perfil) && !empty($mensaje_exito_perfil)): ?>
        <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($mensaje_exito_perfil); ?></div>
    <?php endif; ?>

    <?php // $userData en lugar de $user para consistencia con el controlador ?>
    <?php if (isset($userData) && $userData): ?>
        <div style="text-align:center; margin-bottom: 20px;">
            <?php // Asegurarse que fotografia_url es pasada por UserController ?>
            <img src="<?php echo isset($userData['fotografia_url']) ? htmlspecialchars($userData['fotografia_url']) : BASE_URL . 'img/default-avatar.png'; ?>" alt="Foto de perfil de <?php echo htmlspecialchars($userData['nombre']); ?>" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd;">
        </div>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($userData['nombre']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
        <?php if (isset($userData['nombre_rol'])): // Mostrar rol si está disponible ?>
        <p><strong>Rol:</strong> <?php echo htmlspecialchars($userData['nombre_rol']); ?></p>
        <?php endif; ?>
        <p><strong>Miembro desde:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($userData['fecha_registro']))); ?></p>
        <hr style="margin: 20px 0;">
        <a href="<?php echo BASE_URL; ?>profile/edit" class="btn-submit" style="display: inline-block; text-decoration: none; padding: 10px 15px; text-align:center; background-color: #007bff; color:white; border-radius: 5px;">Editar Perfil</a>
        
        <?php // Enlace dinámico para volver al dashboard usando la clave de sesión unificada ?>
        <?php
            $dashboardLink = BASE_URL . 'login'; // Fallback por si el rol no está o es desconocido
            // Asumimos que $_SESSION['user_rol_id'] es la clave de sesión estándar para el ID de rol
            if (isset($_SESSION['user_rol_id'])) {
                $rol_id_sesion = $_SESSION['user_rol_id'];
                if ($rol_id_sesion == ID_ROL_ADMIN) $dashboardLink = BASE_URL . 'dashboard';
                elseif ($rol_id_sesion == ID_ROL_EMPLEADO) $dashboardLink = BASE_URL . 'empleado/dashboard';
                elseif ($rol_id_sesion == ID_ROL_CLIENTE) $dashboardLink = BASE_URL . 'portal_servicios';
            }
        ?>
        <a href="<?php echo $dashboardLink; ?>" style="margin-left: 10px; color: #007bff; text-decoration:none;">Volver al Panel</a>
    <?php else: ?>
        <p class="message error">No se pudieron cargar los datos del perfil. Es posible que necesites iniciar sesión de nuevo.</p>
        <p><a href="<?php echo BASE_URL; ?>login">Ir a Iniciar Sesión</a></p>
    <?php endif; ?>
</div>