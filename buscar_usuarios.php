<?php
header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de que este archivo establece $conn correctamente

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Solo se permiten peticiones GET']);
    exit;
}

// Consulta para traer todos los usuarios
// Asegúrate de que todas las columnas listadas aquí SÍ EXISTEN en tu tabla 'usuarios'
$sql = "SELECT id, nombre, email, fecha_registro, fotografia FROM usuarios"; // CORREGIDO: Eliminadas columnas no existentes
// Si tienes otras columnas como 'apellido', 'telefono', 'rol' y SÍ existen, puedes añadirlas aquí.
// Ejemplo: $sql = "SELECT id, nombre, email, fecha_registro, fotografia, apellido, telefono, rol FROM usuarios";

$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500); // Error interno del servidor
    error_log("Error en la consulta SQL para buscar usuarios: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error al obtener los datos de los usuarios.']);
    if ($conn) {
        mysqli_close($conn);
    }
    exit;
}

$usuarios = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Opcional: Construir URL completa para la fotografía si es necesario
        // if (!empty($row['fotografia'])) {
        //     $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/Proyecto-clip/uploads/";
        //     $row['fotografia_url'] = $baseUrl . $row['fotografia'];
        // }
        $usuarios[] = $row;
    }
}

http_response_code(200); // OK
echo json_encode($usuarios);

mysqli_free_result($result);
if ($conn) {
    mysqli_close($conn);
}
?>