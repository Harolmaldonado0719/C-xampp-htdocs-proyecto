<?php
// Podrías necesitar la conexión a la BD si el portal muestra datos dinámicos
// require_once __DIR__ . '/../Core/Database.php'; 
// require_once __DIR__ . '/../Models/User.php'; // Si necesitas datos del usuario

class PortalController {
    // private $db;
    // private $conn;
    // private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Descomentar si necesitas DB para el portal
        /*
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
            // $this->userModel = new User($this->conn);
        } catch (Exception $e) {
            error_log("PortalController: Error de conexión a BD - " . $e->getMessage());
            // Considera mostrar un mensaje de error amigable o redirigir
            die("Error crítico: No se pudo conectar al portal de servicios.");
        }
        */

        // Asegurarse de que el usuario esté logueado para acceder a cualquier método de este controlador
        // Esta verificación ya la hace el router, pero es una buena práctica tenerla aquí también por si se llama directamente.
        if (!isset($_SESSION['usuario_id'])) {
            // Guardar mensaje para mostrar en login
            $_SESSION['mensaje_error_global'] = "Acceso denegado. Debes iniciar sesión.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
    }

    public function index() {
        $pageTitle = "Portal de Servicios";
        $usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
        
        // Aquí puedes cargar datos específicos para el portal del cliente
        // Por ejemplo, lista de sus servicios contratados, etc.
        // $servicios = $this->algunaFuncionDelModeloParaObtenerServicios($_SESSION['usuario_id']);

        // Mensaje de error si se redirigió aquí por acceso denegado al dashboard
        $mensaje_error_portal = $_SESSION['mensaje_error_portal'] ?? null;
        unset($_SESSION['mensaje_error_portal']); // Limpiar el mensaje después de leerlo

        $viewPath = __DIR__ . '/../Views/portal/index.php'; // Crear esta vista
        
        $data_for_view = compact('pageTitle', 'usuario_nombre', 'mensaje_error_portal' /*, 'servicios' */);
        extract($data_for_view);
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    // public function __destruct() {
    //     if (isset($this->db) && $this->db) {
    //         $this->db->close();
    //     }
    // }
}
?>