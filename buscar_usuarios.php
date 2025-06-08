<?php
header('Content-Type: application/json');
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Solo se permiten peticiones GET']);
    exit;
}

// Consulta para traer todos los usuarios
$sql = "SELECT id, nombre, email, fecha_registro, fotografia FROM usuarios";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

$usuarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
?>
