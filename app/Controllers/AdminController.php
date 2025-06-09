<?php
require_once __DIR__ . '/../Core/Database.php'; 

// CAMBIO: Nombre de la clase
class AdminController { 
    private $db;
    private $conn;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Proteger el controlador: solo usuarios logueados pueden acceder
        // NOTA: Usas $_SESSION['user_id'] aquí. Asegúrate que AuthController lo establece así.
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder al dashboard.";
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // Verificar el rol
        // NOTA: Usas $_SESSION['user_rol_id'] aquí.
        if (!isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_ADMIN) { // Asumiendo ID_ROL_ADMIN está definido
            $_SESSION['mensaje_error_global'] = "No tienes permiso para acceder a esta sección.";
            if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_EMPLEADO) { // Asumiendo ID_ROL_EMPLEADO está definido
                header("Location: " . BASE_URL . "empleado/dashboard");
            } elseif (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == ID_ROL_CLIENTE) { // Asumiendo ID_ROL_CLIENTE está definido
                header("Location: " . BASE_URL . "portal_servicios");
            } else {
                header("Location: " . BASE_URL . "login");
            }
            exit;
        }

        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            error_log("AdminController: Error de conexión a BD - " . $e->getMessage());
            $pageTitle = "Error de Conexión";
            $mensaje_error = "No se pudo conectar al sistema. Por favor, inténtalo más tarde.";
            // CAMBIO: Ruta a vista de error, asumiendo que está en Views/errors/
            $viewPath = dirname(__DIR__) . '/Views/errors/service_unavailable.php'; 
            extract(compact('pageTitle', 'mensaje_error', 'viewPath'));
            // CAMBIO: Ruta al layout
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
            exit;
        }
    }

    public function index() {
        $pageTitle = "Dashboard Administrador"; // Título más específico
        $usuarios = []; 

        $sql = "SELECT u.id, u.nombre, u.email, u.fecha_registro, u.fotografia, r.nombre_rol 
                FROM usuarios u 
                LEFT JOIN roles r ON u.rol_id = r.id 
                ORDER BY u.fecha_registro DESC";
        $stmt = mysqli_prepare($this->conn, $sql);

        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                if (!empty($row['fotografia'])) {
                    $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                    $row['fotografia_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($row['fotografia']);
                } else {
                    $row['fotografia_url'] = BASE_URL . 'img/default-avatar.png';
                }
                $usuarios[] = $row;
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error al obtener usuarios para Admin Dashboard: " . mysqli_error($this->conn));
            $_SESSION['mensaje_error_dashboard'] = "No se pudieron cargar los datos de los usuarios.";
        }
        
        // NOTA: Usas $_SESSION['user_nombre'] aquí.
        // La vista app/Views/admin/index.php espera $usuario_nombre
        $usuario_nombre = $_SESSION['user_nombre'] ?? 'Administrador'; 

        // CAMBIO: Ruta de la vista a la nueva carpeta 'admin'
        $viewPath = dirname(__DIR__) . '/Views/admin/index.php'; 

        // Pasar las variables a la vista.
        // La vista admin/index.php espera $pageTitle, $usuarios, $usuario_nombre
        extract(compact('pageTitle', 'usuarios', 'usuario_nombre', 'viewPath')); 

        // CAMBIO: Ruta al layout
        include dirname(__DIR__) . '/Views/layouts/main_layout.php'; 
    }

    public function __destruct() {
        if (isset($this->db) && $this->db) {
            $this->db->close();
        }
    }
}
?>