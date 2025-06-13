<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar configuración y clases Core
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/Core/Database.php'; 
require_once __DIR__ . '/../app/Core/Validator.php'; 

// --- Modelos ---
require_once __DIR__ . '/../app/Models/FacturaModel.php'; 

// --- Controladores que se usarán ---
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/EmpleadoController.php';
require_once __DIR__ . '/../app/Controllers/ClienteController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/CitaController.php';
require_once __DIR__ . '/../app/Controllers/ProductoController.php';
require_once __DIR__ . '/../app/Controllers/NotificacionController.php';
require_once __DIR__ . '/../app/Controllers/AtencionClienteController.php';

// --- Lógica de Enrutamiento ---
$baseUrlPath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/'); 
$requestUri = $_SERVER['REQUEST_URI'];
$requestUriPath = strtok($requestUri, '?'); 

if ($baseUrlPath === '') { 
    $cleanedRequestPath = ltrim($requestUriPath, '/');
} else { 
    if (strpos($requestUriPath, $baseUrlPath) === 0) {
        $cleanedRequestPath = substr($requestUriPath, strlen($baseUrlPath));
    } else {
        $cleanedRequestPath = $requestUriPath;
    }
    $cleanedRequestPath = ltrim($cleanedRequestPath, '/');
}

$route = trim($cleanedRequestPath, '/');

if ($route === 'index.php') { 
    $route = ''; 
}


$pageTitle = 'Clip Techs Sistem'; 
$viewPath = null; 

// --- MANEJO DE RUTAS API ESPECÍFICAS (ANTES DE RUTAS HTML) ---
if ($route === 'api/citas/empleados-disponibles') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $citaApiController = new CitaController();
        $citaApiController->obtenerEmpleadosDisponiblesParaServicio(); 
    } else {
        http_response_code(405); 
        header('Content-Type: application/json'); 
        echo json_encode(['error' => 'Método no permitido para esta API.']);
    }
    exit; 
}
// --- FIN RUTAS API ---


// 1. Manejo de la ruta raíz SI el usuario ESTÁ LOGUEADO
if ($route === '' && isset($_SESSION['user_id'])) {
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
            $user_rol_desconocido = $_SESSION['user_rol_id']; 
            $user_id_actual = $_SESSION['user_id'] ?? 'desconocido'; 
            session_unset();
            session_destroy();
            if (session_status() == PHP_SESSION_NONE) { session_start(); }
            $_SESSION['mensaje_error_global'] = "Tu rol de usuario (ID: " . htmlspecialchars($user_rol_desconocido) . ") no está configurado correctamente. Sesión terminada.";
            error_log("Usuario con ID: " . htmlspecialchars($user_id_actual) . " intentó acceder a la raíz con rol desconocido: " . htmlspecialchars($user_rol_desconocido)); 
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    } else {
        $user_id_inconsistente = $_SESSION['user_id']; 
        session_unset();
        session_destroy();
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['mensaje_error_global'] = "Error de sesión de rol. Por favor, inicia sesión de nuevo.";
        error_log("Usuario con ID: " . htmlspecialchars($user_id_inconsistente) . " tenía sesión inconsistente (user_rol_id faltante) en la raíz.");
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

// 2. Manejo de la ruta raíz SI el usuario NO ESTÁ LOGUEADO
if ($route === '') {
    if (file_exists(__DIR__ . '/../app/Views/layouts/main_layout.php')) {
        // Para la página de inicio, podrías querer un título específico o pasar datos
        $pageTitle = "Bienvenido a Clip Techs Sistem"; 
        $active_page = 'inicio'; 
        include __DIR__ . '/../app/Views/layouts/main_layout.php';
    } else {
        echo "<h1>Error: Layout principal no encontrado.</h1>";
        error_log("Error crítico: main_layout.php no encontrado.");
    }
    exit;
}

// 3. Si llegamos aquí, la ruta NO es la raíz.
$mensaje_error_global_for_route = $_SESSION['mensaje_error_global'] ?? null;
$mensaje_exito_global_for_route = $_SESSION['mensaje_exito_global'] ?? null;
$mensaje_info_global_for_route = $_SESSION['mensaje_info_global'] ?? null;

$controller = null; 
$adminController = null;

// --- MANEJO DE RUTAS CON PARÁMETROS (ANTES DEL SWITCH) ---

if (preg_match('/^empleado\/citas\/gestionar\/(\d+)$/', $route, $matches_gestionar_cita)) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol_id'] != ID_ROL_EMPLEADO && $_SESSION['user_rol_id'] != ID_ROL_ADMIN)) {
        $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    $_SESSION['mensaje_info_global'] = "Funcionalidad de gestionar cita específica aún no implementada.";
    header('Location: ' . BASE_URL . 'empleado/agenda'); 
    exit;
}

