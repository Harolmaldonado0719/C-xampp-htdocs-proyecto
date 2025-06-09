
<?php
// require_once __DIR__ . '/../config.php'; // Las constantes de rol ya están disponibles globalmente por index.php

class EmpleadoController {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Proteger el controlador: solo para empleados logueados
        // CAMBIO AQUÍ: usar user_id
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_EMPLEADO) {
            // Si no es empleado, o no está logueado, redirigir
            // CAMBIO AQUÍ: usar user_id
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta área.";
                header("Location: " . BASE_URL . "login");
            } else {
                // Está logueado pero no es empleado
                $_SESSION['mensaje_error_global'] = "No tienes permiso para acceder a esta sección.";
                // Redirigir a su dashboard correspondiente o a una página de error de acceso
                if (isset($_SESSION['user_rol_id'])) { // Verificar que user_rol_id exista antes de usarlo
                    if ($_SESSION['user_rol_id'] == ID_ROL_ADMIN) {
                        header("Location: " . BASE_URL . "dashboard");
                    } elseif ($_SESSION['user_rol_id'] == ID_ROL_CLIENTE) { // Añadido elseif para cliente
                        header("Location: " . BASE_URL . "portal_servicios");
                    } else { // Rol desconocido
                        header("Location: " . BASE_URL . "login"); // O una página de error genérica
                    }
                } else { // user_rol_id no está seteado, sesión inconsistente
                     header("Location: " . BASE_URL . "login");
                }
            }
            exit;
        }
    }

    public function dashboard() {
        $pageTitle = "Panel de Empleado";
        // Aquí puedes cargar datos específicos para el empleado si es necesario
        // $citasPendientes = $this->citaModel->getCitasPendientesEmpleado($_SESSION['user_id']);
        // $serviciosAsignados = $this->servicioModel->getServiciosPorEmpleado($_SESSION['user_id']);

        $viewPath = __DIR__ . '/../Views/empleado/dashboard_empleado.php'; // Necesitaremos crear esta vista
        
        // Pasar datos a la vista
        $data_for_view = compact('pageTitle' /*, 'citasPendientes', 'serviciosAsignados'*/);
        extract($data_for_view);
        
        include __DIR__ . '/../Views/layouts/main_layout.php';
    }

    // Aquí podrías añadir más métodos para el empleado:
    // public function gestionarCitas() { ... }
    // public function verServicios() { ... }
    // etc.
}
?>