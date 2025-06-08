<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

ob_start();

require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/Core/Database.php';

$db = null;

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(503);
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

ob_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Solo se permiten peticiones GET']);
    if ($db) $db->close();
    exit;
}

$sql = "SELECT id, nombre, email, fecha_registro, fotografia FROM usuarios";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    error_log("API Error en la consulta SQL para buscar usuarios: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error al obtener los datos de los usuarios.']);
    if ($db) $db->close();
    exit;
}

$usuarios = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Si la fotografía se guarda solo como nombre de archivo y necesitas la URL completa:
        if (!empty($row['fotografia'])) {
            // Asumiendo que 'uploads/' está dentro de 'public/' y BASE_URL apunta a 'public/'
            // y que 'fotografia' solo contiene el nombre del archivo.
            $row['fotografia_url'] = rtrim(BASE_URL, '/') . '/uploads/' . $row['fotografia'];
        } else {
            $row['fotografia_url'] = null;
        }
        $usuarios[] = $row;
    }
}

http_response_code(200);
echo json_encode($usuarios);

mysqli_free_result($result);
if ($db) {
    $db->close();
}
?>