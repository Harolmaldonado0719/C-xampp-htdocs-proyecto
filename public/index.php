<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar configuración (necesaria para BASE_URL y otras constantes de rol)
require_once __DIR__ . '/../app/config.php';

// --- Lógica de Enrutamiento ---
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
$requestUri = $_SERVER['REQUEST_URI'];
$requestUriPath = strtok($requestUri, '?'); 

$route = '';
if (strpos($requestUriPath, $basePath) === 0) {
    $route = substr($requestUriPath, strlen($basePath));
} else {
    $route = ltrim($requestUriPath, '/');
}
$route = trim($route, '/');

$pageTitle = 'Clip Techs Sistem'; 
$viewPath = null; 

// Redirección en la raíz si el usuario está logueado
if (($route === '' || $route === 'index.php') && isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] !== null) {
        if ($_SESSION['user_rol_id'] == ID_ROL_ADMIN) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        } elseif ($_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
            header('Location: ' . BASE_URL . 'empleado/dashboard');
            exit;
        } elseif ($_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
            header('Location: ' . BASE_URL . 'portal_servicios');
            exit;
        } else {
            // Rol desconocido, pero logueado. Es una situación anómala.
            // Limpiar sesión y redirigir a login con error.
            $user_rol_desconocido = $_SESSION['user_rol_id']; // Guardar para logueo si es necesario
            $user_id_actual = $_SESSION['user_id'] ?? 'desconocido'; // Guardar user_id antes de destruir sesión
            session_unset();
            session_destroy();
            // Iniciar nueva sesión para pasar el mensaje
            if (session_status() == PHP_SESSION_NONE) { session_start(); }
            $_SESSION['mensaje_error_global'] = "Tu rol de usuario (ID: " . htmlspecialchars($user_rol_desconocido) . ") no está configurado correctamente. Sesión terminada.";
            error_log("Usuario con ID: " . htmlspecialchars($user_id_actual) . " intentó acceder a la raíz con rol desconocido: " . htmlspecialchars($user_rol_desconocido)); // Loguear este evento
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    } else {
        // user_id existe pero user_rol_id no (es null o no está seteado). Inconsistencia. Forzar logout.
        $user_id_inconsistente = $_SESSION['user_id']; // Guardar para logueo
        session_unset();
        session_destroy();
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['mensaje_error_global'] = "Error de sesión de rol. Por favor, inicia sesión de nuevo.";
        error_log("Usuario con ID: " . htmlspecialchars($user_id_inconsistente) . " tenía sesión inconsistente (user_rol_id faltante) en la raíz.");
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}


