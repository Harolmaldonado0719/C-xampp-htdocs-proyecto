
<?php

class Database {
    private $mysqli_connection;
    private $pdo_connection;

    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $port = DB_PORT;
    private $charset = 'utf8mb4'; 

    public function __construct() {
        
    }

    private function connectMysqli() {
        if ($this->mysqli_connection === null) {
            $this->mysqli_connection = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname, $this->port);

            if (!$this->mysqli_connection) {
                error_log("Error de conexión MySQLi: " . mysqli_connect_error() . " (Host: {$this->host}, User: {$this->user}, DB: {$this->dbname}, Port: {$this->port})");
                throw new Exception("No se pudo conectar a la base de datos (MySQLi).");
            }

            if (!mysqli_set_charset($this->mysqli_connection, $this->charset)) {
                error_log("Error al establecer el conjunto de caracteres UTF-8 para MySQLi: " . mysqli_error($this->mysqli_connection));
            }
        }
    }

    private function connectPdo() {
        if ($this->pdo_connection === null) {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $this->pdo_connection = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $e) {
                error_log("Error de conexión PDO: " . $e->getMessage() . " (DSN: {$dsn})");
                throw new Exception("No se pudo conectar a la base de datos (PDO).");
            }
        }
    }

    public function getMysqliConnection() {
        if ($this->mysqli_connection === null) {
            $this->connectMysqli();
        }
        return $this->mysqli_connection;
    }

    public function getPdoConnection() {
        if ($this->pdo_connection === null) {
            $this->connectPdo();
        }
        return $this->pdo_connection;
    }

    public function close() {
        if ($this->mysqli_connection) {
            mysqli_close($this->mysqli_connection);
            $this->mysqli_connection = null;
        }
        if ($this->pdo_connection) {
            $this->pdo_connection = null; // PDO cierra la conexión cuando el objeto es destruido o se establece a null
        }
    }

    public function __destruct() {
        $this->close();
    }
}
?>