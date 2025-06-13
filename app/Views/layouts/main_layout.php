<?php
// filepath: c:\xampp\<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\layouts\main_layout.php
// Asegurar que la sesión esté iniciada para acceder a $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir $pageTitle si no está seteado por el controlador
$pageTitle = $pageTitle ?? 'Clip Techs Sistem';

// Definir $viewPath como null si no está seteado (para la página de bienvenida)
// Esta variable $viewPath es la que el controlador (o el index.php en caso de 404) debe definir.
// Si no se define, se asume que es la página de bienvenida si el usuario no está logueado.
$viewPath = $viewPath ?? null; 

// Determinar si el usuario está logueado y su rol usando las claves correctas
$isUserLoggedIn = isset($_SESSION['user_id']);
$userRolId = $_SESSION['user_rol_id'] ?? null;
$userNombre = $_SESSION['user_nombre'] ?? 'Usuario';
$userFotografia = $_SESSION['user_fotografia'] ?? null;
$fotografiaUrl = null;

// Construir URL de la fotografía si existe
if ($userFotografia && defined('BASE_URL')) {
    $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
    $fotografiaUrl = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($userFotografia);
} elseif (defined('BASE_URL')) {
    $fotografiaUrl = BASE_URL . 'img/default-avatar.png'; // Asegúrate que esta imagen exista
}

// Obtener mensajes globales de sesión si existen (para mostrar en el layout)
$mensaje_error_global_layout = $_SESSION['mensaje_error_global'] ?? null;
$mensaje_exito_global_layout = $_SESSION['mensaje_exito_global'] ?? null;
$mensaje_info_global_layout = $_SESSION['mensaje_info_global'] ?? null;

// Limpiar mensajes de sesión después de leerlos para que no se muestren repetidamente
unset($_SESSION['mensaje_error_global'], $_SESSION['mensaje_exito_global'], $_SESSION['mensaje_info_global']);

