<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Flakita_473_03_01_2006'); // ¡Importante! Considera usar variables de entorno para contraseñas en producción.
define('DB_NAME', 'mi_base');
define('DB_PORT', 3307);

// URL base de tu aplicación (apuntando a la carpeta public)
// Asegúrate de que esta URL sea la correcta para acceder a tu public/index.php
// Si tu servidor web está configurado para que el DocumentRoot sea /Proyecto-clip/public/,
// entonces BASE_URL podría ser 'http://localhost/Proyecto-clip/' (sin /public/ al final).
// Pero si accedes vía http://localhost/Proyecto-clip/public/, entonces la configuración actual es correcta.
define('BASE_URL', 'http://localhost/Proyecto-clip/public/');

// Directorio para subidas de archivos (ruta absoluta en el servidor)
// dirname(__DIR__) apunta al directorio padre de 'app', es decir, la raíz del proyecto.
define('APP_UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/');

// Ruta pública relativa para acceder a los archivos subidos desde el navegador (relativa a BASE_URL si BASE_URL ya incluye /public/)
// O relativa a la raíz del sitio si BASE_URL no incluye /public/ y el DocumentRoot es /public/
define('APP_UPLOAD_DIR_PUBLIC_PATH', 'uploads/');


// Configuración de IDs de Roles (deben coincidir con los IDs en tu tabla `roles`)
define('ID_ROL_ADMIN', 1);
define('ID_ROL_CLIENTE', 2); // Asegúrate que este ID es el correcto para clientes en tu BD
define('ID_ROL_EMPLEADO', 3);

// Habilitar o deshabilitar el modo de depuración
define('DEBUG_MODE', true); // Cambiar a false en producción

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0); // Considera E_ALL & ~E_NOTICE & ~E_DEPRECATED para producción si quieres loguear algunos errores.
}

// NO es necesario session_start() aquí si ya se llama en public/index.php al inicio
// y/o en los constructores de los controladores que manejan sesión.
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Crear el directorio de subidas si no existe.
// Es una buena práctica asegurarse de que el directorio exista.
if (!is_dir(APP_UPLOAD_DIR)) {
    // Intentar crear el directorio recursivamente con permisos 0775.
    // El '@' suprime errores de mkdir si DEBUG_MODE está desactivado, pero los loguearemos si falla.
    if (!@mkdir(APP_UPLOAD_DIR, 0775, true)) {
        $error = error_get_last();
        $errorMessage = "Error crítico: No se pudo crear el directorio de subidas: " . APP_UPLOAD_DIR;
        if ($error !== null) {
            $errorMessage .= " - Detalles del sistema: " . $error['message'];
        }
        error_log($errorMessage);
        // En un entorno de producción, podrías querer detener la ejecución o mostrar un error amigable
        // si el directorio de subidas es esencial para la funcionalidad.
        if (DEBUG_MODE) {
            // Solo muestra el die() si estás en modo debug para no exponer rutas en producción.
            die($errorMessage . ". Por favor, verifica los permisos de la carpeta 'public'.");
        }
    }
}
?>