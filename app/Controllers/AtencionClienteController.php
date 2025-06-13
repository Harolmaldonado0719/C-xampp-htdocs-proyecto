<?php

// filepath: app/Controllers/AtencionClienteController.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/SolicitudModel.php'; 
// Si vas a usar NotificacionController, asegúrate de incluirlo también
// require_once __DIR__ . '/../Controllers/NotificacionController.php'; // Ejemplo

class AtencionClienteController {

    private $db;
    private $pdoConn; // Cambiado para reflejar que es una conexión PDO
    private $solicitudModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->db = new Database(); 
            // CORRECCIÓN: Usar el método específico para obtener la conexión PDO
            $this->pdoConn = $this->db->getPdoConnection(); 

            if ($this->pdoConn) { // Verificar si la conexión PDO se obtuvo correctamente
                $this->solicitudModel = new SolicitudModel($this->pdoConn);
            } else {
                // La conexión PDO no se pudo establecer desde Database.php
                error_log("AtencionClienteController: No se pudo obtener la conexión PDO desde la clase Database.");
                $this->solicitudModel = null; 
            }
        } catch (Exception $e) {
            error_log("AtencionClienteController: Error de conexión a BD o instanciación de modelo - " . $e->getMessage());
            $this->solicitudModel = null;
            $this->pdoConn = null; // Asegurarse de que pdoConn también sea null en caso de error
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema (ACC-INIT). Por favor, intente más tarde.";
            // Considerar redirigir a una página de error si es apropiado
        }
    }

    /**
     * Muestra el formulario para que el cliente envíe una nueva solicitud (PQR).
     */
    public function nuevaSolicitudForm() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para enviar una solicitud.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pageTitle = "Nueva Solicitud de Atención";
        $active_page = 'nueva_solicitud'; // Para el layout
        $tipos_solicitud = ['Consulta', 'Queja', 'Reclamo', 'Sugerencia', 'Felicitación']; 
        $email_usuario = $_SESSION['user_email'] ?? ''; 
        
        // Recuperar datos del formulario y errores de la sesión si existen
        $form_data = $_SESSION['form_data_solicitud'] ?? [];
        $form_errors = $_SESSION['form_errors_solicitud'] ?? []; // Cambiado para manejar múltiples errores
        
        unset($_SESSION['form_data_solicitud']);
        unset($_SESSION['form_errors_solicitud']); // Limpiar errores después de usarlos


        // Renderizado de la vista
        $viewPath = dirname(__DIR__) . '/Views/cliente/atencion_cliente/nueva_solicitud_form.php'; // MODIFICADO
        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';
        
        ob_start();
        if (file_exists($viewPath)) {
            extract(compact('pageTitle', 'active_page', 'tipos_solicitud', 'email_usuario', 'form_data', 'form_errors'));
            include $viewPath;
        } else {
            echo "Error: Vista no encontrada para nueva solicitud.";
            error_log("Error: Vista no encontrada en AtencionClienteController: " . $viewPath);
        }
        $content_for_layout = ob_get_clean();
        
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content_for_layout; // Mostrar contenido sin layout si no se encuentra
            error_log("Error: Layout principal no encontrado en AtencionClienteController: " . $layoutPath);
        }
    }

    /**
     * Procesa el envío de una nueva solicitud (PQR) por parte del cliente.
     */
    public function guardarSolicitud() {
        error_log("AtencionClienteController::guardarSolicitud - Método alcanzado."); // Depuración
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            header('Location: ' . BASE_URL . 'atencion-cliente/nueva');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para enviar una solicitud.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $tipo_solicitud = $_POST['tipo_solicitud'] ?? null;
        $asunto = isset($_POST['asunto']) ? trim(strip_tags($_POST['asunto'])) : '';
        $descripcion = isset($_POST['descripcion']) ? trim(strip_tags($_POST['descripcion'])) : '';
        $email_contacto_form = isset($_POST['email_contacto']) ? trim($_POST['email_contacto']) : '';
        
        $email_contacto = filter_var($email_contacto_form, FILTER_VALIDATE_EMAIL) ? $email_contacto_form : ($_SESSION['user_email'] ?? '');
        $usuario_id = $_SESSION['user_id'];
        $errors = [];

        if (empty($tipo_solicitud)) $errors['tipo_solicitud'] = "El tipo de solicitud es obligatorio.";
        if (empty($asunto)) $errors['asunto'] = "El asunto es obligatorio.";
        if (empty($descripcion)) $errors['descripcion'] = "La descripción es obligatoria.";
        if (!filter_var($email_contacto, FILTER_VALIDATE_EMAIL)) $errors['email_contacto'] = "El email de contacto no es válido o está vacío.";

        if (!empty($errors)) {
            $_SESSION['form_errors_solicitud'] = $errors;
            $_SESSION['form_data_solicitud'] = $_POST;
            error_log("AtencionClienteController::guardarSolicitud - Errores de validación: " . json_encode($errors));
            header('Location: ' . BASE_URL . 'atencion-cliente/nueva');
            exit;
        }

        if (!$this->solicitudModel || !$this->pdoConn) {
            $_SESSION['mensaje_error_global'] = "Error del sistema al procesar la solicitud (modelo o conexión no disponible). Intenta más tarde.";
            error_log("AtencionClienteController::guardarSolicitud - solicitudModel o pdoConn no disponibles. SolicitudModel: " . ($this->solicitudModel ? 'OK' : 'NULL') . ", pdoConn: " . ($this->pdoConn ? 'OK' : 'NULL'));
            $_SESSION['form_data_solicitud'] = $_POST;
            header('Location: ' . BASE_URL . 'atencion-cliente/nueva');
            exit;
        }
        
        $datosSolicitud = [
            'usuario_id' => $usuario_id,
            'tipo_solicitud' => $tipo_solicitud,
            'asunto' => $asunto,
            'descripcion' => $descripcion,
            'email_contacto' => $email_contacto,
            'estado' => 'Abierta' // Estado inicial por defecto
        ];
        error_log("AtencionClienteController::guardarSolicitud - Datos para crear: " . json_encode($datosSolicitud));

        $solicitudId = $this->solicitudModel->crear($datosSolicitud);

        if ($solicitudId) {
            $_SESSION['mensaje_exito_global'] = "Tu solicitud (PQR-" . $solicitudId . ") ha sido enviada con éxito.";
            unset($_SESSION['form_data_solicitud']);
            unset($_SESSION['form_errors_solicitud']);
            error_log("AtencionClienteController::guardarSolicitud - Solicitud creada con ID: " . $solicitudId);
            header('Location: ' . BASE_URL . 'mis-solicitudes'); 
            exit;
        } else {
            $_SESSION['mensaje_error_global'] = "Hubo un error al enviar tu solicitud. Por favor, inténtalo de nuevo.";
            error_log("AtencionClienteController::guardarSolicitud - solicitudModel->crear devolvió false. Datos: " . json_encode($datosSolicitud));
            $_SESSION['form_data_solicitud'] = $_POST; // Mantener los datos para rellenar el formulario
            header('Location: ' . BASE_URL . 'atencion-cliente/nueva');
            exit;
        }
    }
    
    /**
     * Muestra la lista de solicitudes enviadas por el cliente.
     */
    public function misSolicitudes() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para ver tus solicitudes.";
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $usuario_id = $_SESSION['user_id'];
        $pageTitle = "Mis Solicitudes de Atención";
        $active_page = 'mis_solicitudes'; 
        
        $solicitudes = [];
        if ($this->solicitudModel && $this->pdoConn) { // Verificar pdoConn también
            $solicitudes = $this->solicitudModel->obtenerPorUsuarioId($usuario_id);
            if ($solicitudes === false) { 
                $_SESSION['mensaje_error_global'] = "Ocurrió un error al cargar tus solicitudes.";
                error_log("AtencionClienteController::misSolicitudes - solicitudModel->obtenerPorUsuarioId devolvió false para usuario ID: $usuario_id");
                $solicitudes = []; 
            }
        } else {
             $_SESSION['mensaje_error_global'] = "No se pudieron cargar tus solicitudes en este momento (sistema no disponible).";
             error_log("AtencionClienteController::misSolicitudes - solicitudModel o pdoConn no disponibles.");
        }
        
        $viewPath = dirname(__DIR__) . '/Views/cliente/atencion_cliente/mis_solicitudes.php'; // MODIFICADO
        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';

        ob_start();
        if (file_exists($viewPath)) {
            extract(compact('pageTitle', 'active_page', 'solicitudes'));
            include $viewPath;
        } else {
            echo "Error: Vista no encontrada para mis solicitudes.";
            error_log("Error: Vista no encontrada en AtencionClienteController: " . $viewPath);
        }
        $content_for_layout = ob_get_clean();

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content_for_layout; 
            error_log("Error: Layout principal no encontrado en AtencionClienteController: " . $layoutPath);
        }
    }

    public function __destruct() {
        $this->pdoConn = null; // PDO cierra la conexión cuando el objeto es null o destruido
        $this->db = null; 
    }
}
?>