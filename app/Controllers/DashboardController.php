<?php
require_once __DIR__ . '/../Core/Database.php'; // Para la conexión a la BD
// Si tienes funciones de ayuda globales, podrías incluirlas aquí también
// require_once __DIR__ . '/../Core/functions.php'; 

class DashboardController {
    private $db;
    private $conn;

    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Proteger el controlador: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['usuario_id'])) {
            // Redirigir al login si no hay sesión
            header("Location: " . BASE_URL . "login?mensaje_error=" . urlencode("Debes iniciar sesión para acceder al dashboard."));
            exit;
        }

        // Establecer conexión a la base de datos
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            // Manejo básico de error de conexión. En producción, loguear y mostrar página amigable.
            error_log("DashboardController: Error de conexión a BD - " . $e->getMessage());
            die("Error crítico: No se pudo conectar a la base de datos. Por favor, intente más tarde.");
        }
    }

    /**
     * Muestra la página principal del dashboard.
     */
    public function index() {
        $pageTitle = "Dashboard";
        $usuarios = []; // Array para almacenar los usuarios a mostrar

        // Obtener usuarios de la base de datos (ejemplo)
        // Esta consulta es similar a la que tenías en tu dashboard.php original
        $sql = "SELECT id, nombre, email, fecha_registro, fotografia FROM usuarios ORDER BY fecha_registro DESC";
        $stmt = mysqli_prepare($this->conn, $sql);

        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                // Procesar la URL de la fotografía
                if (!empty($row['fotografia'])) {
                    // Asumiendo que 'fotografia' solo guarda el nombre del archivo y está en 'uploads/'
                    $row['fotografia_url'] = BASE_URL . 'uploads/' . htmlspecialchars($row['fotografia']);
                    // Para verificar si el archivo existe, necesitamos la ruta del servidor
                    // Asegúrate de que APP_UPLOAD_DIR esté definido en tu config.php y apunte a la carpeta uploads
                    $row['fotografia_path_exists'] = defined('APP_UPLOAD_DIR') && file_exists(APP_UPLOAD_DIR . DIRECTORY_SEPARATOR . $row['fotografia']);
                } else {
                    // Si no hay foto, puedes usar una imagen por defecto
                    $row['fotografia_url'] = BASE_URL . 'img/default-avatar.png'; // Crea esta imagen si no existe
                    $row['fotografia_path_exists'] = false;
                }
                $usuarios[] = $row;
            }
            mysqli_stmt_close($stmt);
        } else {
            // Manejo de error si la consulta falla
            error_log("Error al preparar la consulta para obtener usuarios del dashboard: " . mysqli_error($this->conn));
            // Podrías pasar un mensaje de error a la vista si lo deseas
            // $_SESSION['mensaje_error_dashboard'] = "No se pudieron cargar los datos de los usuarios.";
        }
        
        // Obtener el nombre del usuario de la sesión para el saludo
        $usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario'; // Usa 'Usuario' como fallback

        // Datos que se pasarán a la vista
        $data_for_view = [
            'pageTitle' => $pageTitle,
            'usuarios' => $usuarios,
            'usuario_nombre' => $usuario_nombre,
            // Puedes añadir más datos aquí si los necesitas en la vista del dashboard
        ];

        // Define la ruta de la vista específica del dashboard
        // Esta es la vista que crearemos a continuación
        $viewPath = __DIR__ . '/../Views/dashboard/index.php'; 

        // Extraer las variables para que estén disponibles en el layout y la vista
        extract($data_for_view); 

        // Cargar el layout principal, que a su vez cargará la $viewPath
        include __DIR__ . '/../Views/layouts/main_layout.php'; 
    }

    /**
     * Cierra la conexión a la base de datos al destruir el objeto.
     */
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>