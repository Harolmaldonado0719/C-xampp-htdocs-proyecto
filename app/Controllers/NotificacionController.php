<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/NotificacionModel.php';


class NotificacionController {

    private $db;
    private $pdoConn; 
    private $notificacionModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->db = new Database();
            $this->pdoConn = $this->db->getPdoConnection(); 
            if (!$this->pdoConn) {
                
                $this->notificacionModel = null;
                throw new Exception("NotificacionController: No se pudo obtener la conexión PDO.");
            }
            
            $this->notificacionModel = new NotificacionModel($this->pdoConn);
        } catch (Exception $e) {
            error_log("NotificacionController Constructor: " . $e->getMessage());
            $this->notificacionModel = null; 
            
        }
    }

    public function listarNotificacionesCliente() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tus notificaciones.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $usuario_id = $_SESSION['user_id'];
        $pageTitle = "Mis Notificaciones";
        
        $notificaciones = [];
        if ($this->notificacionModel) { // Verificar si el modelo se instanció correctamente
            $notificaciones = $this->notificacionModel->obtenerPorUsuarioId($usuario_id);
        } else {
             $_SESSION['mensaje_error_notificaciones'] = "No se pudieron cargar las notificaciones en este momento (sistema no disponible).";
             error_log("NotificacionController::listarNotificacionesCliente - notificacionModel no está inicializado.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/cliente/notificaciones/lista_notificaciones.php'; // MODIFICADO
        extract(compact('pageTitle', 'viewPath', 'notificaciones'));
        // Asegurarse que main_layout.php exista y sea accesible
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            error_log("Error crítico: main_layout.php no encontrado en NotificacionController::listarNotificacionesCliente.");
            echo "Error: No se pudo cargar la estructura de la página.";
        }
    }

    public function marcarComoLeida($notificacion_id_str) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403); 
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
        
        $notificacion_id = filter_var($notificacion_id_str, FILTER_VALIDATE_INT);
        if (!$notificacion_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de notificación inválido.']);
            exit;
        }

        $usuario_id = $_SESSION['user_id'];
        $marcada = false;
        if ($this->notificacionModel) { // Verificar si el modelo se instanció
            // Se necesita el método obtenerPorId en NotificacionModel para verificar pertenencia
            // y luego marcarComoLeida que podría tomar solo el ID si la lógica de pertenencia está ahí
            // o el ID y el usuario_id si la lógica de pertenencia está en marcarComoLeida.
            // Asumiendo que marcarComoLeida en el modelo ya maneja la pertenencia o se verifica aquí:
            $notificacion = $this->notificacionModel->obtenerPorId($notificacion_id); // Asume que este método existe
            if ($notificacion && isset($notificacion['usuario_id_destino']) && $notificacion['usuario_id_destino'] == $usuario_id) {
                 // El modelo NotificacionModel::marcarComoLeida debería tomar el id de la notificación y el id del usuario
                 // para asegurar que solo el dueño pueda marcarla.
                $marcada = $this->notificacionModel->marcarComoLeida($notificacion_id, $usuario_id);
            } else if ($notificacion && (!isset($notificacion['usuario_id_destino']) || $notificacion['usuario_id_destino'] != $usuario_id)) {
                error_log("Intento de marcar notificación ajena: user_id=" . $usuario_id . ", notif_id=" . $notificacion_id);
            }
        } else {
            error_log("NotificacionController::marcarComoLeida - notificacionModel no está inicializado.");
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { 
            if ($marcada) {
                echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
            } else {
                http_response_code(500); 
                echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación o no te pertenece.']);
            }
        } else { 
            if ($marcada) {
                $_SESSION['mensaje_exito_global'] = 'Notificación marcada como leída.';
            } else {
                $_SESSION['mensaje_error_global'] = 'No se pudo marcar la notificación o no te pertenece.';
            }
            header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'notificaciones')); // Ajustar la URL de fallback
        }
        exit;
    }
    
    public function __destruct() {
        // El objeto Database se encarga de cerrar sus conexiones
        // if (isset($this->db) && $this->db instanceof Database) {
        // $this->db->close(); // Si Database tuviera un método close general
        // }
        $this->pdoConn = null; // Liberar la conexión PDO
        $this->db = null;
    }
}
?>