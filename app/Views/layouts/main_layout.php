
<?php
// Asegurar que la sesión esté iniciada para acceder a $_SESSION
// Aunque config.php o index.php ya lo hagan, es una buena práctica aquí también.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir $pageTitle si no está seteado por el controlador
$pageTitle = $pageTitle ?? 'Clip Techs Sistem'; // Usar operador de fusión de null

// Definir $viewPath como null si no está seteado (para la página de bienvenida)
$viewPath = $viewPath ?? null;

// Determinar si el usuario está logueado y su rol usando las claves correctas
$isUserLoggedIn = isset($_SESSION['user_id']);
$userRolId = $_SESSION['user_rol_id'] ?? null;
$userNombre = $_SESSION['user_nombre'] ?? 'Usuario';
$userFotografia = $_SESSION['user_fotografia'] ?? null;
$fotografiaUrl = null;

// Construir URL de la fotografía si existe
if ($userFotografia && defined('BASE_URL')) {
    // Asumiendo que APP_UPLOAD_DIR_PUBLIC_PATH está definida en config.php como 'uploads/'
    $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
    $fotografiaUrl = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($userFotografia);
} elseif (defined('BASE_URL')) {
    $fotografiaUrl = BASE_URL . 'img/default-avatar.png'; // Asegúrate que esta imagen exista
}

// Obtener mensajes globales de sesión si existen (para mostrar en el layout)
$mensaje_error_global_layout = $_SESSION['mensaje_error_global'] ?? $mensaje_error_display ?? null;
$mensaje_exito_global_layout = $_SESSION['mensaje_exito_global'] ?? $mensaje_exito_display ?? null;
$mensaje_info_global_layout = $_SESSION['mensaje_info_global'] ?? $mensaje_info_display ?? null;

