<?php

// Requerir los modelos necesarios
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/NotificacionModel.php';

class ClienteController {
    private $db; 
    private $pdoConn; 
    private $notificacionModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!defined('ID_ROL_CLIENTE')) {
            error_log("ClienteController: ID_ROL_CLIENTE no está definido. Verifica config.php.");
            $_SESSION['mensaje_error_global'] = "Error de configuración del sistema (ID Rol Cliente).";
            if (defined('BASE_URL') && !headers_sent()) {
                header("Location: " . BASE_URL . "login");
            }
            exit;
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) { 
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado al portal de cliente.";
            if (defined('BASE_URL') && !headers_sent()) {
                header("Location: " . BASE_URL . "login");
            }
            exit;
        }
        
        try {
            $this->db = new Database();
            $this->pdoConn = $this->db->getPdoConnection(); 
            
            if (!$this->pdoConn) {
                throw new Exception("ClienteController: No se pudo obtener la conexión PDO.");
            }
            $this->notificacionModel = new NotificacionModel($this->pdoConn);

        } catch (Exception $e) {
            error_log("ClienteController Constructor: " . $e->getMessage());
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema (ClientInit). Contacte al administrador.";
            $this->notificacionModel = null; // Asegurarse que el modelo es null si falla la inicialización
            // Considerar si redirigir o mostrar un error más específico si las notificaciones son cruciales.
            // Por ahora, el indicador simplemente no aparecerá o mostrará 0 si el modelo falla.
        }
    }

    public function index() { 
        $pageTitle = "Portal de Servicios";
        $usuario_nombre = $_SESSION['user_nombre'] ?? 'Cliente'; 
        $active_page = 'portal_servicios'; 

        $contador_notificaciones_no_leidas = 0;
        if ($this->notificacionModel && isset($_SESSION['user_id'])) {
            if (method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
                $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
            } else {
                error_log("ClienteController::index - Método contarNoLeidasPorUsuarioId no existe en NotificacionModel.");
            }
        } elseif (!$this->notificacionModel) {
            error_log("ClienteController::index - notificacionModel no está inicializado.");
        }


        $viewPath = dirname(__DIR__) . '/Views/cliente/index.php'; 

        // Pasar el contador a la vista para que esté disponible en el layout
        extract(compact('pageTitle', 'usuario_nombre', 'viewPath', 'active_page', 'contador_notificaciones_no_leidas'));

        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            error_log("Error crítico: No se encontró el archivo de layout principal: " . $layoutPath);
            die("Error crítico: No se pudo cargar la estructura de la página. Contacte al administrador.");
        }
    }
    
    public function __destruct() {
        $this->db = null;
        $this->pdoConn = null;
    }
}
?>