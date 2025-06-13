
<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php'; 
require_once __DIR__ . '/../Models/CitaModel.php'; 
require_once __DIR__ . '/../Models/SolicitudModel.php'; 
require_once __DIR__ . '/../Models/NotificacionModel.php';
require_once __DIR__ . '/../Models/ServicioModel.php'; 
require_once __DIR__ . '/../Models/EmpleadoServicioModel.php'; 
require_once __DIR__ . '/../Models/RolModel.php';
require_once __DIR__ . '/../Models/HorarioModel.php'; 
use App\Models\HorarioModel; // Línea añadida/descomentada

class AdminController { 
    private $db;
    private $conn;
    private $userModel; 
    private $citaModel;
    private $solicitudModel; 
    private $notificacionModel;
    private $servicioModel; 
    private $empleadoServicioModel; 
    private $rolModel;
    private $horarioModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta área.";
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'login');
            }
            exit;
        }

        if (!isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_ADMIN) {
            $_SESSION['mensaje_error_global'] = "No tienes permisos para acceder a esta sección.";
            if (!headers_sent()) {
                if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                    header('Location: ' . BASE_URL . 'portal_servicios');
                } elseif (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                    header('Location: ' . BASE_URL . 'empleado/dashboard');
                } else {
                    header('Location: ' . BASE_URL . 'login');
                }
            }
            exit;
        }

        try {
            $this->db = new Database();
            $this->conn = $this->db->getPdoConnection(); 
            
            if (!$this->conn) { 
                throw new Exception("La conexión a la base de datos no pudo ser establecida por AdminController.");
            }

            $this->userModel = new User($this->conn);
            $this->citaModel = new CitaModel($this->conn);
            $this->solicitudModel = new SolicitudModel($this->conn); // Este es tu modelo para PQR
            $this->notificacionModel = new NotificacionModel($this->conn);
            $this->servicioModel = new ServicioModel($this->conn);
            $this->empleadoServicioModel = new EmpleadoServicioModel($this->conn);
            $this->rolModel = new RolModel($this->conn); 
            $this->horarioModel = new HorarioModel($this->conn); 

        } catch (Exception $e) {
            error_log("AdminController Constructor: Error de conexión a BD o instanciación de modelos - " . $e->getMessage());
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema. Por favor, intente más tarde. (AdminInit)";
            if(defined('DEBUG_MODE') && DEBUG_MODE){ 
                die("Error crítico del sistema al inicializar AdminController: " . $e->getMessage() . ". Revise los logs.");
            } else {
                die("Error crítico del sistema. Por favor, contacte al administrador.");
            }
        }
    }

    public function index() {
        $pageTitle = "Dashboard Administrador";
        $active_page = 'dashboard_admin';
        $usuarios_recientes = []; 
        $totalUsuarios = 0;
        $totalCitasPendientes = 0;
        $totalSolicitudesAbiertas = 0;

        if ($this->userModel && method_exists($this->userModel, 'getUsersWithRoles')) {
            $usuarios_recientes = $this->userModel->getUsersWithRoles(5, 0, 'fecha_registro', 'DESC'); 
            foreach ($usuarios_recientes as &$usuario_r) { 
                unset($usuario_r['password_hash']); 
            }
            unset($usuario_r); 
        } else {
            error_log("AdminController::index - userModel no disponible o método getUsersWithRoles no existe.");
        }
        
        if ($this->userModel && method_exists($this->userModel, 'contarUsuarios')) {
            $totalUsuarios = $this->userModel->contarUsuarios();
        }
        
        if ($this->citaModel && method_exists($this->citaModel, 'contarCitasPorEstado')) {
            $totalCitasPendientes = ($this->citaModel->contarCitasPorEstado('Pendiente') ?? 0) + ($this->citaModel->contarCitasPorEstado('Confirmada') ?? 0);
        }
        $totalSolicitudesAbiertas = $this->contarSolicitudesPorEstadoInterno('Abierta');

        $usuario_nombre = $_SESSION['user_nombre'] ?? 'Administrador'; 
        $viewPath = dirname(__DIR__) . '/Views/admin/index.php'; 
        extract(compact('pageTitle', 'active_page', 'usuarios_recientes', 'usuario_nombre', 'viewPath', 'totalUsuarios', 'totalCitasPendientes', 'totalSolicitudesAbiertas')); 
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::index.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista del dashboard ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function listarUsuarios() {
        $pageTitle = "Gestionar Usuarios";
        $active_page = 'admin_users';
        $trabajadores = [];
        $clientes = [];
        $usuarios_recientes = []; 

        if ($this->userModel && method_exists($this->userModel, 'getUsersWithRoles')) {
            $todosLosUsuarios = $this->userModel->getUsersWithRoles(null, 0, 'id', 'ASC'); 
            $usuarios_recientes = $this->userModel->getUsersWithRoles(5, 0, 'fecha_registro', 'DESC'); 
            
            foreach ($todosLosUsuarios as &$usuario) {
                if (isset($usuario['password_hash'])) unset($usuario['password_hash']); 
                
                if (isset($usuario['rol_id'])) {
                    if ($usuario['rol_id'] == ID_ROL_ADMIN || $usuario['rol_id'] == ID_ROL_EMPLEADO) {
                        $trabajadores[] = $usuario;
                    } elseif ($usuario['rol_id'] == ID_ROL_CLIENTE) {
                        $clientes[] = $usuario;
                    }
                }
            }
            unset($usuario); 

            usort($trabajadores, function ($a, $b) {
                if ($a['rol_id'] == ID_ROL_ADMIN && $b['rol_id'] != ID_ROL_ADMIN) {
                    return -1; 
                }
                if ($a['rol_id'] != ID_ROL_ADMIN && $b['rol_id'] == ID_ROL_ADMIN) {
                    return 1; 
                }
                return $a['id'] <=> $b['id']; 
            });

            foreach ($usuarios_recientes as &$usuario_r) {
                if (isset($usuario_r['password_hash'])) unset($usuario_r['password_hash']);
            }
            unset($usuario_r);

        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario no disponible para listar.";
            error_log("AdminController::listarUsuarios - UserModel no disponible o método getUsersWithRoles no existe.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/users/listar_usuarios.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'trabajadores', 'clientes', 'usuarios_recientes')); 
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::listarUsuarios.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de listar usuarios ('{$viewPath}') ni el layout principal.";
            }
        }
    }
    
    private function contarSolicitudesPorEstadoInterno($estado) {
        if (!$this->solicitudModel || !$this->conn) return 0;
        // Asumiendo que SolicitudModel tiene un método para contar por estado
        if (method_exists($this->solicitudModel, 'contarPorEstado')) {
            return $this->solicitudModel->contarPorEstado($estado);
        }
        // Fallback si el método no existe, usa la lógica anterior (menos eficiente)
        $solicitudes = $this->solicitudModel->obtenerTodas(99999,0); 
        $count = 0;
        foreach ($solicitudes as $solicitud) {
            if (isset($solicitud['estado']) && strtolower($solicitud['estado']) === strtolower($estado)) {
                $count++;
            }
        }
        return $count;
    }

    public function listarPQR() {
        $pageTitle = "Gestionar Solicitudes (PQR)";
        $active_page = 'admin_pqr';
        $solicitudes = [];
        if ($this->solicitudModel && $this->conn) {
            $solicitudes = $this->solicitudModel->obtenerTodas(); 
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudo cargar el modelo de solicitudes.";
            error_log("AdminController::listarPQR - SolicitudModel no disponible.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/pqr/listar_pqr.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'solicitudes'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::listarPQR.");
             if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de PQR ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function verPQR($id_solicitud_str) {
        $id_solicitud = filter_var($id_solicitud_str, FILTER_VALIDATE_INT);
        if (!$id_solicitud) {
            $_SESSION['mensaje_error_global'] = "ID de solicitud inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/pqr");
            }
            exit;
        }

        $pageTitle = "Detalle Solicitud PQR-" . htmlspecialchars($id_solicitud);
        $active_page = 'admin_pqr';
        $solicitud = null;
        $estados_posibles = ['Abierta', 'En Proceso', 'Resuelta', 'Cerrada', 'Requiere Información Adicional'];

        if ($this->solicitudModel && $this->conn) {
            $solicitud = $this->solicitudModel->obtenerPorId($id_solicitud);
        }

        if (!$solicitud) {
            $_SESSION['mensaje_error_global'] = "Solicitud PQR no encontrada.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/pqr");
            }
            exit;
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/pqr/ver_pqr.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'solicitud', 'estados_posibles'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::verPQR.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de detalle PQR ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function actualizarPQR($id_solicitud_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/pqr"); 
            }
            exit;
        }
        $id_solicitud = filter_var($id_solicitud_str, FILTER_VALIDATE_INT);
        if (!$id_solicitud) {
            $_SESSION['mensaje_error_global'] = "ID de solicitud inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/pqr");
            }
            exit;
        }

        $estado = $_POST['estado'] ?? null;
        $respuesta_admin = trim($_POST['respuesta_admin'] ?? '');
        $admin_id = $_SESSION['user_id']; 

        if (empty($estado) || empty($respuesta_admin)) {
            $_SESSION['mensaje_error_global'] = "El estado y la respuesta son obligatorios.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/pqr/ver/" . $id_solicitud);
            }
            exit;
        }

        if ($this->solicitudModel && $this->conn) {
            if ($this->solicitudModel->actualizarRespuestaAdmin($id_solicitud, $respuesta_admin, $estado, $admin_id)) {
                $_SESSION['mensaje_exito_global'] = "Solicitud PQR-" . htmlspecialchars($id_solicitud) . " actualizada correctamente.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al actualizar la solicitud PQR-" . htmlspecialchars($id_solicitud) . ".";
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de solicitudes no disponible.";
            error_log("AdminController::actualizarPQR - SolicitudModel no disponible.");
        }
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "admin/pqr/ver/" . $id_solicitud);
        }
        exit;
    }

    public function createUserForm() {
        $pageTitle = "Crear Nuevo Usuario";
        $active_page = 'admin_users'; 
        
        $form_data = $_SESSION['form_data_user_create'] ?? [];
        $form_errors = $_SESSION['form_errors_user_create'] ?? [];
        unset($_SESSION['form_data_user_create'], $_SESSION['form_errors_user_create']);

        $roles = [];
        if ($this->rolModel && method_exists($this->rolModel, 'getAll')) {
            $roles = $this->rolModel->getAll();
        }

        $viewPath = dirname(__DIR__) . '/Views/admin/users/create_user.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'form_data', 'form_errors', 'roles'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::createUserForm.");
             if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de crear usuario ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/create");
            }
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? ''); 
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);
        $telefono = trim($_POST['telefono'] ?? ''); 
        $activo = isset($_POST['activo']) ? 1 : 0; 

        $errors = [];
        if (empty($nombre)) { $errors['nombre'] = "El nombre es obligatorio."; }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "El email no es válido.";
        } elseif ($this->userModel && method_exists($this->userModel, 'emailExists') && $this->userModel->emailExists($email)) {
            $errors['email'] = "Este email ya está registrado.";
        }
        if (empty($password) || strlen($password) < 6) { $errors['password'] = "La contraseña debe tener al menos 6 caracteres.";}
        if ($password !== $confirm_password) { $errors['confirm_password'] = "Las contraseñas no coinciden."; }
        
        $roles_validos_ids = [];
        if ($this->rolModel && method_exists($this->rolModel, 'getAll')) {
            $roles_existentes = $this->rolModel->getAll();
            $roles_validos_ids = array_column($roles_existentes, 'id');
        }
        if ($rol_id === false || $rol_id === null || !in_array($rol_id, $roles_validos_ids)) {
             if (empty($roles_validos_ids)) { 
                $errors['rol_id'] = "Error al validar el rol. Intente de nuevo.";
                error_log("AdminController::storeUser - No se pudieron cargar roles para validación.");
            } else {
                $errors['rol_id'] = "Selecciona un rol válido.";
            }
        }

        if (!empty($telefono) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
            $errors['telefono'] = "El formato del teléfono no es válido.";
        }
        
        $fotografia_filename = null; 
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/'; 
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) { 
                $errors['fotografia'] = "Error: No se pudo acceder o crear el directorio de subidas.";
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; 
                if (in_array($_FILES['fotografia']['type'], $allowedTypes)) {
                    $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
                    $fotografia_filename = uniqid('user_') . '.' . strtolower($extension);
                    if (!move_uploaded_file($_FILES['fotografia']['tmp_name'], $uploadDir . $fotografia_filename)) {
                        $errors['fotografia'] = "Error al subir la fotografía.";
                        $fotografia_filename = null;
                    }
                } else {
                    $errors['fotografia'] = "Tipo de archivo no permitido para la fotografía.";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors_user_create'] = $errors;
            $_SESSION['form_data_user_create'] = $_POST;
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/create");
            }
            exit;
        }

        if ($this->userModel && method_exists($this->userModel, 'create')) {
            // El modelo User::create se encarga de hashear la contraseña
            if ($this->userModel->create($nombre, $apellido, $email, $password, $rol_id, $telefono, $fotografia_filename, $activo)) {
                $_SESSION['mensaje_exito_global'] = "Usuario '" . htmlspecialchars($nombre) . "' creado exitosamente.";
                if (!headers_sent()) {
                    header("Location: " . BASE_URL . "admin/usuarios"); 
                }
            } else {
                $_SESSION['mensaje_error_global'] = "Error al crear el usuario en la base de datos.";
                $_SESSION['form_data_user_create'] = $_POST; 
                if (!headers_sent()) {
                    header("Location: " . BASE_URL . "admin/users/create");
                }
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario no disponible.";
            error_log("AdminController::storeUser - UserModel no disponible o método create no existe.");
            $_SESSION['form_data_user_create'] = $_POST;
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/create");
            }
        }
        exit;
    }

    public function editUserForm($id_str) {
        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de usuario inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios"); 
            }
            exit;
        }

        if (!$this->userModel || !method_exists($this->userModel, 'findById')) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario no disponible.";
            error_log("AdminController::editUserForm - UserModel no disponible o método findById no existe.");
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        $usuario = $this->userModel->findById($id);

        if (!$usuario) {
            $_SESSION['mensaje_error_global'] = "Usuario no encontrado.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }
        unset($usuario['password_hash']); 

        $pageTitle = "Editar Usuario: " . htmlspecialchars($usuario['nombre']);
        $active_page = 'admin_users'; 
        
        $form_errors = $_SESSION['form_errors_user_edit'][$id] ?? [];
        $form_data_from_session = $_SESSION['form_data_user_edit'][$id] ?? [];
        $form_data = array_merge($usuario, $form_data_from_session); 

        unset($_SESSION['form_errors_user_edit'][$id], $_SESSION['form_data_user_edit'][$id]);

        $roles = [];
        if ($this->rolModel && method_exists($this->rolModel, 'getAll')) {
            $roles = $this->rolModel->getAll();
        }

        $viewPath = dirname(__DIR__) . '/Views/admin/users/edit_user.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'usuario', 'form_errors', 'form_data', 'roles'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::editUserForm.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de editar usuario ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function updateUser($id_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios"); 
            }
            exit;
        }
        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de usuario inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        if (!$this->userModel || !method_exists($this->userModel, 'update') || !method_exists($this->userModel, 'findById')) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario no disponible.";
            error_log("AdminController::updateUser - UserModel no disponible o métodos necesarios no existen.");
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/edit/" . $id);
            }
            exit;
        }
        
        $usuarioActual = $this->userModel->findById($id);
        if (!$usuarioActual) {
            $_SESSION['mensaje_error_global'] = "Usuario no encontrado para actualizar.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? ''); 
        $email = trim($_POST['email'] ?? '');
        $password_nueva_texto_plano = $_POST['password'] ?? ''; // Contraseña nueva en texto plano
        $confirm_password = $_POST['confirm_password'] ?? '';
        $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);
        $telefono = trim($_POST['telefono'] ?? ''); 
        $activo = isset($_POST['activo']) ? 1 : 0; 

        $errors = [];
        if (empty($nombre)) { $errors['nombre'] = "El nombre es obligatorio."; }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "El email no es válido.";
        } elseif (strtolower($email) !== strtolower($usuarioActual['email'])) {
            if ($this->userModel->emailExists($email)) {
                $errors['email'] = "Este email ya está registrado por otro usuario.";
            }
        }

        $password_para_pasar_al_modelo = null; // Por defecto, no se cambia la contraseña
        if (!empty($password_nueva_texto_plano)) {
            if (strlen($password_nueva_texto_plano) < 6) {
                $errors['password'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            } elseif ($password_nueva_texto_plano !== $confirm_password) {
                $errors['confirm_password'] = "Las nuevas contraseñas no coinciden.";
            } else {
                // Si la contraseña es válida, se pasa en texto plano al modelo.
                // El modelo User::update() se encargará de hashearla.
                $password_para_pasar_al_modelo = $password_nueva_texto_plano;
                error_log("AdminController::updateUser - Se pasará la nueva contraseña en texto plano ('{$password_para_pasar_al_modelo}') al modelo para el ID: $id");
            }
        }
        
        $roles_validos_ids = [];
        if ($this->rolModel && method_exists($this->rolModel, 'getAll')) {
            $roles_existentes = $this->rolModel->getAll();
            $roles_validos_ids = array_column($roles_existentes, 'id');
        }
        if ($rol_id === false || $rol_id === null || !in_array($rol_id, $roles_validos_ids)) {
             if (empty($roles_validos_ids)) {
                $errors['rol_id'] = "Error al validar el rol. Intente de nuevo.";
                error_log("AdminController::updateUser - No se pudieron cargar roles para validación.");
            } else {
                $errors['rol_id'] = "Selecciona un rol válido.";
            }
        }

        if (!empty($telefono) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
            $errors['telefono'] = "El formato del teléfono no es válido.";
        }
        
        $fotografia_a_actualizar = $usuarioActual['fotografia'] ?? null; 
        $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/'; 

        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($uploadDir)) { 
                 $errors['fotografia'] = "Error: El directorio de subidas no existe o no es accesible.";
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['fotografia']['type'], $allowedTypes)) {
                    if ($usuarioActual['fotografia'] && $usuarioActual['fotografia'] !== 'default-avatar.png' && file_exists($uploadDir . $usuarioActual['fotografia'])) {
                        @unlink($uploadDir . $usuarioActual['fotografia']);
                    }
                    $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
                    $fotografia_a_actualizar = uniqid('user_') . '.' . strtolower($extension);
                    if (!move_uploaded_file($_FILES['fotografia']['tmp_name'], $uploadDir . $fotografia_a_actualizar)) {
                        $errors['fotografia'] = "Error al subir la nueva fotografía.";
                        $fotografia_a_actualizar = $usuarioActual['fotografia']; 
                    }
                } else {
                    $errors['fotografia'] = "Tipo de archivo no permitido para la fotografía.";
                }
            }
        } elseif (isset($_POST['eliminar_fotografia']) && $_POST['eliminar_fotografia'] == '1') {
            if ($usuarioActual['fotografia'] && $usuarioActual['fotografia'] !== 'default-avatar.png' && file_exists($uploadDir . $usuarioActual['fotografia'])) {
                @unlink($uploadDir . $usuarioActual['fotografia']);
            }
            $fotografia_a_actualizar = null; 
        }

        if (!empty($errors)) {
            $_SESSION['form_errors_user_edit'][$id] = $errors;
            $_SESSION['form_data_user_edit'][$id] = $_POST; 
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/edit/" . $id);
            }
            exit;
        }
        
        // Pasar la contraseña en texto plano (o null si no se cambia) al modelo
        $actualizado = $this->userModel->update(
            $id, 
            $nombre, 
            $apellido, 
            $email, 
            $rol_id, 
            $telefono, 
            $fotografia_a_actualizar, 
            $activo, 
            $password_para_pasar_al_modelo // Aquí se pasa la contraseña en texto plano o null
        );

        if ($actualizado) {
            $_SESSION['mensaje_exito_global'] = "Usuario '" . htmlspecialchars($nombre) . "' actualizado exitosamente.";
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) { 
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_fotografia'] = $fotografia_a_actualizar;
                // $_SESSION['user_email'] = $email; // El email en sesión no se suele actualizar directamente aquí
                // $_SESSION['user_rol_id'] = $rol_id; // El rol en sesión no se suele actualizar directamente aquí
            }
            if (!headers_sent()) {
                header('Location: ' . BASE_URL . 'admin/usuarios'); 
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error al actualizar el usuario o no hubo cambios.";
            $_SESSION['form_data_user_edit'][$id] = $_POST;
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/users/edit/" . $id);
            }
        }
        exit;
    }

    public function deleteUser($id_str) { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de usuario inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
            $_SESSION['mensaje_error_global'] = "No puedes desactivarte a ti mismo.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }
        
        if ($this->userModel && method_exists($this->userModel, 'desactivar')) {
            if ($this->userModel->desactivar($id)) {
                $_SESSION['mensaje_exito_global'] = "Usuario (ID: " . htmlspecialchars($id) . ") desactivado correctamente.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al desactivar el usuario (ID: " . htmlspecialchars($id) . ") o no hubo cambios.";
                error_log("AdminController::deleteUser - Error al llamar a userModel->desactivar() para el ID: $id");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario o método no disponible.";
            error_log("AdminController::deleteUser - UserModel o método desactivar no existe.");
        }
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "admin/usuarios");
        }
        exit;
    }

    public function activateUser($id_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de usuario inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }
        
        if ($this->userModel && method_exists($this->userModel, 'activar')) {
            if ($this->userModel->activar($id)) {
                $_SESSION['mensaje_exito_global'] = "Usuario (ID: " . htmlspecialchars($id) . ") activado correctamente.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al activar el usuario (ID: " . htmlspecialchars($id) . ") o no hubo cambios.";
                error_log("AdminController::activateUser - Error al llamar a userModel->activar() para el ID: $id");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de usuario o método no disponible.";
            error_log("AdminController::activateUser - UserModel o método activar no existe.");
        }
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "admin/usuarios");
        }
        exit;
    }

    // Método modificado para generar los datos para la vista de reportes
    public function showReportsPage() {
        $pageTitle = "Reportes Generales";
        $active_page = 'admin_reports'; // Asegúrate que esta clave coincida con tu layout para el menú activo
        
        $reportData = [];

        // Reporte de Usuarios
        if ($this->userModel && method_exists($this->userModel, 'contarUsuarios')) {
            $reportData['totalUsuarios'] = $this->userModel->contarUsuarios();
        } else {
            $reportData['totalUsuarios'] = 0;
            error_log("AdminController::showReportsPage - userModel no disponible o método contarUsuarios no existe.");
        }

        // Reporte de Citas
        if ($this->citaModel && method_exists($this->citaModel, 'contarCitasPorEstado')) {
            $reportData['citasPendientes'] = $this->citaModel->contarCitasPorEstado('Pendiente') ?? 0;
            $reportData['citasConfirmadas'] = $this->citaModel->contarCitasPorEstado('Confirmada') ?? 0;
            $reportData['citasCompletadas'] = $this->citaModel->contarCitasPorEstado('Completada') ?? 0;
            
            // Sumar todos los tipos de citas canceladas
            $citasCanceladas = 0;
            // Ajusta estos estados según los que realmente uses en tu sistema para citas canceladas
            $estadosCancelacionCitas = ['Cancelada Empleado', 'Cancelada Cliente', 'Cancelada Sistema', 'Cancelada'];
            foreach ($estadosCancelacionCitas as $estadoCancelacion) {
                $citasCanceladas += ($this->citaModel->contarCitasPorEstado($estadoCancelacion) ?? 0);
            }
            $reportData['citasCanceladas'] = $citasCanceladas;

        } else {
            $reportData['citasPendientes'] = 0;
            $reportData['citasConfirmadas'] = 0;
            $reportData['citasCompletadas'] = 0;
            $reportData['citasCanceladas'] = 0;
            error_log("AdminController::showReportsPage - citaModel no disponible o método contarCitasPorEstado no existe.");
        }
        
        // Reporte de Solicitudes (PQR)
        // Asumiendo que SolicitudModel tiene un método contarPorEstado o similar.
        // Si no, tendrás que implementar esa lógica en SolicitudModel o aquí.
        $estadosPQR = [
            'pqrAbiertas' => 'Abierta',
            'pqrEnProceso' => 'En Proceso',
            'pqrResueltas' => 'Resuelta',
            'pqrCerradas' => 'Cerrada'
            // Añade 'Requiere Información Adicional' si también lo quieres en el reporte principal
        ];

        foreach ($estadosPQR as $key => $estadoValor) {
            if ($this->solicitudModel && method_exists($this->solicitudModel, 'contarPorEstado')) {
                 // Idealmente, SolicitudModel tendría un método como contarPorEstado
                $reportData[$key] = $this->solicitudModel->contarPorEstado($estadoValor) ?? 0;
            } elseif ($this->solicitudModel && method_exists($this->solicitudModel, 'obtenerTodas')) {
                // Fallback: contar manualmente si el método específico no existe
                $todasLasSolicitudes = $this->solicitudModel->obtenerTodas();
                $count = 0;
                foreach ($todasLasSolicitudes as $solicitud) {
                    if (isset($solicitud['estado']) && strtolower($solicitud['estado']) === strtolower($estadoValor)) {
                        $count++;
                    }
                }
                $reportData[$key] = $count;
            } else {
                $reportData[$key] = 0;
                if ($key === 'pqrAbiertas') { // Solo loguear una vez por PQR si el modelo falla
                     error_log("AdminController::showReportsPage - solicitudModel no disponible o métodos necesarios no existen.");
                }
            }
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/reports/main_reports.php'; 
        extract(compact('pageTitle', 'active_page', 'viewPath', 'reportData'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::showReportsPage.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de reportes ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function gestionarServiciosEmpleadoForm($empleado_id_str) {
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        if (!$empleado_id) {
            $_SESSION['mensaje_error_global'] = "ID de empleado inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios"); 
            }
            exit;
        }

        $empleado = null;
        if ($this->userModel && method_exists($this->userModel, 'findById')) {
            $empleado = $this->userModel->findById($empleado_id);
        }

        if (!$empleado || $empleado['rol_id'] != ID_ROL_EMPLEADO) { 
            $_SESSION['mensaje_error_global'] = "Empleado no encontrado o no es un empleado válido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios");
            }
            exit;
        }

        $pageTitle = "Gestionar Servicios de " . htmlspecialchars($empleado['nombre'] . " " . ($empleado['apellido'] ?? ''));
        $active_page = 'admin_users'; 

        $todosLosServicios = [];
        if ($this->servicioModel && method_exists($this->servicioModel, 'obtenerTodosActivos')) { 
            $todosLosServicios = $this->servicioModel->obtenerTodosActivos();
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los servicios.";
            error_log("AdminController::gestionarServiciosEmpleadoForm - ServicioModel no disponible o método obtenerTodosActivos no existe.");
        }

        $serviciosAsignadosIds = [];
        if ($this->empleadoServicioModel && method_exists($this->empleadoServicioModel, 'obtenerIdsServiciosPorEmpleado')) { 
            $serviciosAsignadosIds = $this->empleadoServicioModel->obtenerIdsServiciosPorEmpleado($empleado_id);
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los servicios asignados al empleado.";
            error_log("AdminController::gestionarServiciosEmpleadoForm - EmpleadoServicioModel no disponible o método obtenerIdsServiciosPorEmpleado no existe.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/users/gestionar_servicios.php'; 
        extract(compact('pageTitle', 'active_page', 'viewPath', 'empleado', 'todosLosServicios', 'serviciosAsignadosIds'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::gestionarServiciosEmpleadoForm.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de gestionar servicios ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function guardarServiciosEmpleado($empleado_id_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id_str . "/servicios");
            }
            exit;
        }
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        if (!$empleado_id) {
            $_SESSION['mensaje_error_global'] = "ID de empleado inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/usuarios"); 
            }
            exit;
        }
        
        if (!$this->empleadoServicioModel) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de asignación de servicios no disponible.";
            error_log("AdminController::guardarServiciosEmpleado - EmpleadoServicioModel no instanciado.");
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/servicios");
            }
            exit;
        }

        $serviciosActualesIds = $this->empleadoServicioModel->obtenerIdsServiciosPorEmpleado($empleado_id);
        
        $serviciosSeleccionados = isset($_POST['servicios']) && is_array($_POST['servicios']) ? $_POST['servicios'] : [];
        $serviciosSeleccionadosIds = array_map('intval', $serviciosSeleccionados);

        $errores = 0;

        $serviciosParaAnadir = array_diff($serviciosSeleccionadosIds, $serviciosActualesIds);
        foreach ($serviciosParaAnadir as $servicio_id_anadir) {
            if (!$this->empleadoServicioModel->asignarServicioAEmpleado($empleado_id, $servicio_id_anadir)) {
                $errores++;
                error_log("Error al asignar servicio ID $servicio_id_anadir a empleado ID $empleado_id");
            }
        }

        $serviciosParaQuitar = array_diff($serviciosActualesIds, $serviciosSeleccionadosIds);
        foreach ($serviciosParaQuitar as $servicio_id_quitar) {
            if (!$this->empleadoServicioModel->removerServicioDeEmpleado($empleado_id, $servicio_id_quitar)) {
                $errores++;
                 error_log("Error al remover servicio ID $servicio_id_quitar de empleado ID $empleado_id");
            }
        }

        if ($errores > 0) {
            $_SESSION['mensaje_error_global'] = "Hubo $errores errores al actualizar los servicios del empleado.";
        } else {
            $_SESSION['mensaje_exito_global'] = "Servicios del empleado actualizados correctamente.";
        }
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/servicios");
        }
        exit;
    }

    public function listarServicios() {
        $pageTitle = "Gestionar Servicios";
        $active_page = 'admin_servicios'; 
        $servicios = [];

        if ($this->servicioModel) {
            $servicios = $this->servicioModel->obtenerTodos();
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de servicios no disponible.";
            error_log("AdminController::listarServicios - ServicioModel no instanciado.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/admin/servicios/listar.php'; 
        extract(compact('pageTitle', 'active_page', 'viewPath', 'servicios'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::listarServicios.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de listar servicios ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function crearServicioForm() {
        $pageTitle = "Crear Nuevo Servicio";
        $active_page = 'admin_servicios_create'; 
        
        $form_data = $_SESSION['form_data_servicio_crear'] ?? [];
        $form_errors = $_SESSION['form_errors_servicio_crear'] ?? [];
        unset($_SESSION['form_data_servicio_crear'], $_SESSION['form_errors_servicio_crear']);

        $viewPath = dirname(__DIR__) . '/Views/admin/servicios/crear.php'; 
        extract(compact('pageTitle', 'active_page', 'viewPath', 'form_data', 'form_errors'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::crearServicioForm.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de crear servicio ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function guardarServicio() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/crear");
            }
            exit;
        }

        if (!$this->servicioModel) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de servicios no disponible (guardarServicio).";
            error_log("AdminController::guardarServicio - ServicioModel no instanciado.");
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/crear");
            }
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $duracion_minutos = filter_input(INPUT_POST, 'duracion_minutos', FILTER_VALIDATE_INT);
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $activo = isset($_POST['activo']) ? 1 : 0;


        $errors = [];
        if (empty($nombre)) {
            $errors['nombre'] = "El nombre del servicio es obligatorio.";
        }
        if ($duracion_minutos === false || $duracion_minutos <= 0) {
            $errors['duracion_minutos'] = "La duración debe ser un número entero positivo.";
        }
        if ($precio === false || $precio < 0) {
            $errors['precio'] = "El precio debe ser un número positivo (puede ser 0).";
        }

        if (!empty($errors)) {
            $_SESSION['form_errors_servicio_crear'] = $errors;
            $_SESSION['form_data_servicio_crear'] = $_POST;
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/crear");
            }
            exit;
        }
        
        $this->servicioModel->nombre = $nombre;
        $this->servicioModel->descripcion = $descripcion;
        $this->servicioModel->duracion_minutos = $duracion_minutos;
        $this->servicioModel->precio = $precio;
        $this->servicioModel->activo = $activo;

        if ($this->servicioModel->crear()) { 
            $_SESSION['mensaje_exito_global'] = "Servicio '" . htmlspecialchars($nombre) . "' creado exitosamente.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error al crear el servicio en la base de datos.";
            $_SESSION['form_data_servicio_crear'] = $_POST; 
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/crear");
            }
        }
        exit;
    }

    public function editarServicioForm($id_servicio_str) {
        $id_servicio = filter_var($id_servicio_str, FILTER_VALIDATE_INT);
        if (!$id_servicio) {
            $_SESSION['mensaje_error_global'] = "ID de servicio inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }

        if (!$this->servicioModel) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de servicios no disponible.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }

        $servicio = $this->servicioModel->obtenerPorId($id_servicio);
        if (!$servicio) {
            $_SESSION['mensaje_error_global'] = "Servicio no encontrado.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }

        $pageTitle = "Editar Servicio: " . htmlspecialchars($servicio['nombre']);
        $active_page = 'admin_servicios_edit'; 
        
        $form_data_from_session = $_SESSION['form_data_servicio_editar'][$id_servicio] ?? [];
        $form_data = array_merge($servicio, $form_data_from_session);
        $form_errors = $_SESSION['form_errors_servicio_editar'][$id_servicio] ?? [];
        unset($_SESSION['form_data_servicio_editar'][$id_servicio], $_SESSION['form_errors_servicio_editar'][$id_servicio]);

        $viewPath = dirname(__DIR__) . '/Views/admin/servicios/editar.php'; 
        extract(compact('pageTitle', 'active_page', 'viewPath', 'servicio', 'form_data', 'form_errors'));
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::editarServicioForm.");
            if(file_exists($viewPath)){
                include $viewPath; 
            } else {
                echo "Error: No se pudo encontrar la vista de editar servicio ('{$viewPath}') ni el layout principal.";
            }
        }
    }

    public function actualizarServicio($id_servicio_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }
        $id_servicio = filter_var($id_servicio_str, FILTER_VALIDATE_INT);
        if (!$id_servicio) {
            $_SESSION['mensaje_error_global'] = "ID de servicio inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }

        if (!$this->servicioModel) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de servicios no disponible.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/editar/" . $id_servicio);
            }
            exit;
        }
        
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $duracion_minutos = filter_input(INPUT_POST, 'duracion_minutos', FILTER_VALIDATE_INT);
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $activo = isset($_POST['activo']) ? 1 : 0;

        $errors = [];
        if (empty($nombre)) $errors['nombre'] = "El nombre del servicio es obligatorio.";
        if ($duracion_minutos === false || $duracion_minutos <= 0) $errors['duracion_minutos'] = "La duración debe ser un número entero positivo.";
        if ($precio === false || $precio < 0) $errors['precio'] = "El precio debe ser un número positivo (puede ser 0).";

        if (!empty($errors)) {
            $_SESSION['form_errors_servicio_editar'][$id_servicio] = $errors;
            $_SESSION['form_data_servicio_editar'][$id_servicio] = $_POST;
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/editar/" . $id_servicio);
            }
            exit;
        }
        
        $this->servicioModel->id = $id_servicio; 
        $this->servicioModel->nombre = $nombre;
        $this->servicioModel->descripcion = $descripcion;
        $this->servicioModel->duracion_minutos = $duracion_minutos;
        $this->servicioModel->precio = $precio;
        $this->servicioModel->activo = $activo;

        if ($this->servicioModel->actualizar()) { 
            $_SESSION['mensaje_exito_global'] = "Servicio '" . htmlspecialchars($nombre) . "' actualizado exitosamente.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error al actualizar el servicio o no hubo cambios.";
            $_SESSION['form_data_servicio_editar'][$id_servicio] = $_POST; 
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios/editar/" . $id_servicio);
            }
        }
        exit;
    }

    public function eliminarServicio($id_servicio_str) { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }
        $id_servicio = filter_var($id_servicio_str, FILTER_VALIDATE_INT);
        if (!$id_servicio) {
            $_SESSION['mensaje_error_global'] = "ID de servicio inválido.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit;
        }

        if (!$this->servicioModel) {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de servicios no disponible.";
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "admin/servicios");
            }
            exit; 
        }
        
        $servicioActual = $this->servicioModel->obtenerPorId($id_servicio);
        
        if ($this->servicioModel->eliminar($id_servicio)) { 
            $_SESSION['mensaje_exito_global'] = "Servicio '" . htmlspecialchars($servicioActual['nombre'] ?? 'ID:'.$id_servicio) . "' ha sido desactivado exitosamente.";
        } else {
            $_SESSION['mensaje_error_global'] = "Error al desactivar el servicio '" . htmlspecialchars($servicioActual['nombre'] ?? 'ID:'.$id_servicio) . "'.";
        }
        if (!headers_sent()) {
            header("Location: " . BASE_URL . "admin/servicios");
        }
        exit;
    }

    // --- Métodos para Gestión de Horarios de Empleados por Admin ---
    public function gestionarHorarioEmpleadoForm($empleado_id_str) {
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        if (!$empleado_id) {
            $_SESSION['mensaje_error_global'] = "ID de empleado inválido.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $empleado = $this->userModel->findById($empleado_id);
        if (!$empleado || $empleado['rol_id'] != ID_ROL_EMPLEADO) {
            $_SESSION['mensaje_error_global'] = "Empleado no encontrado o no tiene el rol adecuado.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $pageTitle = "Gestionar Horario de " . htmlspecialchars($empleado['nombre'] . " " . ($empleado['apellido'] ?? ''));
        $active_page = 'admin_users'; 

        $horariosRecurrentes = $this->horarioModel->obtenerHorariosRecurrentesPorEmpleado($empleado_id);
        
        $fechaInicioExcepciones = date('Y-m-d');
        $fechaFinExcepciones = date('Y-m-d', strtotime('+2 months -1 day'));
        $excepciones = $this->horarioModel->obtenerExcepcionesPorEmpleadoYFecha($empleado_id, $fechaInicioExcepciones, $fechaFinExcepciones);
        
        $diasSemanaMap = [
            0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
        ];

        $viewPath = dirname(__DIR__) . '/Views/admin/horarios/gestionar_horario_empleado.php';
        extract(compact('pageTitle', 'active_page', 'viewPath', 'empleado', 'horariosRecurrentes', 'excepciones', 'diasSemanaMap'));
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en AdminController::gestionarHorarioEmpleadoForm.");
            if(file_exists($viewPath)) include $viewPath;
            else echo "Error: No se pudo cargar la vista ni el layout.";
        }
    }

    public function guardarHorarioRecurrenteAdmin($empleado_id_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        if (!$empleado_id) {
            $_SESSION['mensaje_error_global'] = "ID de empleado inválido.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $dia_semana = filter_input(INPUT_POST, 'dia_semana', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 6]]);
        $hora_inicio = $_POST['hora_inicio_recurrente'] ?? null;
        $hora_fin = $_POST['hora_fin_recurrente'] ?? null;
        $fecha_desde = !empty($_POST['fecha_desde_recurrente']) ? $_POST['fecha_desde_recurrente'] : null;
        $fecha_hasta = !empty($_POST['fecha_hasta_recurrente']) ? $_POST['fecha_hasta_recurrente'] : null;

        if ($dia_semana === false || $dia_semana === null || !$this->validarFormatoHora($hora_inicio) || !$this->validarFormatoHora($hora_fin)) {
            $_SESSION['mensaje_error_global'] = "Datos de horario recurrente inválidos (día o formato de hora).";
        } elseif (strtotime($hora_inicio) >= strtotime($hora_fin)) {
            $_SESSION['mensaje_error_global'] = "La hora de inicio debe ser anterior a la hora de fin.";
        } elseif (($fecha_desde && !$this->validarFormatoFecha($fecha_desde)) || ($fecha_hasta && !$this->validarFormatoFecha($fecha_hasta))) {
            $_SESSION['mensaje_error_global'] = "Formato de fecha desde/hasta inválido.";
        } elseif ($fecha_desde && $fecha_hasta && strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            $_SESSION['mensaje_error_global'] = "La fecha 'desde' no puede ser posterior a la fecha 'hasta'.";
        } else {
            if ($this->horarioModel->crearHorarioRecurrente($empleado_id, $dia_semana, $hora_inicio, $hora_fin, $fecha_desde, $fecha_hasta)) {
                $_SESSION['mensaje_exito_global'] = "Horario recurrente guardado para el empleado.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al guardar el horario recurrente. Verifique que no exista un conflicto o revise los logs.";
                error_log("AdminController::guardarHorarioRecurrenteAdmin - Error al llamar a horarioModel->crearHorarioRecurrente para empleado ID {$empleado_id}.");
            }
        }
        if (!headers_sent()) header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/horario");
        exit;
    }

    public function eliminarHorarioRecurrenteAdmin($empleado_id_str, $id_horario_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        $id_horario = filter_var($id_horario_str, FILTER_VALIDATE_INT);

        if (!$empleado_id || !$id_horario) {
            $_SESSION['mensaje_error_global'] = "IDs inválidos para eliminar horario recurrente.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $horario_a_borrar = $this->horarioModel->obtenerHorarioRecurrentePorId($id_horario);
        if ($horario_a_borrar && $horario_a_borrar['empleado_id'] == $empleado_id) {
            if ($this->horarioModel->eliminarHorarioRecurrente($id_horario)) {
                $_SESSION['mensaje_exito_global'] = "Horario recurrente eliminado.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al eliminar el horario recurrente.";
                error_log("AdminController::eliminarHorarioRecurrenteAdmin - Error al llamar a horarioModel->eliminarHorarioRecurrente para ID {$id_horario}.");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "No se encontró el horario o no pertenece al empleado especificado.";
        }
        if (!headers_sent()) header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/horario");
        exit;
    }

    public function guardarExcepcionHorarioAdmin($empleado_id_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        if (!$empleado_id) {
            $_SESSION['mensaje_error_global'] = "ID de empleado inválido.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $fecha = $_POST['fecha_excepcion'] ?? null;
        $tipo_excepcion = $_POST['tipo_excepcion'] ?? null; 
        $hora_inicio_ex = !empty($_POST['hora_inicio_excepcion']) ? $_POST['hora_inicio_excepcion'] : null;
        $hora_fin_ex = !empty($_POST['hora_fin_excepcion']) ? $_POST['hora_fin_excepcion'] : null;
        $descripcion_ex = !empty($_POST['descripcion_excepcion']) ? trim(strip_tags($_POST['descripcion_excepcion'])) : null;

        if (!$this->validarFormatoFecha($fecha) || !in_array($tipo_excepcion, ['NO_DISPONIBLE', 'DISPONIBLE_EXTRA'])) {
            $_SESSION['mensaje_error_global'] = "Datos de excepción inválidos (fecha o tipo).";
        } elseif (($hora_inicio_ex && !$this->validarFormatoHora($hora_inicio_ex)) || ($hora_fin_ex && !$this->validarFormatoHora($hora_fin_ex))) {
            $_SESSION['mensaje_error_global'] = "Formato de hora para excepción inválido.";
        } elseif ($hora_inicio_ex && $hora_fin_ex && strtotime($hora_inicio_ex) >= strtotime($hora_fin_ex)) {
             $_SESSION['mensaje_error_global'] = "La hora de inicio de la excepción no puede ser posterior o igual a la hora de fin.";
        } elseif ($tipo_excepcion === 'DISPONIBLE_EXTRA' && (empty($hora_inicio_ex) || empty($hora_fin_ex))) {
            $_SESSION['mensaje_error_global'] = "Para 'Disponible (Extra)', las horas de inicio y fin son obligatorias.";
        } else {
            if ($this->horarioModel->crearExcepcion($empleado_id, $fecha, $tipo_excepcion, $hora_inicio_ex, $hora_fin_ex, $descripcion_ex)) {
                $_SESSION['mensaje_exito_global'] = "Excepción de horario guardada para el empleado.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al guardar la excepción de horario. Verifique los datos o revise los logs.";
                 error_log("AdminController::guardarExcepcionHorarioAdmin - Error al llamar a horarioModel->crearExcepcion para empleado ID {$empleado_id}.");
            }
        }
        if (!headers_sent()) header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/horario");
        exit;
    }

    public function eliminarExcepcionHorarioAdmin($empleado_id_str, $id_excepcion_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }
        $empleado_id = filter_var($empleado_id_str, FILTER_VALIDATE_INT);
        $id_excepcion = filter_var($id_excepcion_str, FILTER_VALIDATE_INT);

        if (!$empleado_id || !$id_excepcion) {
            $_SESSION['mensaje_error_global'] = "IDs inválidos para eliminar excepción.";
            if (!headers_sent()) header("Location: " . BASE_URL . "admin/usuarios");
            exit;
        }

        $excepcion_a_borrar = $this->horarioModel->obtenerExcepcionPorId($id_excepcion);
        if ($excepcion_a_borrar && $excepcion_a_borrar['empleado_id'] == $empleado_id) {
            if ($this->horarioModel->eliminarExcepcion($id_excepcion)) {
                $_SESSION['mensaje_exito_global'] = "Excepción de horario eliminada.";
            } else {
                $_SESSION['mensaje_error_global'] = "Error al eliminar la excepción de horario.";
                error_log("AdminController::eliminarExcepcionHorarioAdmin - Error al llamar a horarioModel->eliminarExcepcion para ID {$id_excepcion}.");
            }
        } else {
            $_SESSION['mensaje_error_global'] = "No se encontró la excepción o no pertenece al empleado especificado.";
        }
        if (!headers_sent()) header("Location: " . BASE_URL . "admin/empleado/" . $empleado_id . "/horario");
        exit;
    }

    private function validarFormatoHora($hora) {
        if ($hora === null) return true; 
        return preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $hora) || preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/", $hora);
    }

    private function validarFormatoFecha($fecha) {
        if ($fecha === null) return false; 
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    // --- Fin Métodos Gestión de Horarios ---

    private function verificarAccesoAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_ADMIN) {
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
            if(!headers_sent()){
                header('Location: ' . BASE_URL . 'login');
            }
            exit;
        }
        return true;
    }

    public function __destruct() {
        // La conexión se cierra en el destructor de Database.php si está implementado allí
    }
}
?>