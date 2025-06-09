
<?php
// require_once __DIR__ . '/../Core/Database.php'; // Descomentar si necesitas conexión directa aquí

class ClienteController {
    // private $db; // Descomentar si necesitas conexión directa aquí
    // private $conn; // Descomentar si necesitas conexión directa aquí

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Asegurar que sea un cliente y esté logueado
        // CORRECCIÓN: Usar $_SESSION['user_id'] y $_SESSION['user_rol_id'] para consistencia
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != ID_ROL_CLIENTE) { 
            // ID_ROL_CLIENTE debe estar definido en config.php (en tu config es 2)
            $_SESSION['mensaje_error_global'] = "Acceso no autorizado al portal de cliente.";
            header("Location: " . BASE_URL . "login");
            exit;
        }

        /* 
        // Descomentar y adaptar si necesitas conexión a BD en el constructor
        try {
            // $this->db = new Database();
            // $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            error_log("ClienteController: Error de conexión a BD - " . $e->getMessage());
            // Manejar el error apropiadamente, quizás mostrando una página de error.
            // $pageTitle = "Error de Sistema";
            // $viewPath = dirname(__DIR__) . '/Views/errors/service_unavailable.php'; // Asegúrate que esta vista exista
            // extract(compact('pageTitle', 'viewPath'));
            // include dirname(__DIR__) . '/Views/layouts/main_layout.php';
            // exit;
        }
        */
    }

    public function index() { 
        $pageTitle = "Portal de Servicios";
        // CORRECCIÓN: Usar $_SESSION['user_nombre'] para consistencia, 
        // asumiendo que AuthController establece esta clave para el nombre del usuario.
        $usuario_nombre = $_SESSION['user_nombre'] ?? 'Cliente'; 

        // Aquí podrías cargar datos específicos del cliente si es necesario
        // Ejemplo: $servicios_cliente = $this->cargarServiciosDelCliente($_SESSION['user_id']);

        // Ruta a la vista (app/Views/cliente/index.php)
        $viewPath = dirname(__DIR__) . '/Views/cliente/index.php'; 

        // Pasar las variables a la vista.
        // La vista cliente/index.php espera $pageTitle, $usuario_nombre
        // Si pasas $servicios_cliente, añádelo aquí:
        // extract(compact('pageTitle', 'usuario_nombre', 'servicios_cliente', 'viewPath'));
        extract(compact('pageTitle', 'usuario_nombre', 'viewPath'));

        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            error_log("Error crítico: No se encontró el archivo de layout principal: " . $layoutPath);
            die("Error crítico: No se pudo cargar la estructura de la página. Contacte al administrador.");
        }
    }

    // Otros métodos específicos del ClienteController...
    // private function cargarServiciosDelCliente($clienteId) {
    //     // Lógica para obtener datos de la base de datos
    //     // Ejemplo:
    //     // if (!$this->conn) { // Manejar error de conexión // return []; }
    //     // $sql = "SELECT * FROM servicios_contratados WHERE id_cliente = ?";
    //     // $stmt = mysqli_prepare($this->conn, $sql);
    //     // mysqli_stmt_bind_param($stmt, "i", $clienteId);
    //     // mysqli_stmt_execute($stmt);
    //     // $result = mysqli_stmt_get_result($stmt);
    //     // $servicios = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //     // mysqli_stmt_close($stmt);
    //     // return $servicios;
    //     return []; // Retorna un array de datos
    // }
    

    /*
    // Descomentar si necesitas cerrar la conexión a BD en el destructor
    public function __destruct() {
        if (isset($this->db) && $this->db) {
            $this->db->close();
        }
    }
    */
}
?>