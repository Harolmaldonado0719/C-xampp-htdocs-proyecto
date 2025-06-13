<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Database.php';

class AuthController {
    private $userModel;
    private $db;
    private $pdoConn; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->db = new Database();
            $this->pdoConn = $this->db->getPdoConnection(); 
            if (!$this->pdoConn) {
                throw new Exception("AuthController: No se pudo obtener la conexión PDO.");
            }
            $this->userModel = new User($this->pdoConn); 
        } catch (Exception $e) {
            error_log("AuthController Constructor: " . $e->getMessage());
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema (AuthInit). Por favor, intente más tarde.";
            die("Error crítico del sistema. Por favor, intente más tarde. (Auth)");
        }
    }

    public function showLoginForm($mensaje_error = null, $mensaje_exito = null, $datos_previos = []) {
        $pageTitle = "Iniciar Sesión";
        
        $mensaje_error_display = $mensaje_error;
        $mensaje_exito_display = $mensaje_exito;

        if ($mensaje_error_display === null && isset($_SESSION['mensaje_error_global'])) {
            $mensaje_error_display = $_SESSION['mensaje_error_global'];
            unset($_SESSION['mensaje_error_global']);
        }
        if ($mensaje_exito_display === null && isset($_SESSION['mensaje_exito_global'])) {
            $mensaje_exito_display = $_SESSION['mensaje_exito_global'];
            unset($_SESSION['mensaje_exito_global']);
        }
        
        $email_val = $datos_previos['email_val'] ?? '';
        if (empty($email_val) && isset($_SESSION['form_data']['email_val'])) {
            $email_val = $_SESSION['form_data']['email_val'];
        }
        unset($_SESSION['form_data']); 

        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_rol_id'])) {
                if ($_SESSION['user_rol_id'] == ID_ROL_ADMIN) {
                    header("Location: " . BASE_URL . "dashboard");
                    exit;
                } elseif ($_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                    header("Location: " . BASE_URL . "empleado/dashboard");
                    exit;
                } elseif ($_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                    header("Location: " . BASE_URL . "portal_servicios");
                    exit;
                } else {
                    $this->logout("Tu rol de usuario no está configurado. Sesión terminada.");
                    exit;
                }
            } else {
                $this->logout("Error de configuración de rol. Sesión terminada.");
                exit;
            }
        }

        $viewPath = __DIR__ . '/../Views/auth/login.php';
        extract(compact('pageTitle', 'mensaje_error_display', 'mensaje_exito_display', 'email_val', 'viewPath'));
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Contraseña en texto plano ingresada por el usuario
        $email_val = $email; 

        if (empty($email) || empty($password)) {
            $this->showLoginForm("Todos los campos son obligatorios.", null, ['email_val' => $email_val]);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && isset($user['password_hash'])) {
            // DEBUGGING: Log para verificar datos antes de password_verify
            error_log("AuthController::handleLogin - Intentando login para Email: [{$email}]. Contraseña ingresada (longitud): " . strlen($password) . ". Hash de BD (primeros 10 chars): [" . substr($user['password_hash'], 0, 10) . "...]");

            if (password_verify($password, $user['password_hash'])) {
                error_log("AuthController::handleLogin - password_verify ÉXITO para email: [{$email}]");
                session_regenerate_id(true); 

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_rol_id'] = $user['rol_id'] ?? null; 
                $_SESSION['user_rol_nombre'] = $user['nombre_rol'] ?? null; 
                $_SESSION['user_fotografia'] = $user['fotografia'] ?? null;
                
                if (!empty($user['fotografia'])) {
                    $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                    $_SESSION['user_fotografia_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($user['fotografia']);
                } else {
                    $_SESSION['user_fotografia_url'] = BASE_URL . 'img/default-avatar.png';
                }

                if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] !== null) {
                    if ($_SESSION['user_rol_id'] == ID_ROL_ADMIN) {
                        header("Location: " . BASE_URL . "dashboard");
                        exit;
                    } elseif ($_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                        header("Location: " . BASE_URL . "empleado/dashboard");
                        exit;
                    } elseif ($_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                        header("Location: " . BASE_URL . "portal_servicios");
                        exit;
                    } else {
                        error_log("AuthController::handleLogin - Rol desconocido después de login exitoso. Rol ID: " . $_SESSION['user_rol_id']);
                        session_unset(); session_destroy(); if (session_status() == PHP_SESSION_NONE) { session_start(); }
                        $this->showLoginForm("Tu rol de usuario no está configurado para el acceso. Contacta al administrador.", null, ['email_val' => $email_val]);
                        exit; 
                    }
                } else {
                    error_log("AuthController::handleLogin - user_rol_id es NULL después de login exitoso para email: [{$email}]");
                    session_unset(); session_destroy(); if (session_status() == PHP_SESSION_NONE) { session_start(); }
                    $this->showLoginForm("Error de configuración de rol. No se pudo determinar tu rol.", null, ['email_val' => $email_val]);
                    exit; 
                }
            } else {
                error_log("AuthController::handleLogin - password_verify FALLÓ para email: [{$email}]. Contraseña ingresada no coincide con hash.");
                $this->showLoginForm("Correo electrónico o contraseña incorrectos.", null, ['email_val' => $email_val]);
            }
        } else {
            if (!$user) {
                error_log("AuthController::handleLogin - Usuario no encontrado para email: [{$email}]");
            } elseif (!isset($user['password_hash'])) {
                // Esto no debería ocurrir si el usuario existe y la columna password_hash está presente.
                error_log("AuthController::handleLogin - password_hash no encontrado en los datos del usuario para email: [{$email}] (Usuario sí encontrado).");
            }
            $this->showLoginForm("Correo electrónico o contraseña incorrectos.", null, ['email_val' => $email_val]);
        }
    }
    
    public function showRegisterForm($mensaje_error = null, $datos_previos = []) {
        $pageTitle = "Registrarse";

        $mensaje_error_display = $mensaje_error ?? $_SESSION['mensaje_error_global'] ?? null;
        unset($_SESSION['mensaje_error_global']);

        $nombre_val = $datos_previos['nombre_val'] ?? $_SESSION['form_data']['nombre_val'] ?? '';
        $email_val = $datos_previos['email_val'] ?? $_SESSION['form_data']['email_val'] ?? '';
        unset($_SESSION['form_data']);

        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_rol_id'])) {
                if ($_SESSION['user_rol_id'] == ID_ROL_ADMIN) {
                    header("Location: " . BASE_URL . "dashboard"); exit;
                } elseif ($_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) {
                    header("Location: " . BASE_URL . "empleado/dashboard"); exit;
                } elseif ($_SESSION['user_rol_id'] == ID_ROL_CLIENTE) {
                    header("Location: " . BASE_URL . "portal_servicios"); exit;
                }
            }
            // Si tiene user_id pero rol no coincide o no existe, redirigir a login para que se re-evalúe.
            header("Location: " . BASE_URL . "login"); 
            exit;
        }

        $viewPath = __DIR__ . '/../Views/auth/register.php';
        extract(compact('pageTitle', 'mensaje_error_display', 'nombre_val', 'email_val', 'viewPath'));
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "register");
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Contraseña en texto plano
        $confirm_password = $_POST['confirm_password'] ?? '';

        $datos_previos = ['nombre_val' => $nombre, 'email_val' => $email];
        $_SESSION['form_data'] = $datos_previos; 

        if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
            $this->showRegisterForm("Todos los campos son obligatorios.", $datos_previos);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showRegisterForm("El formato del correo electrónico no es válido.", $datos_previos);
            return;
        }
        if (strlen($password) < 6) { 
            $this->showRegisterForm("La contraseña debe tener al menos 6 caracteres.", $datos_previos);
            return;
        }
        if ($password !== $confirm_password) {
            $this->showRegisterForm("Las contraseñas no coinciden.", $datos_previos);
            return;
        }
        if ($this->userModel->emailExists($email)) { 
            $this->showRegisterForm("Este correo electrónico ya está registrado.", $datos_previos);
            return;
        }

        $rol_id = ID_ROL_CLIENTE; 

        // El modelo User::create ya hashea la contraseña. Se pasa $password en texto plano.
        $userId = $this->userModel->create($nombre, null, $email, $password, $rol_id, null, null, 1); 

        if ($userId) {
            unset($_SESSION['form_data']); 
            error_log("AuthController::handleRegister - Nuevo usuario registrado con ID: {$userId}, Email: [{$email}]");
            $this->showLoginForm(null, "¡Registro exitoso! Ahora puedes iniciar sesión.", ['email_val' => $email]);
        } else {
            error_log("AuthController::handleRegister - Falló el registro para Email: [{$email}]");
            $this->showRegisterForm("Hubo un error durante el registro. Por favor, inténtalo de nuevo.", $datos_previos);
        }
    }

    public function logout($mensaje_logout = "Has cerrado sesión correctamente.") {
        $user_id_logout = $_SESSION['user_id'] ?? 'N/A';
        error_log("AuthController::logout - Cerrando sesión para User ID: {$user_id_logout}");
        session_unset();
        session_destroy();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mensaje_exito_global'] = $mensaje_logout;
        header("Location: " . BASE_URL); 
        exit;
    }

    public function __destruct() {
        // El objeto Database se encarga de cerrar sus conexiones en su propio destructor
    }
}
?>