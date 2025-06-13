<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/User.php'; 
require_once __DIR__ . '/../Core/Validator.php'; 

class UserController {
    private $db;
    private $mysqliConn; 
    private $userModel; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $this->db = new Database();
            $this->mysqliConn = $this->db->getMysqliConnection(); 
            if (!$this->mysqliConn) {
                throw new Exception("UserController: No se pudo obtener la conexión MySQLi.");
            }
            $this->userModel = new User($this->mysqliConn); 
        } catch (Exception $e) {
            error_log("UserController Constructor: " . $e->getMessage());
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema (UserInit). Por favor, intente más tarde.";
            if (defined('BASE_URL') && !headers_sent()) {
                 // header("Location: " . BASE_URL . "error-page"); 
            }
            die("Error crítico del sistema. Por favor, intente más tarde. (User)");
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta sección.";
            if (defined('BASE_URL') && !headers_sent()) {
                header("Location: " . BASE_URL . "login");
            }
            exit;
        }
    }

    public function showProfile() {
        $pageTitle = "Mi Perfil";
        $usuario_id = $_SESSION['user_id'];
        $userData = null;

        $rawUserData = $this->userModel->findById($usuario_id); 

        if ($rawUserData) {
            $userData = $rawUserData; 
            if (isset($userData['rol_id'])) {
                // Asegurarse que las constantes de rol estén definidas
                $rolDefinido = false;
                if (defined('ID_ROL_ADMIN') && $userData['rol_id'] == ID_ROL_ADMIN) { $userData['nombre_rol'] = 'Administrador'; $rolDefinido = true; }
                elseif (defined('ID_ROL_EMPLEADO') && $userData['rol_id'] == ID_ROL_EMPLEADO) { $userData['nombre_rol'] = 'Empleado'; $rolDefinido = true; }
                elseif (defined('ID_ROL_CLIENTE') && $userData['rol_id'] == ID_ROL_CLIENTE) { $userData['nombre_rol'] = 'Cliente'; $rolDefinido = true; }
                
                if (!$rolDefinido) {
                    $userData['nombre_rol'] = 'Desconocido (ID: ' . $userData['rol_id'] . ')';
                }

            } else {
                $userData['nombre_rol'] = 'No asignado';
            }

            if (!empty($userData['fotografia'])) {
                $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                $userData['fotografia_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($userData['fotografia']);
            } else {
                $userData['fotografia_url'] = BASE_URL . 'img/default-avatar.png';
            }
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los datos de tu perfil.";
            $this->redirectToDashboardPorRol(); 
            return; 
        }

        $mensaje_exito_perfil = $_SESSION['mensaje_exito_perfil'] ?? null;
        $mensaje_error_perfil = $_SESSION['mensaje_error_perfil'] ?? null;
        unset($_SESSION['mensaje_exito_perfil'], $_SESSION['mensaje_error_perfil']);

        $viewPath = dirname(__DIR__) . '/Views/user/profile.php';
        extract(compact('pageTitle', 'userData', 'mensaje_exito_perfil', 'mensaje_error_perfil', 'viewPath'));
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en UserController::showProfile.");
            echo "Error: No se pudo cargar la estructura de la página.";
        }
    }

    public function editProfileForm() {
        $pageTitle = "Editar Mi Perfil";
        $usuario_id = $_SESSION['user_id'];
        $dbUserData = $this->userModel->findById($usuario_id);

        if (!$dbUserData) {
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar los datos para editar tu perfil.";
            header("Location: " . BASE_URL . "profile");
            exit;
        }

        $userDataForView = []; 
        if (!empty($dbUserData['fotografia'])) {
            $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
            $userDataForView['fotografia_url_actual'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($dbUserData['fotografia']);
        } else {
            $userDataForView['fotografia_url_actual'] = BASE_URL . 'img/default-avatar.png';
        }

        $nombre_val = $_SESSION['form_data_perfil']['nombre'] ?? $dbUserData['nombre'] ?? '';
        $apellido_val = $_SESSION['form_data_perfil']['apellido'] ?? $dbUserData['apellido'] ?? '';
        $email_val = $_SESSION['form_data_perfil']['email'] ?? $dbUserData['email'] ?? '';
        $telefono_val = $_SESSION['form_data_perfil']['telefono'] ?? $dbUserData['telefono'] ?? '';

        $mensaje_error_perfil_edit = $_SESSION['mensaje_error_perfil_edit'] ?? null;
        unset($_SESSION['form_data_perfil'], $_SESSION['mensaje_error_perfil_edit']);

        $viewPath = dirname(__DIR__) . '/Views/user/edit_profile.php';
        
        $userData = array_merge($dbUserData, $userDataForView); // Combinar para tener todos los datos del usuario y la URL de la foto
        
        extract(compact('pageTitle', 'userData', 'nombre_val', 'apellido_val', 'email_val', 'telefono_val', 'mensaje_error_perfil_edit', 'viewPath'));
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en UserController::editProfileForm.");
            echo "Error: No se pudo cargar la estructura de la página.";
        }
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        $usuario_id = $_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? ''); 
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? ''); 
        $current_password = $_POST['current_password'] ?? '';
        $new_password_texto_plano = $_POST['new_password'] ?? ''; // Contraseña nueva en texto plano
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        $errors = [];
        $_SESSION['form_data_perfil'] = [ 
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono
        ];

        if (empty($nombre)) {
            $errors[] = "El nombre es obligatorio.";
        }
        if (empty($apellido)) {
            $errors[] = "El apellido es obligatorio.";
        }
        if (empty($email)) {
            $errors[] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Formato de correo electrónico inválido.";
        } else {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $usuario_id) {
                $errors[] = "El correo electrónico ya está registrado por otro usuario.";
            }
        }
        
        if (!empty($telefono) && !preg_match('/^[0-9\s\-\+\(\)]{7,20}$/', $telefono)) { 
            $errors[] = "El formato del teléfono no es válido.";
        }

        $currentUserData = $this->userModel->findById($usuario_id);
        if (!$currentUserData) {
            $_SESSION['mensaje_error_perfil_edit'] = "Error crítico: No se pudo verificar el usuario actual.";
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }
        
        $password_para_db = null; // Será la contraseña en texto plano si se cambia, o null si no.
        $password_changed = false;

        if (!empty($new_password_texto_plano)) {
            if (empty($current_password)) {
                $errors[] = "Debes ingresar tu contraseña actual para establecer una nueva.";
            } 
            elseif (!isset($currentUserData['password_hash']) || !password_verify($current_password, $currentUserData['password_hash'])) {
                $errors[] = "La contraseña actual es incorrecta.";
            } 
            elseif (strlen($new_password_texto_plano) < 6) {
                $errors[] = "La nueva contraseña debe tener al menos 6 caracteres.";
            } 
            elseif ($new_password_texto_plano !== $confirm_new_password) {
                $errors[] = "Las nuevas contraseñas no coinciden.";
            } else {
                // NO hashear aquí. El modelo User::update() se encargará.
                $password_para_db = $new_password_texto_plano; 
                $password_changed = true;
                error_log("UserController::updateProfile - Se pasará la nueva contraseña en texto plano al modelo para el ID: $usuario_id");
            }
        }

        $nombre_archivo_final_para_db = $currentUserData['fotografia']; 

        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/';
            if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
                $errors[] = "Error al crear el directorio de subidas: " . $uploadDir;
            }

            if (is_dir($uploadDir)) { 
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['fotografia']['tmp_name']);
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Tipo de archivo de fotografía no permitido. Solo JPG, PNG, GIF.";
                } elseif ($_FILES['fotografia']['size'] > $maxSize) {
                    $errors[] = "La fotografía es demasiado grande (máximo 2MB).";
                } else {
                    $extension_archivo = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
                    $nombre_archivo_unico = uniqid('user_' . $usuario_id . '_', true) . '.' . $extension_archivo;
                    $ruta_destino_completa = $uploadDir . $nombre_archivo_unico;

                    if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_destino_completa)) {
                        if ($nombre_archivo_final_para_db && 
                            $nombre_archivo_final_para_db !== 'default-avatar.png' && 
                            $nombre_archivo_final_para_db !== $nombre_archivo_unico && 
                            file_exists($uploadDir . $nombre_archivo_final_para_db)) {
                            @unlink($uploadDir . $nombre_archivo_final_para_db);
                        }
                        $nombre_archivo_final_para_db = $nombre_archivo_unico;
                    } else {
                        $errors[] = "Error al mover la nueva fotografía.";
                    }
                }
            }
        } elseif (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors[] = "Error al subir la fotografía. Código: " . $_FILES['fotografia']['error'];
        }

        if (!empty($errors)) {
            $_SESSION['mensaje_error_perfil_edit'] = implode("<br>", $errors);
            header("Location: " . BASE_URL . "profile/edit");
            exit;
        }

        // Pasar $password_para_db (que es la contraseña nueva en texto plano, o null)
        $updateResult = $this->userModel->update(
            $usuario_id,
            $nombre,
            $apellido,
            $email,
            $currentUserData['rol_id'], 
            !empty($telefono) ? $telefono : null,
            $nombre_archivo_final_para_db,
            $currentUserData['activo'],   
            $password_para_db // Pasar la contraseña en texto plano (o null si no se cambia)
        );

        if ($updateResult) {
            unset($_SESSION['form_data_perfil']); 
            $_SESSION['mensaje_exito_perfil'] = "Perfil actualizado correctamente.";
            if ($password_changed) {
                 $_SESSION['mensaje_exito_perfil'] .= " Tu contraseña ha sido cambiada.";
            }


            $_SESSION['user_nombre'] = $nombre; 
            if ($nombre_archivo_final_para_db !== $currentUserData['fotografia']) { // Actualizar foto en sesión solo si cambió
                $new_photo_url_session = BASE_URL . 'img/default-avatar.png';
                if ($nombre_archivo_final_para_db && $nombre_archivo_final_para_db !== 'default-avatar.png') {
                    $uploadPathSession = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                    $new_photo_url_session = BASE_URL . rtrim($uploadPathSession, '/') . '/' . htmlspecialchars($nombre_archivo_final_para_db);
                }
                $_SESSION['user_fotografia_url'] = $new_photo_url_session; 
            }
            header("Location: " . BASE_URL . "profile");
            exit;
        } else {
            $_SESSION['mensaje_error_perfil_edit'] = "Error al actualizar el perfil en la base de datos o no hubo cambios detectados.";
            error_log("UserController::updateProfile - Error o no cambios al llamar a userModel->update() para el ID: $usuario_id");
            header("Location: " . BASE_URL . "profile/edit"); 
            exit;
        }
    }

    private function redirectToDashboardPorRol() {
        $rol_id_sesion = $_SESSION['user_rol_id'] ?? null;

        if ($rol_id_sesion === null && isset($_SESSION['user_id'])) {
            // Intentar recargar el rol desde la BD si no está en sesión
            $user = $this->userModel->findById($_SESSION['user_id']);
            if ($user && isset($user['rol_id'])) {
                $rol_id_sesion = $user['rol_id'];
                $_SESSION['user_rol_id'] = $rol_id_sesion; 
            } else {
                error_log("redirectToDashboardPorRol: No se pudo obtener rol_id para usuario_id: " . ($_SESSION['user_id'] ?? 'No definido'));
            }
        }

        if ($rol_id_sesion !== null) {
            if (defined('ID_ROL_ADMIN') && $rol_id_sesion == ID_ROL_ADMIN) {
                header("Location: " . BASE_URL . "dashboard"); exit;
            } elseif (defined('ID_ROL_EMPLEADO') && $rol_id_sesion == ID_ROL_EMPLEADO) {
                header("Location: " . BASE_URL . "empleado/dashboard"); exit;
            } elseif (defined('ID_ROL_CLIENTE') && $rol_id_sesion == ID_ROL_CLIENTE) {
                header("Location: " . BASE_URL . "portal_servicios"); exit;
            } else {
                error_log("redirectToDashboardPorRol: rol_id_sesion (" . $rol_id_sesion . ") no coincide con roles definidos o constantes no disponibles.");
            }
        }
        
        // Fallback si no se puede redirigir por rol
        $_SESSION['mensaje_error_global'] = "No se pudo determinar tu panel de control. Se cerrará la sesión.";
        // Limpiar sesión de forma segura
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        session_unset();
        session_destroy(); 
        
        header("Location: " . BASE_URL . "login");
        exit;
    }

    public function __destruct() {
        // El objeto Database se encarga de cerrar sus conexiones
    }
}
?>