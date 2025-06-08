<?php
// No es necesario incluir config.php aquí si ya se incluye globalmente
// en el front controller (public/index.php) antes de instanciar Database.
// Si no, tendrías que hacer: require_once __DIR__ . '/../config.php';

class Database {
    private $connection;
    // Las propiedades pueden tomar los valores de las constantes directamente
    // o puedes pasarlas al constructor si prefieres más flexibilidad (pero para este caso, directo está bien)
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $port = DB_PORT; // Usar la constante del puerto

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        // Usar las propiedades de la clase que tomaron los valores de las constantes
        $this->connection = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname, $this->port);

        if (!$this->connection) {
            error_log("Error de conexión a la base de datos: " . mysqli_connect_error() . " (Host: {$this->host}, User: {$this->user}, DB: {$this->dbname}, Port: {$this->port})");
            // En lugar de die(), que detiene todo, lanzamos una excepción
            // Esto permite que el código que llama maneje el error de forma más elegante.
            throw new Exception("No se pudo conectar a la base de datos. Por favor, inténtelo más tarde o contacte al administrador.");
        }

        if (!mysqli_set_charset($this->connection, "utf8mb4")) {
            error_log("Error al establecer el conjunto de caracteres UTF-8 para la conexión: " . mysqli_error($this->connection));
            // Podrías lanzar una excepción aquí también si es crítico
        }
    }

    public function getConnection() {
        if (!$this->connection) {
            // Intenta reconectar si la conexión se perdió por alguna razón (opcional, puede tener implicaciones)
            // error_log("Conexión perdida, intentando reconectar...");
            // $this->connect();
            // O simplemente lanza un error si se espera que la conexión exista
            throw new Exception("La conexión a la base de datos no está disponible.");
        }
        return $this->connection;
    }

    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
            $this->connection = null; // Marcar como cerrada
        }
    }

    public function __destruct() {
        $this->close();
    }
}
?>