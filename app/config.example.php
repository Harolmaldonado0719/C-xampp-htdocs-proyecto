<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Usuario predeterminado en XAMPP
define('DB_PASS', '');           // Contraseña en XAMPP (por defecto vacía)
define('DB_NAME', 'mi_base'); // Nombre que deberá usar para la base de datos
define('DB_PORT', 3307);         // Puerto predeterminado de MySQL (cambie si es necesario)

// URL base de la aplicación
define('BASE_URL', 'http://localhost/Proyecto-clip/public/');

// Directorio para subidas de archivos
define('APP_UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/');

// Ruta pública para acceder a los archivos subidos
define('APP_UPLOAD_DIR_PUBLIC_PATH', 'uploads/');

// Configuración de IDs de Roles
define('ID_ROL_ADMIN', 1);
define('ID_ROL_CLIENTE', 2);
define('ID_ROL_EMPLEADO', 3);

// Configuración del modo de depuración
define('DEBUG_MODE', true);  // Cambiar a false en producción

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Crear el directorio de subidas si no existe
if (!is_dir(APP_UPLOAD_DIR)) {
    if (!@mkdir(APP_UPLOAD_DIR, 0775, true)) {
        $error = error_get_last();
        $errorMessage = "Error crítico: No se pudo crear el directorio de subidas: " . APP_UPLOAD_DIR;
        if ($error !== null) {
            $errorMessage .= " - Detalles del sistema: " . $error['message'];
        }
        error_log($errorMessage);
        
        if (DEBUG_MODE) {
            die($errorMessage . ". Por favor, verifica los permisos de la carpeta 'public'.");
        }
    }
}
?>