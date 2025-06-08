<?php
$host = "localhost";
$user = "root";
$password = "Flakita_473_03_01_2006";
$db = "mi_base";

$conn = mysqli_connect($host, $user, $password, $db, 3307);

if (!$conn) {
    die(json_encode(["error" => "Error de conexión: " . mysqli_connect_error()]));
}
?>
