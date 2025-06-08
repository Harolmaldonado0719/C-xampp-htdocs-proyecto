<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');                     // Tomado de tu $host
define('DB_USER', 'root');                         // Tomado de tu $user
define('DB_PASS', 'Flakita_473_03_01_2006');      // Tomado de tu $password
define('DB_NAME', 'mi_base');                      // Tomado de tu $db
define('DB_PORT', 3307);                           // Tomado de tu puerto en mysqli_connect

// URL base de tu aplicación (apuntando a la carpeta public)
// Ajusta "Proyecto-clip" si el nombre de tu carpeta raíz es diferente
define('BASE_URL', 'http://localhost/Proyecto-clip/public/');

// Directorio para subidas de archivos (desde la raíz del proyecto a public/uploads/)
// __DIR__ aquí se refiere al directorio 'app' (donde está este config.php)
define('APP_UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/');
// Alternativamente, si quieres definir PROJECT_ROOT_PATH correctamente:
// define('PROJECT_ROOT_PATH', dirname(__DIR__)); // Esto sería c:\xampp\htdocs\Proyecto-clip
// define('APP_UPLOAD_DIR', PROJECT_ROOT_PATH . '/public/uploads/');


// Podrías añadir más configuraciones aquí después, como:
// define('SITE_NAME', 'Mi Proyecto Clip');
// define('SESSION_TIMEOUT', 3600); // 1 hora

// Asegúrate de que la carpeta de subidas exista y tenga permisos de escritura
// C:\xampp\htdocs\Proyecto-clip\public\uploads\
?>