if (preg_match('/^empleado\/citas\/actualizar_estado\/(\d+)$/', $route, $matches_actualizar_cita_empleado)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
            header('Location: ' . BASE_URL . 'empleado/agenda'); 
            exit;
        }
        $controller = new EmpleadoController();
        $controller->actualizarEstadoCita($matches_actualizar_cita_empleado[1]);
    } else {
        http_response_code(405); 
        echo "Método no permitido para esta ruta. Se esperaba POST.";
    }
    exit;
}

// RUTA PARA VER DETALLE DE FACTURA EMPLEADO (GET)
if (preg_match('/^empleado\/facturas\/ver\/(\d+)$/', $route, $matches_ver_factura_empleado)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) { 
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado para ver esta factura.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->verFactura($matches_ver_factura_empleado[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta. Se esperaba GET.";
    }
    exit;
}

// RUTA PARA ACTUALIZAR ESTADO DE FACTURA EMPLEADO (POST)
if (preg_match('/^empleado\/facturas\/actualizar-estado\/(\d+)$/', $route, $matches_actualizar_estado_factura)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado para modificar esta factura.";
            header('Location: ' . BASE_URL . 'login'); 
            exit;
        }
        $controller = new EmpleadoController();
        $controller->actualizarEstadoFacturaPost($matches_actualizar_estado_factura[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta. Se esperaba POST.";
    }
    exit;
}


if (preg_match('/^producto\/(\d+)$/', $route, $matches_producto_detalle)) {
    $controller = new ProductoController();
    $controller->verDetalleProductoCliente($matches_producto_detalle[1]); 
    exit;
}

if (preg_match('/^notificaciones\/marcar-leida\/(\d+)$/', $route, $matches_marcar_leida)) {
    if (!isset($_SESSION['user_id'])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    $controller = new NotificacionController();
    $controller->marcarComoLeida($matches_marcar_leida[1]); 
    exit;
}

if (preg_match('/^admin\/pqr\/ver\/(\d+)$/', $route, $matches_ver_pqr_admin)) {
    if (!isset($adminController)) $adminController = new AdminController();
    $adminController->verPQR($matches_ver_pqr_admin[1]);
    exit;
}
if (preg_match('/^admin\/pqr\/actualizar\/(\d+)$/', $route, $matches_actualizar_pqr_admin)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->actualizarPQR($matches_actualizar_pqr_admin[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta.";
    }
    exit;
}

if (preg_match('/^admin\/users\/edit\/(\d+)$/', $route, $matches_edit_user)) {
    if (!isset($adminController)) $adminController = new AdminController();
    $adminController->editUserForm($matches_edit_user[1]);
    exit;
}
if (preg_match('/^admin\/users\/update\/(\d+)$/', $route, $matches_update_user)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->updateUser($matches_update_user[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta.";
    }
    exit;
}
if (preg_match('/^admin\/users\/delete\/(\d+)$/', $route, $matches_delete_user)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->deleteUser($matches_delete_user[1]); 
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta. Se esperaba POST.";
    }
    exit;
}
if (preg_match('/^admin\/users\/activate\/(\d+)$/', $route, $matches_activate_user)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->activateUser($matches_activate_user[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta. Se esperaba POST.";
    }
    exit;
}

if (preg_match('/^empleado\/productos\/editar\/(\d+)$/', $route, $matches_editar_producto)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->editarProductoForm($matches_editar_producto[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^empleado\/productos\/actualizar\/(\d+)$/', $route, $matches_actualizar_producto)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->actualizarProducto($matches_actualizar_producto[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^empleado\/productos\/eliminar\/(\d+)$/', $route, $matches_eliminar_producto)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->eliminarProducto($matches_eliminar_producto[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}

if (preg_match('/^empleado\/horario-recurrente\/eliminar\/(\d+)$/', $route, $matches_eliminar_hr)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
            header('Location: ' . BASE_URL . 'empleado/gestionar-horario');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->eliminarHorarioRecurrente($matches_eliminar_hr[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^empleado\/horario-excepcion\/eliminar\/(\d+)$/', $route, $matches_eliminar_ex)) {
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
            header('Location: ' . BASE_URL . 'empleado/gestionar-horario');
            exit;
        }
        $controller = new EmpleadoController();
        $controller->eliminarExcepcionHorario($matches_eliminar_ex[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}

if (preg_match('/^admin\/empleado\/(\d+)\/servicios$/', $route, $matches_gest_serv_emp)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->gestionarServiciosEmpleadoForm($matches_gest_serv_emp[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/empleado\/(\d+)\/servicios\/guardar$/', $route, $matches_save_serv_emp)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->guardarServiciosEmpleado($matches_save_serv_emp[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}

if (preg_match('/^admin\/empleado\/(\d+)\/horario$/', $route, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->gestionarHorarioEmpleadoForm($matches[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/empleado\/(\d+)\/horario\/recurrente\/guardar$/', $route, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->guardarHorarioRecurrenteAdmin($matches[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/empleado\/(\d+)\/horario\/recurrente\/eliminar\/(\d+)$/', $route, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->eliminarHorarioRecurrenteAdmin($matches[1], $matches[2]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/empleado\/(\d+)\/horario\/excepcion\/guardar$/', $route, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->guardarExcepcionHorarioAdmin($matches[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/empleado\/(\d+)\/horario\/excepcion\/eliminar\/(\d+)$/', $route, $matches)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->eliminarExcepcionHorarioAdmin($matches[1], $matches[2]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}

if (preg_match('/^admin\/servicios\/editar\/(\d+)$/', $route, $matches_edit_servicio)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->editarServicioForm($matches_edit_servicio[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/servicios\/actualizar\/(\d+)$/', $route, $matches_update_servicio)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->actualizarServicio($matches_update_servicio[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}
if (preg_match('/^admin\/servicios\/eliminar\/(\d+)$/', $route, $matches_delete_servicio)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
        if (!isset($adminController)) $adminController = new AdminController();
        $adminController->eliminarServicio($matches_delete_servicio[1]);
    } else { http_response_code(405); echo "Método no permitido."; }
    exit;
}

if (preg_match('/^citas\/cancelar\/(\d+)$/', $route, $matches_cancelar_cita_cliente)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user_id']) || !defined('ID_ROL_CLIENTE') || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        $controller = new CitaController();
        $controller->cancelarCitaCliente($matches_cancelar_cita_cliente[1]);
    } else {
        http_response_code(405);
        echo "Método no permitido para esta ruta.";
    }
    exit;
}
// --- FIN RUTAS CON PARÁMETROS ---


switch ($route) {
    case 'login':
        $controller = new AuthController();
        $mensaje_error_get = isset($_GET['mensaje_error']) ? urldecode($_GET['mensaje_error']) : null;
        $mensaje_exito_get = isset($_GET['mensaje_exito']) ? urldecode($_GET['mensaje_exito']) : null;
        $email_val_get = isset($_GET['email_val']) ? urldecode($_GET['email_val']) : '';
        $controller->showLoginForm($mensaje_error_get, $mensaje_exito_get, ['email_val' => $email_val_get]);
        exit; 

    case 'handle_login':
        $controller = new AuthController();
        $controller->handleLogin(); 
        exit; 

    case 'register':
        $controller = new AuthController();
        $controller->showRegisterForm(); 
        exit;

    case 'handle_register':
        $controller = new AuthController();
        $controller->handleRegister(); 
        exit;

    case 'logout':
        $controller = new AuthController();
        $controller->logout(); 
        exit;

    case 'dashboard': 
        if (!isset($adminController)) $adminController = new AdminController(); 
        $adminController->index(); 
        exit;

    case 'empleado/dashboard':
        $controller = new EmpleadoController(); 
        $controller->dashboard();
        exit;
    
    case 'empleado/horarios': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->verMisHorarios(); 
        } else {
            http_response_code(405); 
            echo "Método no permitido para esta ruta.";
        }
        exit;

    case 'empleado/facturas':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado para ver facturas.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->listarFacturas();
        } else {
            http_response_code(405);
            echo "Método no permitido para esta ruta. Se esperaba GET.";
        }
        exit;

    case 'empleado/productos': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
             if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->listarProductos();
        } else {
            http_response_code(405); 
            echo "Método no permitido para esta ruta.";
        }
        exit;

    case 'empleado/productos/crear': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->crearProductoForm();
        } else {
            http_response_code(405);
            echo "Método no permitido para esta ruta.";
        }
        exit;

    case 'empleado/productos/guardar': 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
                header('Location: ' . BASE_URL . 'empleado/productos'); 
                exit;
            }
            $controller = new EmpleadoController();
            $controller->guardarProducto();
        } else {
            http_response_code(405);
            echo "Método no permitido para esta ruta.";
        }
        exit;
    
    case 'portal_servicios':
        $controller = new ClienteController();
        $controller->index(); 
        exit;

    case 'profile':
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tu perfil.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $controller = new UserController();
        $controller->showProfile();
        exit;

    case 'profile/edit':
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para editar tu perfil.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $controller = new UserController();
        $controller->editProfileForm();
        exit;
    
    case 'profile/update':
        if (!isset($_SESSION['user_id'])) { 
            header("Location: " . BASE_URL . "login"); 
            exit;
        }
        $controller = new UserController();
        $controller->updateProfile(); 
        exit;

    case 'citas/reservar': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !defined('ID_ROL_CLIENTE') || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) {
                $_SESSION['mensaje_error_global'] = "Debes ser un cliente para reservar citas.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new CitaController();
            $controller->mostrarCalendarioCliente();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'citas/guardar': 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || !defined('ID_ROL_CLIENTE') || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) {
                $_SESSION['mensaje_error_global'] = "Acción no permitida.";
                header('Location: ' . BASE_URL . 'citas/reservar'); 
                exit;
            }
            $controller = new CitaController();
            $controller->guardarCitaCliente();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'mis-citas': 
    case 'cliente/citas': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !defined('ID_ROL_CLIENTE') || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión como cliente para ver tus citas.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new CitaController();
            $controller->misCitasCliente();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'cliente/notificaciones':
    case 'notificaciones': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tus notificaciones.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                $controller = new NotificacionController(); 
                $controller->listarNotificacionesCliente();
            } elseif (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                 $_SESSION['mensaje_info_global'] = "Notificaciones para empleados aún no implementadas en esta ruta general.";
                 header('Location: ' . BASE_URL . 'empleado/dashboard'); // O a una vista de notificaciones de empleado si la tienes
            } else {
                 $_SESSION['mensaje_error_global'] = "Rol no configurado para notificaciones en esta ruta.";
                 header('Location: ' . BASE_URL . 'login');
            }
        } else {
            http_response_code(405);
            echo "Método no permitido para esta ruta.";
        }
        exit;

    case 'empleado/agenda': 
    case 'empleado/citas': 
         if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || 
                !defined('ID_ROL_EMPLEADO') || 
                ($_SESSION['user_rol_id'] != ID_ROL_EMPLEADO)) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado para ver esta agenda.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController(); 
            $controller->agenda();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    
    case 'empleado/historial-citas': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !defined('ID_ROL_EMPLEADO') || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->historialCitas();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'empleado/reportes': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !defined('ID_ROL_EMPLEADO') || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->generarReportes();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'empleado/gestionar-horario':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->gestionarHorario();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    case 'empleado/horario-recurrente/guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
                header('Location: ' . BASE_URL . 'empleado/gestionar-horario');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->guardarHorarioRecurrente();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    case 'empleado/horario-excepcion/guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
                $_SESSION['mensaje_error_global'] = "Acción no autorizada.";
                header('Location: ' . BASE_URL . 'empleado/gestionar-horario');
                exit;
            }
            $controller = new EmpleadoController();
            $controller->guardarExcepcionHorario();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'admin/citas/calendario': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $citaController = new CitaController(); 
            $citaController->calendarioGeneralAdmin();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'admin/citas': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $citaController = new CitaController(); 
            $citaController->listarCitasAdmin();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'admin/usuarios':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->listarUsuarios();
        } else {
            http_response_code(405);
            echo "Método no permitido.";
        }
        exit;

    case 'catalogo': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller = new ProductoController();
            $controller->mostrarCatalogoCliente();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'atencion-cliente/nueva': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller = new AtencionClienteController();
            $controller->nuevaSolicitudForm();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;

    case 'atencion-cliente/guardar': 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new AtencionClienteController();
            $controller->guardarSolicitud();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
        
    case 'mis-solicitudes': 
    case 'cliente/solicitudes': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller = new AtencionClienteController();
            $controller->misSolicitudes();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    
    case 'admin/pqr': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController(); 
            $adminController->listarPQR();
        } else {
             http_response_code(405); 
             echo "Método no permitido para esta ruta.";
        }
        exit;
    
    case 'admin/users/create': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->createUserForm();
        } else {
             http_response_code(405); 
             echo "Método no permitido para esta ruta.";
        }
        exit;

    case 'admin/users/store': 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->storeUser();
        } else {
             http_response_code(405); 
             echo "Método no permitido para esta ruta.";
        }
        exit;
    
    case 'admin/reports': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->showReportsPage();
        } else {
             http_response_code(405); 
             echo "Método no permitido para esta ruta.";
        }
        exit;
    
    case 'admin/servicios':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->listarServicios();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    case 'admin/servicios/crear':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->crearServicioForm();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    case 'admin/servicios/guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($adminController)) $adminController = new AdminController();
            $adminController->guardarServicio();
        } else { http_response_code(405); echo "Método no permitido."; }
        exit;
    
    default:
        error_log("Ruta no encontrada en el enrutador: '" . $route . "' (Request URI: " . $_SERVER['REQUEST_URI'] . ")");
        http_response_code(404);
        $pageTitle = "404 - Página No Encontrada";
        
        $data_for_404 = compact('pageTitle', 
                                'mensaje_error_global_for_route', 
                                'mensaje_exito_global_for_route', 
                                'mensaje_info_global_for_route',
                                'route' 
                            );
        extract($data_for_404);
        
        if (isset($_SESSION['mensaje_error_global'])) unset($_SESSION['mensaje_error_global']);
        if (isset($_SESSION['mensaje_exito_global'])) unset($_SESSION['mensaje_exito_global']);
        if (isset($_SESSION['mensaje_info_global'])) unset($_SESSION['mensaje_info_global']);

        $viewPath = __DIR__ . '/../app/Views/errors/404.php'; 
        if (file_exists(__DIR__ . '/../app/Views/layouts/main_layout.php')) {
             include __DIR__ . '/../app/Views/layouts/main_layout.php';
        } elseif (file_exists($viewPath)) {
            include $viewPath; 
        } else {
             echo "<h1>404 - Página no encontrada</h1><p>La página que buscas ('" . htmlspecialchars($route) . "') no existe.</p>";
        }
        exit;
}

if (!headers_sent()) { 
    error_log("Fallback inesperado al final del enrutador. Ruta: '" . $route . "', Sesión ID: " . ($_SESSION['user_id'] ?? 'ninguna'));
    http_response_code(500); 
    echo "<h1>Error Inesperado del Sistema</h1><p>No se pudo procesar la solicitud para la ruta: '" . htmlspecialchars($route) . "'.</p>";
    exit;
}
?>