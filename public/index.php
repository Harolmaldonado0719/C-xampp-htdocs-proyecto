<?php
// Iniciar sesión en todas las páginas que usan el router
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar configuración (necesaria para BASE_URL y otras constantes)
require_once __DIR__ . '/../app/config.php';

// --- Lógica de Enrutamiento ---

// Obtener la ruta de la URL
$basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
$requestUri = $_SERVER['REQUEST_URI'];
$requestUriPath = strtok($requestUri, '?'); // Limpiar parámetros GET para la lógica de ruta

$route = '';
if (strpos($requestUriPath, $basePath) === 0) {
    $route = substr($requestUriPath, strlen($basePath));
} else {
    $route = ltrim($requestUriPath, '/');
}
$route = trim($route, '/');

// Si la ruta está vacía (raíz del sitio público) o es 'index.php',
// decidir a dónde ir según el estado de sesión y rol.
if ($route === '' || $route === 'index.php') {
    if (isset($_SESSION['usuario_id'])) {
        // Usuario logueado, redirigir según rol
        if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
            $route = 'dashboard';
        } else {
            // Para 'cliente' u otros roles no admin
            $route = 'portal_servicios';
        }
    } else {
        // Usuario no logueado, ir al login
        $route = 'login';
    }
}

// --- Cargar el Controlador Apropiado Basado en la Ruta ---
switch ($route) {
    case 'login':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $mensaje_error = $_GET['mensaje_error'] ?? ($_SESSION['mensaje_error_global'] ?? null);
        $mensaje_exito = $_GET['mensaje_exito'] ?? ($_SESSION['mensaje_exito_global'] ?? null);
        unset($_SESSION['mensaje_error_global'], $_SESSION['mensaje_exito_global']); // Limpiar mensajes de sesión
        $controller->showLoginForm($mensaje_error, $mensaje_exito);
        break;

    case 'handle_login':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->handleLogin();
        break;

    case 'register':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->showRegisterForm();
        break;

    case 'handle_register':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->handleRegister();
        break;

    case 'logout':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'dashboard':
        // Proteger ruta: solo para administradores
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta página.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            // Si está logueado pero no es admin, redirigir al portal de servicios con un mensaje
            $_SESSION['mensaje_error_portal'] = "No tienes permiso para acceder al dashboard."; // Mensaje para el portal
            header("Location: " . BASE_URL . "portal_servicios");
            exit;
        }
        require_once __DIR__ . '/../app/Controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    // --- NUEVA RUTA PARA EL PORTAL DE SERVICIOS DEL CLIENTE ---
    case 'portal_servicios':
        // Proteger ruta: solo para usuarios logueados
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder al portal de servicios.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        // Aquí necesitarás un nuevo controlador y vista
        require_once __DIR__ . '/../app/Controllers/PortalController.php'; // Crear este controlador
        $controller = new PortalController();
        $controller->index(); // Crear este método
        break;

    // --- Rutas para UserController (perfil) ---
    case 'profile':
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tu perfil.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->showProfile();
        break;

    case 'profile/edit':
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para editar tu perfil.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->editProfileForm();
        break;
    
    case 'profile/update':
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "login"); // No debería llegar aquí sin sesión si el form está protegido
            exit;
        }
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->updateProfile();
        break;
    // --- Fin de Rutas para UserController ---
    
    default:
        http_response_code(404);
        // Podrías cargar una vista de error 404 más elaborada
        $pageTitle = "404 - Página No Encontrada";
        $viewPath = __DIR__ . '/../app/Views/errors/404.php'; // Crear esta vista si quieres
        if (file_exists($viewPath)) {
            $data_for_view = compact('pageTitle', 'route');
            extract($data_for_view);
            include __DIR__ . '/../app/Views/layouts/main_layout.php';
        } else {
            echo "<h1>404 - Página No Encontrada</h1>";
            echo "<p>La ruta solicitada '/" . htmlspecialchars($route) . "' no fue encontrada.</p>";
            echo "<a href='" . BASE_URL . "'>Volver al inicio</a>";
        }
        break;
}
?>