// Limpiar mensajes de sesión después de leerlos para que no se muestren repetidamente
unset($_SESSION['mensaje_error_global'], $_SESSION['mensaje_exito_global'], $_SESSION['mensaje_info_global']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle) . ' - Clip Techs Sistem'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>img/logo-pestaña.png" type="image/x-icon">
    <!-- Font Awesome para iconos (si los usas) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>" class="logo-link">
                    <img src="<?php echo BASE_URL; ?>img/logo.png" alt="Clip Techs Sistem Logo" class="logo-img">
                    <h1>Clip Techs Sistem</h1>
                </a>
            </div>
            <nav class="site-nav">
                <ul>
                    <?php if ($isUserLoggedIn): ?>
                        <?php // Usuario Logueado ?>
                        <?php if ($fotografiaUrl): ?>
                            <li class="nav-profile-pic"><img src="<?php echo $fotografiaUrl; ?>" alt="Foto" class="profile-pic-nav-small"></li>
                        <?php endif; ?>
                        <li><span class="nav-welcome">Hola, <?php echo htmlspecialchars($userNombre); ?></span></li>

                        <?php if ($userRolId == ID_ROL_ADMIN): ?>
                            <li><a href="<?php echo BASE_URL; ?>dashboard">Dashboard Admin</a></li>
                        <?php elseif ($userRolId == ID_ROL_EMPLEADO): ?>
                            <li><a href="<?php echo BASE_URL; ?>empleado/dashboard">Panel Empleado</a></li>
                        <?php elseif ($userRolId == ID_ROL_CLIENTE): ?>
                            <li><a href="<?php echo BASE_URL; ?>portal_servicios">Portal Servicios</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>logout" class="btn-logout">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <?php // Usuario No Logueado ?>
                        <li><a href="<?php echo BASE_URL; ?>login">Iniciar Sesión</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Mostrar mensajes globales -->
            <?php if ($mensaje_error_global_layout): ?>
                <div class="notification is-danger global-message"><?php echo htmlspecialchars($mensaje_error_global_layout); ?></div>
            <?php endif; ?>
            <?php if ($mensaje_exito_global_layout): ?>
                <div class="notification is-success global-message"><?php echo htmlspecialchars($mensaje_exito_global_layout); ?></div>
            <?php endif; ?>
            <?php if ($mensaje_info_global_layout): ?>
                <div class="notification is-info global-message"><?php echo htmlspecialchars($mensaje_info_global_layout); ?></div>
            <?php endif; ?>

            <?php
            // Lógica para mostrar el contenido principal o la bienvenida
            if ($viewPath && file_exists($viewPath)) {
                // Carga la vista específica (login, register, dashboard, etc.)
                // Las variables necesarias para la vista ($usuarios, $pageTitle específico, etc.)
                // deben haber sido extraídas por el controlador o por public/index.php
                include $viewPath;
            } elseif (!$isUserLoggedIn && ($viewPath === null || $viewPath === '')) {
                // Mostrar contenido de bienvenida solo si no hay usuario logueado Y no hay una vista específica definida
            ?>
                <div class="welcome-section">
                    <h2>Bienvenido a Clip Techs Sistem</h2>
                    <p>Una solución digital para modernizar y facilitar la administración de tu peluquería. Gestiona turnos, usuarios, servicios y mucho más desde un solo lugar.</p>
            
                    <h3><i class="fas fa-bullseye"></i> Misión</h3>
                    <p>Brindar a los salones de belleza y peluquerías una herramienta web intuitiva, eficiente y segura que permita gestionar citas, clientes y servicios de forma automatizada, mejorando la experiencia tanto para administradores como para clientes.</p>
            
                    <h3><i class="fas fa-eye"></i> Visión</h3>
                    <p>Convertirnos en una plataforma líder en gestión de peluquerías a nivel local y regional, innovando constantemente en tecnología accesible y moderna que impulse la transformación digital del sector de belleza.</p>
            
                    <h3><i class="fas fa-check-circle"></i> Objetivos del Proyecto</h3>
                    <ul class="objetivos-lista">
                        <li>Automatizar la gestión de usuarios, citas y servicios.</li>
                        <li>Mejorar la organización interna del negocio.</li>
                        <li>Ofrecer una interfaz sencilla, clara y adaptable a cualquier dispositivo.</li>
                        <li>Permitir la administración segura y eficiente del sistema por parte de los administradores.</li>
                        <li>Facilitar el acceso de los clientes a servicios y reservas en línea.</li>
                    </ul>

                    <h3 class="org-title"><i class="fas fa-sitemap"></i> Organigrama General</h3>
                    <div class="organigrama-container">
                        <img src="<?php echo BASE_URL; ?>img/organigrama.png" alt="Organigrama de Clip Techs Sistem" class="organigrama-img">
                    </div>

                    <h3 class="contact-title"><i class="fas fa-phone-alt"></i> Información de Contacto</h3>
                    <div class="contact-info">
                        <p>Si tienes alguna pregunta o necesitas soporte, no dudes en contactarme:</p>
                        <p><strong>Correo Electrónico:</strong> <a href="mailto:harolmaldonado14@gmail.com">harolmaldonado14@gmail.com</a></p>
                        <p><strong>Teléfono:</strong> <a href="tel:+573017678950">+57 301 767 8950</a></p>
                    </div>
                </div> <!-- Fin de .welcome-section -->
            <?php
            } elseif (isset($viewPath)) {
                // Si $viewPath está seteado pero el archivo no existe (y no es la bienvenida)
                echo "<p class='message error'>Error: La vista especificada no fue encontrada: " . htmlspecialchars($viewPath) . "</p>";
            }
            // Si el usuario está logueado y $viewPath no está seteado (ej. va a la raíz),
            // public/index.php debería haber redirigido al dashboard/portal.
            // Si por alguna razón no lo hizo y llegamos aquí, esta sección principal quedaría vacía.
            ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Clip Techs Sistem. Todos los derechos reservados.</p>
            <p>
                <a href="#">Política de Privacidad</a> | 
                <a href="#">Términos de Servicio</a>
            </p>
        </div>
    </footer>
    <!-- <script src="<?php echo BASE_URL; ?>js/main.js"></script> -->
</body>
</html>