<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/CitaModel.php';
require_once __DIR__ . '/../Models/NotificacionModel.php'; 
require_once __DIR__ . '/../Models/HorarioModel.php'; 
require_once __DIR__ . '/../Core/Validator.php';      
require_once __DIR__ . '/../Models/EmpleadoServicioModel.php'; 
require_once __DIR__ . '/../Models/User.php'; 
require_once __DIR__ . '/../Models/ServicioModel.php';



class CitaController {

    private $db; 
    private $pdoConn;    // Conexión PDO
    private $mysqliConn; // Conexión MySQLi

    private $citaModel;
    private $notificacionModel; 
    private $usuarioModel = null;    
    private $servicioModel = null;   
    private $horarioModel = null; 
    private $empleadoServicioModel = null; 

    // ID de estado de cita para "Pendiente"
    const ID_ESTADO_CITA_PENDIENTE = 1; 
    // Define ID_ROL_CLIENTE y ID_ROL_ADMIN si no están definidos globalmente
    // Ejemplo: const ID_ROL_CLIENTE = 1; const ID_ROL_ADMIN = 3;


    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $this->db = new Database(); // Instancia de tu clase Database
            
            // Obtener ambas conexiones
            $this->pdoConn = $this->db->getPdoConnection(); 
            $this->mysqliConn = $this->db->getMysqliConnection();

            // --- Instanciación de Modelos ---

            // Modelos que usan PDO
            // Si CitaModel, NotificacionModel, etc., están en un namespace (ej. App\Models)
            // y has añadido la declaración 'use' correspondiente arriba,
            // la instanciación sigue siendo 'new NombreClase($this->pdoConn);'.
            // Si no tienen namespace, 'new NombreClase($this->pdoConn);' también es correcto.

            $this->citaModel = new CitaModel($this->pdoConn); 
            $this->notificacionModel = new NotificacionModel($this->pdoConn); 
            
            // HorarioModel (usa PDO)
            // Ajusta 'HorarioModel' a 'App\Models\HorarioModel' si usa namespace y tienes el 'use'
            if (class_exists('App\Models\HorarioModel')) { // Si usa namespace
                 $this->horarioModel = new \App\Models\HorarioModel($this->pdoConn);
            } elseif (class_exists('HorarioModel')) { // Si no usa namespace
                $this->horarioModel = new HorarioModel($this->pdoConn);
            } else {
                error_log("CitaController: Clase HorarioModel no encontrada.");
            }


            // EmpleadoServicioModel (usa PDO)
            // Ajusta 'EmpleadoServicioModel' a 'App\Models\EmpleadoServicioModel' si usa namespace y tienes el 'use'
            if (class_exists('EmpleadoServicioModel')) { 
                $this->empleadoServicioModel = new EmpleadoServicioModel($this->pdoConn);
            } else {
                error_log("CitaController: Clase EmpleadoServicioModel no encontrada.");
            }

            // User Model (usuarioModel): Este usa PDO (corregido)
            // User.php no parece tener namespace, así que 'User' es el nombre global de la clase.
            if (class_exists('User')) { 
                $this->usuarioModel = new User($this->pdoConn); // CORREGIDO: pasando conexión PDO
            } else {
                error_log("CitaController: La clase de modelo de usuario 'User' no fue encontrada.");
            }