// --- Cargar el Controlador Apropiado Basado en la Ruta ---
// Solo si $route no es la raíz para un usuario no logueado (ya que la bienvenida se maneja al final)
// O si es cualquier otra ruta.
if (!($route === '' || $route === 'index.php') || isset($_SESSION['user_id'])) {
    // Obtener mensajes globales de sesión si existen (para rutas que no sean login/register que los manejan específicamente)
    // AuthController::showLoginForm y showRegisterForm ya manejan sus propios mensajes de sesión.
    $mensaje_error_global_for_route = $_SESSION['mensaje_error_global'] ?? null;
    $mensaje_exito_global_for_route = $_SESSION['mensaje_exito_global'] ?? null;
    $mensaje_info_global_for_route = $_SESSION['mensaje_info_global'] ?? null;
    // Limpiar solo si se van a usar en esta petición para una ruta genérica.
    // No limpiar aquí si login/register los necesitan. AuthController los limpiará.

    switch ($route) {
        case 'login':
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            
            // Obtener mensajes de GET (AuthController::showLoginForm se encargará de combinarlos con los de sesión)
            $mensaje_error_get = isset($_GET['mensaje_error']) ? urldecode($_GET['mensaje_error']) : null;
            $mensaje_exito_get = isset($_GET['mensaje_exito']) ? urldecode($_GET['mensaje_exito']) : null;
            $email_val_get = isset($_GET['email_val']) ? urldecode($_GET['email_val']) : '';

            $controller->showLoginForm(
                $mensaje_error_get,
                $mensaje_exito_get,
                ['email_val' => $email_val_get]
            );
            exit; 

        case 'handle_login':
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->handleLogin(); 
            exit; 

        case 'register':
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            // AuthController::showRegisterForm leerá los mensajes y datos previos de la sesión directamente.
            $controller->showRegisterForm(); 
            exit;

        case 'handle_register':
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->handleRegister(); 
            exit;

        case 'logout':
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout(); 
            exit;

        case 'dashboard':
            // La protección de esta ruta ya está en el constructor de AdminController
            // y también aquí como una capa adicional.
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta página.";
                header("Location: " . BASE_URL . "login");
                exit;
            }
            if (!isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_ADMIN) {
                $_SESSION['mensaje_error_global'] = "No tienes permiso para acceder al dashboard.";
                if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                    header("Location: " . BASE_URL . "empleado/dashboard");
                } elseif (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                    header("Location: " . BASE_URL . "portal_servicios");
                } else { 
                    header("Location: " . BASE_URL . "login");
                }
                exit;
            }
            // CAMBIO: Usar AdminController en lugar de DashboardController
            require_once __DIR__ . '/../app/Controllers/AdminController.php';
            $controller = new AdminController();
            $controller->index(); 
            exit;

        case 'empleado/dashboard':
            // El constructor de EmpleadoController ya protege la ruta.
            require_once __DIR__ . '/../app/Controllers/EmpleadoController.php';
            $controller = new EmpleadoController(); 
            $controller->dashboard();
            exit;
        
        case 'portal_servicios':
            // El constructor de ClienteController ya protege la ruta (verifica user_id).
            // CAMBIO: Usar ClienteController en lugar de PortalController
            require_once __DIR__ . '/../app/Controllers/ClienteController.php';
            $controller = new ClienteController();
            $controller->index(); 
            exit;

        case 'profile':
            // El constructor de UserController debería proteger esto.
            // O añadir protección aquí si UserController no lo hace.
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tu perfil.";
                header("Location: " . BASE_URL . "login");
                exit;
            }
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->showProfile();
            exit;

        case 'profile/edit':
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para editar tu perfil.";
                header("Location: " . BASE_URL . "login");
                exit;
            }
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->editProfileForm();
            exit;
        
        case 'profile/update':
            if (!isset($_SESSION['user_id'])) { // Asegurar que el usuario esté logueado
                header("Location: " . BASE_URL . "login"); // Redirigir si no
                exit;
            }
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->updateProfile(); 
            exit;
        
        default:
            // Si la ruta no es la raíz para un usuario no logueado, y no coincide con ninguna otra, es 404.
            // La condición `!($route === '' || $route === 'index.php')` asegura que esto no se active para la bienvenida.
            http_response_code(404);
            $pageTitle = "404 - Página No Encontrada";
            $viewPath = __DIR__ . '/../app/Views/errors/404.php'; 
            
            // Pasar variables necesarias al layout para la página 404
            $data_for_404 = compact('pageTitle', 'viewPath', 
                                    'mensaje_error_global_for_route', 
                                    'mensaje_exito_global_for_route', 
                                    'mensaje_info_global_for_route');
            extract($data_for_404);
            unset($_SESSION['mensaje_error_global'], $_SESSION['mensaje_exito_global'], $_SESSION['mensaje_info_global']); // Limpiar después de usarlos para 404
            include __DIR__ . '/../app/Views/layouts/main_layout.php';
            exit;
    }
}

// Incluir el layout principal para la página de bienvenida
// Esto se ejecuta si:
// 1. La ruta es la raíz ('', 'index.php') Y el usuario NO está logueado.
// (Si el usuario ESTÁ logueado y en la raíz, la primera sección de redirección ya habrá hecho un exit).
if ( ($route === '' || $route === 'index.php') && !isset($_SESSION['user_id']) ) {
    // $pageTitle ya está seteado para la bienvenida
    // $viewPath es null, main_layout.php debe manejar esto para mostrar el contenido de bienvenida.
    
    // Preparar mensajes globales para la página de bienvenida (si los hay, ej. de un logout)
    $mensaje_error_global = $_SESSION['mensaje_error_global'] ?? null;
    $mensaje_exito_global = $_SESSION['mensaje_exito_global'] ?? null;
    $mensaje_info_global = $_SESSION['mensaje_info_global'] ?? null;
    unset($_SESSION['mensaje_error_global'], $_SESSION['mensaje_exito_global'], $_SESSION['mensaje_info_global']);

    extract(compact('pageTitle', 'viewPath', 'mensaje_error_global', 'mensaje_exito_global', 'mensaje_info_global'));
    require_once __DIR__ . '/../app/Views/layouts/main_layout.php';
}
?>