<div class="profile-container" style="background: white; padding: 20px; border-radius: 8px; text-align: left; max-width: 600px; margin: auto;">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Mi Perfil'; ?></h2>
    
    <?php if (isset($_SESSION['mensaje_error']) && !empty($_SESSION['mensaje_error'])): ?>
        <div class="message error" style="margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_exito']) && !empty($_SESSION['mensaje_exito'])): ?>
        <div class="message success" style="margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?></div>
    <?php endif; ?>

    <?php if (isset($user) && $user): ?>
        <div style="text-align:center; margin-bottom: 20px;">
            <img src="<?php echo htmlspecialchars($user['fotografia_url']); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($user['nombre']); ?>" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ddd;">
        </div>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['nombre']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Miembro desde:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($user['fecha_registro']))); ?></p>
        <hr style="margin: 20px 0;">
        <a href="<?php echo BASE_URL; ?>profile/edit" class="btn-submit" style="display: inline-block; text-decoration: none; padding: 10px 15px; text-align:center; background-color: #007bff; color:white; border-radius: 5px;">Editar Perfil</a>
        <a href="<?php echo BASE_URL; ?>dashboard" style="margin-left: 10px; color: #007bff; text-decoration:none;">Volver al Dashboard</a>
    <?php else: ?>
        <p class="message error">No se pudieron cargar los datos del perfil. Es posible que necesites iniciar sesión de nuevo.</p>
        <p><a href="<?php echo BASE_URL; ?>login">Ir a Iniciar Sesión</a></p>
    <?php endif; ?>
</div>