// Contador de notificaciones (debe ser pasado por el controlador, ej. ClienteController)
$contador_notificaciones_no_leidas_layout = $contador_notificaciones_no_leidas ?? 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle) . ' - Clip Techs Sistem'; ?></title>
    
    <!-- Bootstrap CSS (Local) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/bootstrap.min.css">
    
    <!-- Tu CSS personalizado (DEBE IR DESPUÉS de Bootstrap para sobrescribir estilos si es necesario) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    
    <link rel="icon" href="<?php echo BASE_URL; ?>img/logo-pestaña.png" type="image/x-icon">
    <!-- Font Awesome para iconos (si los usas) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Estilos para la foto de perfil en la barra de navegación */
        .profile-pic-nav-small {
            width: 60px; /* Tamaño deseado - AUMENTADO */
            height: 70px; /* Tamaño deseado - AUMENTADO */
            border-radius: 50%; /* Para hacerla redonda */
            object-fit: cover; /* Asegura que la imagen cubra el espacio sin distorsionarse */
            margin-right: 8px; /* Espacio a la derecha de la imagen */
            vertical-align: middle; /* Alinea la imagen con el texto del nombre */
        }
        .site-nav ul {
            align-items: center; /* Asegura que los elementos del nav estén centrados verticalmente */
        }
        .nav-profile-pic {
            line-height: 0; /* Ayuda a alinear mejor la imagen si está en un li */
        }
        .notification-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 0.1em 0.4em;
            font-size: 0.7rem;
            position: relative;
            top: -10px;
            right: -5px;
            border: 1px solid white;
        }
        .nav-link-icon {
            margin-right: 5px;
        }
    </style>
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
                        <?php if ($fotografiaUrl): ?>
                            <li class="nav-profile-pic"><img src="<?php echo $fotografiaUrl; ?>" alt="Foto" class="profile-pic-nav-small"></li>
                        <?php endif; ?>
                        <li><span class="nav-welcome">Hola, <?php echo htmlspecialchars($userNombre); ?></span></li>

                        <?php if ($userRolId == (defined('ID_ROL_ADMIN') ? ID_ROL_ADMIN : 1)): ?>
                            <li><a href="<?php echo BASE_URL; ?>dashboard">Dashboard Admin</a></li>
                        <?php elseif ($userRolId == (defined('ID_ROL_EMPLEADO') ? ID_ROL_EMPLEADO : 2)): ?>
                            <li><a href="<?php echo BASE_URL; ?>empleado/dashboard">Panel Empleado</a></li>
                        <?php elseif ($userRolId == (defined('ID_ROL_CLIENTE') ? ID_ROL_CLIENTE : 3)): ?>
                            <li><a href="<?php echo BASE_URL; ?>portal_servicios">Portal Servicios</a></li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>cliente/notificaciones">
                                    <i class="fas fa-bell nav-link-icon"></i>Notificaciones
                                    <?php if ($contador_notificaciones_no_leidas_layout > 0): ?>
                                        <span class="notification-badge"><?php echo $contador_notificaciones_no_leidas_layout; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>logout" class="btn btn-danger btn-sm">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>login">Iniciar Sesión</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content py-4">
        <div class="container">
            <!-- Mostrar mensajes globales con clases de Bootstrap -->
            <?php if ($mensaje_error_global_layout): ?>
                <div class="alert alert-danger alert-dismissible fade show global-message" role="alert">
                    <?php echo htmlspecialchars($mensaje_error_global_layout); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($mensaje_exito_global_layout): ?>
                <div class="alert alert-success alert-dismissible fade show global-message" role="alert">
                    <?php echo htmlspecialchars($mensaje_exito_global_layout); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($mensaje_info_global_layout): ?>
                <div class="alert alert-info alert-dismissible fade show global-message" role="alert">
                    <?php echo htmlspecialchars($mensaje_info_global_layout); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php
            // $viewPath es la ruta completa al archivo de la vista específica que debe ser incluida.
            // Esta variable debe ser definida por el controlador o por el index.php (en caso de 404).
            if ($viewPath && file_exists($viewPath)) {
                include $viewPath;
            } elseif (!$isUserLoggedIn && ($viewPath === null || $viewPath === '')) {
            ?>
                <div class="welcome-section p-5 mb-4 bg-light rounded-3">
                    <div class="container-fluid py-5">
                        <h2 class="display-5 fw-bold">Bienvenido a Clip Techs Sistem</h2>
                        <p class="col-md-8 fs-4">Una solución digital para modernizar y facilitar la administración de tu peluquería. Gestiona turnos, usuarios, servicios y mucho más desde un solo lugar.</p>
                    
                        <h3><i class="fas fa-bullseye"></i> Misión</h3>
                        <p>Brindar a los salones de belleza y peluquerías una herramienta web intuitiva, eficiente y segura que permita gestionar citas, clientes y servicios de forma automatizada, mejorando la experiencia tanto para administradores como para clientes.</p>
                
                        <h3><i class="fas fa-eye"></i> Visión</h3>
                        <p>Convertirnos en una plataforma líder en gestión de peluquerías a nivel local y regional, innovando constantemente en tecnología accesible y moderna que impulse la transformación digital del sector de belleza.</p>
                
                        <h3><i class="fas fa-check-circle"></i> Objetivos del Proyecto</h3>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Automatizar la gestión de usuarios, citas y servicios.</li>
                            <li><i class="fas fa-check text-success me-2"></i>Mejorar la organización interna del negocio.</li>
                            <li><i class="fas fa-check text-success me-2"></i>Ofrecer una interfaz sencilla, clara y adaptable a cualquier dispositivo.</li>
                            <li><i class="fas fa-check text-success me-2"></i>Permitir la administración segura y eficiente del sistema por parte de los administradores.</li>
                            <li><i class="fas fa-check text-success me-2"></i>Facilitar el acceso de los clientes a servicios y reservas en línea.</li>
                        </ul>

                        <h3 class="mt-4"><i class="fas fa-sitemap"></i> Organigrama General</h3>
                        <div class="text-center my-3">
                            <img src="<?php echo BASE_URL; ?>img/organiagrama.png" alt="Organigrama de Clip Techs Sistem" class="img-fluid rounded" style="max-width: 600px;">
                        </div>

                        <h3 class="mt-4"><i class="fas fa-phone-alt"></i> Información de Contacto</h3>
                        <div class="contact-info">
                            <p>Si tienes alguna pregunta o necesitas soporte, no dudes en contactarme:</p>
                            <p><strong>Correo Electrónico:</strong> <a href="mailto:harolmaldonado14@gmail.com">harolmaldonado14@gmail.com</a></p>
                            <p><strong>Teléfono:</strong> <a href="tel:+573017678950">+57 301 767 8950</a></p>
                        </div>
                    </div>
                </div>
            <?php
            } elseif (isset($viewPath)) { // Solo muestra error si $viewPath fue seteado pero no encontrado
                echo "<p class='alert alert-warning'>Error: La vista especificada no fue encontrada: " . htmlspecialchars($viewPath) . "</p>";
                error_log("Layout: Vista no encontrada - " . $viewPath);
            }
            ?>
        </div>
    </main>

    <footer class="site-footer text-center py-4 mt-auto bg-dark text-white">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Clip Techs Sistem. Todos los derechos reservados.</p>
            <p>
                <a href="#" class="text-white-50">Política de Privacidad</a> | 
                <a href="#" class="text-white-50">Términos de Servicio</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle con Popper (Local) -->
    <script src="<?php echo BASE_URL; ?>js/bootstrap.bundle.min.js"></script>
    
    <!-- Tu script main.js personalizado (si tienes uno, descomenta la siguiente línea) -->
    <!-- <script src="<?php echo BASE_URL; ?>js/main.js"></script> -->
    
    <?php if (isset($scripts_for_layout) && is_array($scripts_for_layout)): ?>
        <?php foreach ($scripts_for_layout as $script_url): ?>
            <script src="<?php echo htmlspecialchars($script_url); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>