            // ServicioModel (usa PDO)
            // Ajusta 'ServicioModel' a 'App\Models\ServicioModel' si usa namespace y tienes el 'use'
            if (class_exists('ServicioModel')) { 
                $this->servicioModel = new ServicioModel($this->pdoConn);
            } else {
                 error_log("CitaController: La clase 'ServicioModel' no fue encontrada.");
            }

        } catch (Exception $e) {
            error_log("CitaController Constructor: Error de conexión a BD o instanciación de modelos - " . $e->getMessage());
            // Destruir la sesión podría ser demasiado drástico aquí, considera solo mensaje de error.
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema. Por favor, intente más tarde.";
            if (defined('BASE_URL') && !headers_sent()) { 
                header("Location: " . BASE_URL . "login"); // O una página de error genérica
                exit; 
            } else {
                // Si las cabeceras ya se enviaron o BASE_URL no está definida, muestra un mensaje simple.
                echo "Error crítico del sistema. Por favor, contacte al administrador. (Ref: CCInitFail)";
                exit;
            }
        }
    }

    private function renderView($viewName, $data = []) {
        $viewPath = dirname(__DIR__) . '/Views/' . $viewName . '.php';
        extract($data); 
        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';
        if (file_exists($layoutPath)) {
            ob_start();
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "Error: No se pudo encontrar la vista '$viewName'.";
                error_log("Error: No se pudo encontrar la vista '$viewName' ($viewPath).");
            }
            $content_for_layout = ob_get_clean();
            include $layoutPath;
        } else {
            if (file_exists($viewPath)) {
                include $viewPath; // Renderizar sin layout si el layout no existe
            } else {
                echo "Error: No se pudo encontrar la vista '$viewName' ni el layout principal.";
                error_log("Error: No se pudo encontrar la vista '$viewName' ($viewPath) ni el layout principal ($layoutPath).");
            }
        }
    }

    public function mostrarCalendarioCliente() {
        // Asegúrate de que ID_ROL_CLIENTE esté definido, ya sea como constante de clase o global.
        // Si no está definido globalmente, usa self::ID_ROL_CLIENTE (si lo defines en esta clase)
        // o el valor numérico directamente. Por ahora, asumo que ID_ROL_CLIENTE es una constante global.
        $rolCliente = defined('ID_ROL_CLIENTE') ? ID_ROL_CLIENTE : 1; // Asumir 1 si no está definida globalmente
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolCliente) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión como cliente para reservar citas.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Reservar Cita";
        $active_page = 'reservar_cita'; 
        $servicios = [];
        if ($this->servicioModel) {
            // Intenta obtener solo servicios activos si el método existe
            if (method_exists($this->servicioModel, 'obtenerTodosActivos')) {
                $servicios = $this->servicioModel->obtenerTodosActivos();
            } elseif (method_exists($this->servicioModel, 'obtenerTodos')) { // Fallback a obtenerTodos
                $servicios = $this->servicioModel->obtenerTodos();
            }
        } else {
            error_log("CitaController::mostrarCalendarioCliente - servicioModel no está inicializado.");
            $_SESSION['mensaje_error_global'] = "No se pueden cargar los servicios en este momento (modelo no disponible).";
        }
        $form_data = $_SESSION['form_data']['reservar_cita'] ?? [];
        $form_errors = $_SESSION['form_errors']['reservar_cita'] ?? [];
        unset($_SESSION['form_data']['reservar_cita'], $_SESSION['form_errors']['reservar_cita']); 
        $this->renderView('cliente/citas/reservar_cita', compact('pageTitle', 'active_page', 'servicios', 'form_data', 'form_errors'));
    }

    public function guardarCitaCliente() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }
        $rolCliente = defined('ID_ROL_CLIENTE') ? ID_ROL_CLIENTE : 1; // Asumir 1 si no está definida globalmente
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolCliente) {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $datosCita = [
            'cliente_id' => $_SESSION['user_id'],
            'empleado_id' => filter_input(INPUT_POST, 'empleado_id', FILTER_VALIDATE_INT),
            'servicio_id' => filter_input(INPUT_POST, 'servicio_id', FILTER_VALIDATE_INT),
            'fecha_hora_cita_input' => $_POST['fecha_hora_cita'] ?? null, 
            'estado_cita' => self::ID_ESTADO_CITA_PENDIENTE, 
            'notas_cliente' => isset($_POST['notas_cliente']) ? trim(strip_tags($_POST['notas_cliente'])) : null,
            'fecha_hora_fin' => null 
        ];
        
        $validator = new Validator();
        $validator->validate($datosCita['servicio_id'], 'Servicio', ['required', 'integer']);
        $validator->validate($datosCita['empleado_id'], 'Empleado', ['required', 'integer']);
        
        $fecha_hora_cita_obj = null;
        if (!empty($datosCita['fecha_hora_cita_input'])) {
            $dateTimeObjInput = DateTime::createFromFormat('Y-m-d\TH:i', $datosCita['fecha_hora_cita_input']);
            if ($dateTimeObjInput && $dateTimeObjInput->format('Y-m-d\TH:i') === $datosCita['fecha_hora_cita_input']) {
                $umbralPasado = (new DateTime())->sub(new DateInterval('PT5M')); // 5 minutos de margen
                if ($dateTimeObjInput < $umbralPasado) {
                    $validator->addError('fecha_hora_cita', 'La fecha y hora de la cita no pueden ser en el pasado.');
                } else {
                    $fecha_hora_cita_obj = $dateTimeObjInput; 
                    $datosCita['fecha_hora_cita'] = $fecha_hora_cita_obj->format('Y-m-d H:i:s'); 
                }
            } else {
                $validator->addError('fecha_hora_cita', 'El formato de fecha y hora de la cita no es válido. Use el selector.');
            }
        } else {
            $validator->addError('fecha_hora_cita', 'La fecha y hora de la cita son requeridas.');
        }

        $duracion_servicio_minutos = 0;
        if ($datosCita['servicio_id'] && $this->servicioModel) {
            $servicioInfo = $this->servicioModel->obtenerPorId($datosCita['servicio_id']);
            if ($servicioInfo && isset($servicioInfo['duracion_minutos'])) {
                $duracion_servicio_minutos = (int)$servicioInfo['duracion_minutos'];
                if ($duracion_servicio_minutos <= 0) {
                    $validator->addError('servicio_id', 'El servicio seleccionado tiene una duración inválida.');
                }
            } else {
                $validator->addError('servicio_id', 'No se pudo obtener la información del servicio seleccionado.');
            }
        }

        if ($validator->hasErrors()) {
            $errors = $validator->getErrors();
            $_SESSION['form_errors']['reservar_cita'] = $errors;
            $_SESSION['form_data']['reservar_cita'] = $_POST; 
            $_SESSION['mensaje_error_global'] = "Por favor, corrige los errores del formulario.";
            error_log("Errores de validación en guardarCitaCliente: " . print_r($errors, true));
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }

        if ($fecha_hora_cita_obj && $duracion_servicio_minutos > 0) {
            $fecha_hora_fin_obj = clone $fecha_hora_cita_obj;
            $fecha_hora_fin_obj->add(new DateInterval("PT{$duracion_servicio_minutos}M"));
            $datosCita['fecha_hora_fin'] = $fecha_hora_fin_obj->format('Y-m-d H:i:s');
        } else {
            $_SESSION['mensaje_error_global'] = "Error al calcular la hora de finalización de la cita. Verifique la fecha y el servicio.";
            $_SESSION['form_data']['reservar_cita'] = $_POST;
            error_log("Error al calcular fecha_hora_fin. Fecha obj: " . ($fecha_hora_cita_obj ? 'OK' : 'NULL') . ", Duración: $duracion_servicio_minutos");
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }

        if ($this->empleadoServicioModel && $datosCita['empleado_id'] && $datosCita['servicio_id']) {
            if (!$this->empleadoServicioModel->empleadoPuedeRealizarServicio($datosCita['empleado_id'], $datosCita['servicio_id'])) {
                $_SESSION['mensaje_error_global'] = "El empleado seleccionado no ofrece el servicio elegido.";
                $_SESSION['form_data']['reservar_cita'] = $_POST;
                header("Location: " . BASE_URL . "citas/reservar");
                exit;
            }
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudo verificar si el empleado ofrece el servicio (Error del sistema ESM).";
             error_log("CitaController::guardarCitaCliente - No se pudo verificar servicio de empleado. EmpleadoServicioModel: " . ($this->empleadoServicioModel ? 'OK' : 'NULL') . ", EmpleadoID: " . $datosCita['empleado_id'] . ", ServicioID: " . $datosCita['servicio_id']);
            $_SESSION['form_data']['reservar_cita'] = $_POST;
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }

        if ($this->horarioModel && $datosCita['empleado_id'] && $datosCita['fecha_hora_cita'] && $duracion_servicio_minutos > 0) {
            // Asume que isEmpleadoDisponible toma (empleado_id, fecha_hora_inicio_str, duracion_minutos_servicio)
            if (!$this->horarioModel->isEmpleadoDisponible($datosCita['empleado_id'], $datosCita['fecha_hora_cita'], $duracion_servicio_minutos)) {
                $_SESSION['mensaje_error_global'] = "El empleado seleccionado no está disponible en la fecha y hora elegidas para la duración de este servicio. Por favor, selecciona otro horario o empleado.";
                $_SESSION['form_data']['reservar_cita'] = $_POST;
                header("Location: " . BASE_URL . "citas/reservar");
                exit;
            }
        } else {
            $logMsg = "CitaController::guardarCitaCliente - No se pudo verificar disponibilidad. ";
            $logMsg .= "HorarioModel: " . ($this->horarioModel ? 'OK' : 'NULL') . ", ";
            $logMsg .= "EmpleadoID: " . ($datosCita['empleado_id'] ?? 'NULL') . ", ";
            $logMsg .= "FechaHora: " . ($datosCita['fecha_hora_cita'] ?? 'NULL') . ", ";
            $logMsg .= "Duracion: " . ($duracion_servicio_minutos);
            error_log($logMsg);
            $_SESSION['mensaje_error_global'] = "No se pudo verificar la disponibilidad del empleado (Error del sistema HM).";
            $_SESSION['form_data']['reservar_cita'] = $_POST;
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }
        
        $datosParaCrear = $datosCita;
        unset($datosParaCrear['fecha_hora_cita_input']);

        $nuevaCitaId = $this->citaModel->crear($datosParaCrear); 

        if ($nuevaCitaId) {
            $_SESSION['mensaje_exito_global'] = "Tu cita ha sido reservada exitosamente con ID #{$nuevaCitaId}.";
            if ($datosCita['empleado_id'] && $this->notificacionModel && $this->usuarioModel) {
                $empleado_id_notificar = $datosCita['empleado_id'];
                $nombreCliente = $_SESSION['user_nombre'] ?? 'Un cliente'; 
                // Usar findById si ese es el método en tu User.php
                $clienteInfo = $this->usuarioModel->findById($datosCita['cliente_id']); 
                if ($clienteInfo && !empty($clienteInfo['nombre'])) {
                    $nombreCliente = $clienteInfo['nombre'];
                    if (!empty($clienteInfo['apellido'])) {
                        $nombreCliente .= ' ' . $clienteInfo['apellido'];
                    }
                }
                $nombreServicio = 'un servicio'; 
                if ($this->servicioModel && $datosCita['servicio_id']) {
                    $servicioInfo = $this->servicioModel->obtenerPorId($datosCita['servicio_id']); 
                    if ($servicioInfo && !empty($servicioInfo['nombre'])) {
                        $nombreServicio = $servicioInfo['nombre'];
                    }
                }
                $fechaFormateada = 'fecha desconocida';
                $horaFormateada = 'hora desconocida';
                $fechaParaUrl = date('Y-m-d'); 
                if (isset($datosCita['fecha_hora_cita'])) { 
                    try {
                        $fechaHoraObjNotif = new DateTime($datosCita['fecha_hora_cita']);
                        $fechaFormateada = $fechaHoraObjNotif->format('d/m/Y');
                        $horaFormateada = $fechaHoraObjNotif->format('h:i A');
                        $fechaParaUrl = $fechaHoraObjNotif->format('Y-m-d');
                    } catch (Exception $e) {
                        error_log("Error al formatear fecha para notificación de nueva cita: " . $e->getMessage());
                    }
                }
                $mensajeNotif = "Nueva cita asignada: Por " . htmlspecialchars($nombreCliente) . 
                                " para el servicio '" . htmlspecialchars($nombreServicio) . 
                                "' el " . $fechaFormateada . " a las " . $horaFormateada . ".";
                $urlDestinoNotif = BASE_URL . 'empleado/agenda?fecha_inicio=' . $fechaParaUrl;
                $this->notificacionModel->crear(
                    $empleado_id_notificar,
                    $mensajeNotif,
                    'nueva_cita', 
                    $urlDestinoNotif
                );
            }
            header("Location: " . BASE_URL . "mis-citas");
            exit;
        } else {
            $_SESSION['mensaje_error_global'] = "Hubo un error al intentar reservar tu cita. Por favor, inténtalo de nuevo o contacta con soporte.";
            error_log("CitaController::guardarCitaCliente - Error al llamar a citaModel->crear con datos: " . json_encode($datosParaCrear));
            $_SESSION['form_data']['reservar_cita'] = $_POST; 
            header("Location: " . BASE_URL . "citas/reservar");
            exit;
        }
    }

    public function misCitasCliente() {
        $rolCliente = defined('ID_ROL_CLIENTE') ? ID_ROL_CLIENTE : 1; // Asumir 1 si no está definida globalmente
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolCliente) {
            $_SESSION['mensaje_error_global'] = "Debes iniciar sesión como cliente para ver tus citas.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Mis Citas";
        $active_page = 'mis_citas';
        $cliente_id = $_SESSION['user_id'];
        $citas = []; 
        if ($this->citaModel && method_exists($this->citaModel, 'obtenerPorClienteIdConDetalles')) {
            $citas = $this->citaModel->obtenerPorClienteIdConDetalles($cliente_id);
            foreach ($citas as &$cita) { 
                if (isset($cita['fecha_hora_cita'])) {
                    try {
                        $dateObj = new DateTime($cita['fecha_hora_cita']);
                        $cita['fecha_formateada'] = $dateObj->format('d/m/Y');
                        $cita['hora_formateada'] = $dateObj->format('h:i A');
                    } catch (Exception $e) {
                        $cita['fecha_formateada'] = 'Inválida';
                        $cita['hora_formateada'] = 'Inválida';
                        error_log("Error al formatear fecha para misCitasCliente: " . $e->getMessage() . " Cita ID: " . ($cita['id_cita'] ?? 'N/A'));
                    }
                }
            }
            unset($cita); 
        } else {
            error_log("CitaController::misCitasCliente - CitaModel o método obtenerPorClienteIdConDetalles no disponible.");
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar tus citas en este momento.";
        }
        $this->renderView('cliente/citas/mis_citas', compact('pageTitle', 'citas', 'active_page'));
    }

    public function calendarioGeneralAdmin() {
        $rolAdmin = defined('ID_ROL_ADMIN') ? ID_ROL_ADMIN : 3; // Asumir 3 si no está definida globalmente
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolAdmin) {
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Calendario General de Citas";
        $active_page = 'calendario_citas_admin';
        $citas_data = [];
        $citas_agrupadas = [];
        if ($this->citaModel && method_exists($this->citaModel, 'obtenerTodasConDetalles')) {
            $citas_data = $this->citaModel->obtenerTodasConDetalles();
            if ($citas_data === false) { 
                error_log("CitaController::calendarioGeneralAdmin - obtenerTodasConDetalles devolvió false.");
                $_SESSION['mensaje_error_global'] = "Error al cargar las citas para el calendario.";
                $citas_data = []; 
            }
        } else {
            error_log("CitaController::calendarioGeneralAdmin - CitaModel o método obtenerTodasConDetalles no disponible.");
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar las citas (modelo no disponible).";
        }
        foreach ($citas_data as $cita) {
            try {
                $fecha = (new DateTime($cita['fecha_hora_cita']))->format('Y-m-d');
                $cita['hora_formateada'] = (new DateTime($cita['fecha_hora_cita']))->format('h:i A'); 
                $citas_agrupadas[$fecha][] = $cita;
            } catch (Exception $e) {
                error_log("Error al procesar fecha de cita para calendario: " . $e->getMessage() . " Cita ID: " . ($cita['id_cita'] ?? 'N/A'));
            }
        }
        ksort($citas_agrupadas); 
        $this->renderView('admin/citas/calendario_general', compact('pageTitle', 'citas_agrupadas', 'active_page'));
    }

    public function listarCitasAdmin() {
        $rolAdmin = defined('ID_ROL_ADMIN') ? ID_ROL_ADMIN : 3; // Asumir 3 si no está definida globalmente
         if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolAdmin) {
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Gestionar Todas las Citas";
        $active_page = 'gestionar_citas_admin';
        $citas = [];
        if ($this->citaModel && method_exists($this->citaModel, 'obtenerTodasConDetalles')) {
            $citas = $this->citaModel->obtenerTodasConDetalles();
            if ($citas === false) { 
                 error_log("CitaController::listarCitasAdmin - obtenerTodasConDetalles devolvió false.");
                $_SESSION['mensaje_error_global'] = "Error al cargar la lista de citas.";
                $citas = []; 
            }
            foreach ($citas as &$cita_item) { 
                if (isset($cita_item['fecha_hora_cita'])) {
                    try {
                        $dateObj = new DateTime($cita_item['fecha_hora_cita']);
                        $cita_item['fecha_formateada'] = $dateObj->format('d/m/Y');
                        $cita_item['hora_formateada'] = $dateObj->format('h:i A');
                    } catch (Exception $e) {
                        $cita_item['fecha_formateada'] = 'Inválida';
                        $cita_item['hora_formateada'] = 'Inválida';
                         error_log("Error al procesar fecha de cita para lista admin: " . $e->getMessage() . " Cita ID: " . ($cita_item['id_cita'] ?? 'N/A'));
                    }
                }
            }
            unset($cita_item); 
        } else {
            error_log("CitaController::listarCitasAdmin - CitaModel o método obtenerTodasConDetalles no disponible.");
            $_SESSION['mensaje_error_global'] = "No se pudieron cargar las citas (modelo no disponible).";
        }
        $this->renderView('admin/citas/lista_citas', compact('pageTitle', 'citas', 'active_page'));
    }
    
    public function cancelarCitaCliente($id_cita_str) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            header("Location: " . BASE_URL . "mis-citas");
            exit;
        }
        $rolCliente = defined('ID_ROL_CLIENTE') ? ID_ROL_CLIENTE : 1; // Asumir 1 si no está definida globalmente
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != $rolCliente) {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $id_cita = filter_var($id_cita_str, FILTER_VALIDATE_INT);
        if (!$id_cita) {
             $_SESSION['mensaje_error_global'] = "ID de cita inválido.";
            header("Location: " . BASE_URL . "mis-citas");
            exit;
        }
        $cita = $this->citaModel->obtenerCitaPorId($id_cita); 
        if (!$cita || $cita['cliente_id'] != $_SESSION['user_id']) {
            $_SESSION['mensaje_error_global'] = "No puedes cancelar esta cita o la cita no existe.";
            header("Location: " . BASE_URL . "mis-citas");
            exit;
        }
        // Considera usar IDs de estado en lugar de strings para 'estado_cita' para mayor robustez
        if (in_array($cita['estado_cita'], ['Completada', 'Cancelada por Cliente', 'Cancelada por Sistema', 'No Asistió'])) {
             $_SESSION['mensaje_info_global'] = "Esta cita ya no se puede cancelar (Estado actual: ".htmlspecialchars($cita['estado_cita']).").";
            header("Location: " . BASE_URL . "mis-citas");
            exit;
        }
        // Considera pasar un ID de estado numérico a actualizarEstado
        $actualizado = $this->citaModel->actualizarEstado($id_cita, 'Cancelada por Cliente', $_SESSION['user_id']); 
        if ($actualizado) {
            $_SESSION['mensaje_exito_global'] = "La cita #{$id_cita} ha sido cancelada.";
            if (isset($cita['empleado_id']) && $cita['empleado_id'] && $this->notificacionModel && $this->usuarioModel) {
                $empleado_id_notificar = $cita['empleado_id'];
                $nombreCliente = $_SESSION['user_nombre'] ?? 'un cliente';
                // Usar findById si ese es el método en tu User.php
                $clienteInfo = $this->usuarioModel->findById($cita['cliente_id']); 
                if ($clienteInfo && !empty($clienteInfo['nombre'])) {
                    $nombreCliente = $clienteInfo['nombre'];
                    if (!empty($clienteInfo['apellido'])) {
                        $nombreCliente .= ' ' . $clienteInfo['apellido'];
                    }
                }
                $nombreServicio = 'un servicio';
                if ($this->servicioModel && isset($cita['servicio_id'])) {
                    $servicioInfo = $this->servicioModel->obtenerPorId($cita['servicio_id']);
                    if ($servicioInfo && !empty($servicioInfo['nombre'])) {
                        $nombreServicio = $servicioInfo['nombre'];
                    }
                }
                $fechaFormateada = 'fecha desconocida';
                $fechaParaUrlNotif = date('Y-m-d');
                 try {
                    $fechaHoraObjNotifCancel = new DateTime($cita['fecha_hora_cita']);
                    $fechaFormateada = $fechaHoraObjNotifCancel->format('d/m/Y \a \l\a\s h:i A'); 
                    $fechaParaUrlNotif = $fechaHoraObjNotifCancel->format('Y-m-d');
                } catch (Exception $e) { /* ya está inicializada, no hacer nada o loggear */ }

                $mensajeNotif = "Cita cancelada: El cliente " . htmlspecialchars($nombreCliente) . 
                                " ha cancelado su cita para '" . htmlspecialchars($nombreServicio) . 
                                "' del " . $fechaFormateada . ".";
                $urlDestinoNotif = BASE_URL . 'empleado/agenda?fecha_inicio=' . $fechaParaUrlNotif;
                $this->notificacionModel->crear(
                    $empleado_id_notificar,
                    $mensajeNotif,
                    'cita_cancelada',
                    $urlDestinoNotif
                );
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error al intentar cancelar la cita #{$id_cita}. Por favor, contacta con soporte.";
            error_log("CitaController::cancelarCitaCliente - Error al llamar a citaModel->actualizarEstado para cita ID: $id_cita");
        }
        header("Location: " . BASE_URL . "mis-citas");
        exit;
    }

    public function obtenerEmpleadosDisponiblesParaServicio() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['error' => 'No autenticado', 'empleados' => []]);
            http_response_code(401); 
            exit;
        }

        $servicio_id = filter_input(INPUT_GET, 'servicio_id', FILTER_VALIDATE_INT);
        $fecha_hora_str_input = $_GET['fecha_hora'] ?? null; 

        // Log para depuración
        error_log("DEBUG: API empleados disponibles - Solicitado servicio_id=$servicio_id, fecha_hora=$fecha_hora_str_input");

        if (!$servicio_id || !$fecha_hora_str_input) {
            echo json_encode(['error' => 'Parámetros incompletos: servicio_id y fecha_hora son requeridos.', 'empleados' => []]);
            http_response_code(400); 
            exit;
        }
        
        $fecha_hora_obj_consulta = null;
        try {
            $dt = new DateTime($fecha_hora_str_input); 
            $umbralPasadoAjax = (new DateTime())->sub(new DateInterval('PT1M')); // 1 minuto de margen
            if ($dt < $umbralPasadoAjax) {
                 echo json_encode(['error' => 'La fecha y hora seleccionadas están en el pasado.', 'empleados' => []]);
                 http_response_code(400); 
                 exit;
            }
            $fecha_hora_obj_consulta = $dt; 
        } catch (Exception $e) {
            echo json_encode(['error' => 'Formato de fecha y hora inválido. Use el selector.', 'empleados' => []]);
            http_response_code(400); 
            exit;
        }

        $duracion_servicio_minutos = 0;
        if ($this->servicioModel) {
            $servicioInfo = $this->servicioModel->obtenerPorId($servicio_id);
            if ($servicioInfo && isset($servicioInfo['duracion_minutos'])) {
                $duracion_servicio_minutos = (int)$servicioInfo['duracion_minutos'];
            }
        }
        if ($duracion_servicio_minutos <= 0) {
            echo json_encode(['error' => 'No se pudo determinar la duración del servicio o es inválida.', 'empleados' => []]);
            http_response_code(400);
            exit;
        }

        $empleadosQueOfrecenServicio = [];
        if ($this->empleadoServicioModel && method_exists($this->empleadoServicioModel, 'obtenerEmpleadosPorServicio')) {
            $empleadosQueOfrecenServicio = $this->empleadoServicioModel->obtenerEmpleadosPorServicio($servicio_id);
            error_log("DEBUG: API empleados disponibles - Se encontraron " . count($empleadosQueOfrecenServicio) . " empleados que ofrecen el servicio $servicio_id");
        } else {
            error_log("CitaController::obtenerEmpleadosDisponiblesParaServicio - EmpleadoServicioModel no disponible o método no existe.");
            echo json_encode(['error' => 'Error del sistema al buscar empleados (ESM).', 'empleados' => []]);
            http_response_code(500); 
            exit;
        }
        
        // MODIFICACIÓN: Usar directamente los empleados que ofrecen el servicio sin verificar horarios
        $empleadosDisponibles = [];
        foreach ($empleadosQueOfrecenServicio as $empleado) {
            if (isset($empleado['id'])) { 
                $nombre = $empleado['nombre'] ?? 'Empleado';
                $apellido = $empleado['apellido'] ?? ''; 
                $nombreCompleto = htmlspecialchars(trim($nombre . ($apellido ? ' ' . $apellido : '')));
                $empleadosDisponibles[] = ['id' => $empleado['id'], 'nombre_completo' => $nombreCompleto];
            }
        }

        error_log("DEBUG: API empleados disponibles - Retornando " . count($empleadosDisponibles) . " empleados");

        if (empty($empleadosDisponibles) && !empty($empleadosQueOfrecenServicio)) {
             echo json_encode(['empleados' => [], 'mensaje' => 'Ningún empleado que ofrece este servicio está disponible en la fecha y hora seleccionadas para la duración del mismo.']);
        } elseif (empty($empleadosQueOfrecenServicio)) {
            echo json_encode(['empleados' => [], 'mensaje' => 'Ningún empleado ofrece este servicio actualmente.']);
        }
        else {
            echo json_encode(['empleados' => $empleadosDisponibles]);
        }
        exit;
    }

    public function __destruct() {
        // La clase Database (en $this->db) debería manejar el cierre de sus conexiones
        // PDO y MySQLi en su propio __destruct si es necesario (estableciendo las propiedades a null).
        // No es estrictamente necesario cerrar $this->pdoConn y $this->mysqliConn aquí
        // si $this->db lo hace o si confías en que PHP cierre las conexiones al final del script.
        // Sin embargo, para ser explícito, podrías hacer:
        // $this->pdoConn = null;
        // $this->mysqliConn = null;
        // $this->db = null; 
        // Pero esto es opcional y depende de cómo esté implementado Database::__destruct().
    }
}
?>