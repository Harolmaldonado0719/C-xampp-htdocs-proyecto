
<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Core/Database.php';

class AuthController {
    private $userModel;
    private $db;
    private $conn;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->userModel = new User($this->conn);
    }

    public function showLoginForm($mensaje_error = null, $mensaje_exito = null, $datos_previos = []) {
        $pageTitle = "Iniciar Sesión";
        
        // Priorizar mensajes pasados como parámetros
        $mensaje_error_display = $mensaje_error;
        $mensaje_exito_display = $mensaje_exito;

        // Si no hay mensajes como parámetros, usar los de sesión (y luego limpiarlos)
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
        unset($_SESSION['form_data']); // Limpiar datos de formulario de sesión

        // Si el usuario ya está logueado, redirigir a su dashboard correspondiente
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
                    // Rol desconocido, pero logueado. Mejor cerrar sesión.
                    $this->logout("Tu rol de usuario no está configurado. Sesión terminada.");
                    exit;
                }
            } else {
                 // user_id existe pero rol_id no. Sesión inconsistente.
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
        $password = $_POST['password'] ?? '';
        $email_val = $email; 

        if (empty($email) || empty($password)) {
            $this->showLoginForm("Todos los campos son obligatorios.", null, ['email_val' => $email_val]);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); 

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol_id'] = $user['rol_id'] ?? null; 
            $_SESSION['user_rol_nombre'] = $user['nombre_rol'] ?? null;
            $_SESSION['user_fotografia'] = $user['fotografia'] ?? null;

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
                    session_unset(); session_destroy(); if (session_status() == PHP_SESSION_NONE) { session_start(); }
                    $this->showLoginForm("Tu rol de usuario no está configurado para el acceso. Contacta al administrador.", null, ['email_val' => $email_val]);
                    exit; 
                }
            } else {
                session_unset(); session_destroy(); if (session_status() == PHP_SESSION_NONE) { session_start(); }
                $this->showLoginForm("Error de configuración de rol. No se pudo determinar tu rol.", null, ['email_val' => $email_val]);
                exit; 
            }
        } else {
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
            header("Location: " . BASE_URL . "dashboard"); // O a donde corresponda
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
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $datos_previos = ['nombre_val' => $nombre, 'email_val' => $email];
        $_SESSION['form_data'] = $datos_previos; // Guardar para repoblar

        if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
            $this->showRegisterForm("Todos los campos son obligatorios.", $datos_previos);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showRegisterForm("El formato del correo electrónico no es válido.", $datos_previos);
            return;
        }
        if (strlen($password) < 6) { // Ejemplo de validación de contraseña
            $this->showRegisterForm("La contraseña debe tener al menos 6 caracteres.", $datos_previos);
            return;
        }
        if ($password !== $confirm_password) {
            $this->showRegisterForm("Las contraseñas no coinciden.", $datos_previos);
            return;
        }
        if ($this->userModel->findByEmail($email)) {
            $this->showRegisterForm("Este correo electrónico ya está registrado.", $datos_previos);
            return;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol_id = ID_ROL_CLIENTE; // Rol por defecto para nuevos registros

        $userId = $this->userModel->create($nombre, $email, $password_hash, $rol_id);

        if ($userId) {
            unset($_SESSION['form_data']); // Limpiar datos de formulario en éxito
            // Opcional: Iniciar sesión automáticamente después del registro
            // $_SESSION['user_id'] = $userId;
            // $_SESSION['user_nombre'] = $nombre;
            // $_SESSION['user_rol_id'] = $rol_id;
            // $_SESSION['user_rol_nombre'] = 'Cliente'; // O buscarlo en la BD
            // header("Location: " . BASE_URL . "portal_servicios");
            // exit;
            
            // O redirigir a login con mensaje de éxito
            $this->showLoginForm(null, "¡Registro exitoso! Ahora puedes iniciar sesión.", ['email_val' => $email]);
        } else {
            $this->showRegisterForm("Hubo un error durante el registro. Por favor, inténtalo de nuevo.", $datos_previos);
        }
    }

    public function logout($mensaje_logout = "Has cerrado sesión correctamente.") {
        session_unset();
        session_destroy();
        // Iniciar una nueva sesión para poder pasar el mensaje
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mensaje_exito_global'] = $mensaje_logout;
        // CAMBIO AQUÍ: Redirigir a la página principal
        header("Location: " . BASE_URL);
        exit;
    }

    public function __destruct() {
        if (isset($this->db)) {
            $this->db->close();
        }
    }
}
?>