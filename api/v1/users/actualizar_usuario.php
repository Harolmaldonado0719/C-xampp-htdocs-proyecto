
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, PATCH"); // Permitir PATCH también es común para actualizaciones parciales
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

// Obtener los datos enviados en la solicitud PUT o PATCH
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    if (!isset($data['id']) || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID de usuario no proporcionado o vacío."]);
        if ($db) $db->close();
        exit();
    }
    
    $id = $data['id'];
    $updates = [];
    $params = [];
    $types = "";

    // Campos permitidos para actualizar (excluye 'password' a menos que se maneje específicamente con re-hash)
    // Si quieres actualizar la foto, necesitarías un manejo de archivos aquí.
    $allowed_fields = ['nombre', 'email']; // Simplificado, añade más según tu tabla 'usuarios'
                                          // Ejemplo: 'apellido', 'telefono', 'rol' si existen

    foreach ($allowed_fields as $field) {
        if (array_key_exists($field, $data)) { // Usar array_key_exists para permitir valores null o vacíos si son válidos
            $updates[] = "$field = ?";
            $params[] = $data[$field];
            $types .= "s"; // Asumimos strings, ajusta si es necesario
        }
    }
    
    // Manejo especial para la contraseña si se incluye
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }


    if (count($updates) > 0) {
        $params[] = $id; // Añadir el ID al final para el WHERE
        $types .= "i";   // El ID es un entero

        $sql = "UPDATE usuarios SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            http_response_code(500);
            error_log("API Error al preparar la consulta UPDATE: " . $conn->error . " SQL: " . $sql);
            echo json_encode(["error" => "Error al preparar la consulta para actualizar."]);
            if ($db) $db->close();
            exit();
        }

        // Usar call_user_func_array para bind_param con un número variable de parámetros
        $bind_params = [&$types]; // El primer elemento es la cadena de tipos
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key]; // Pasar por referencia
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_params);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Usuario actualizado correctamente."]);
            } else {
                // Verificar si el usuario existe para diferenciar entre "no cambios" y "no encontrado"
                $check_sql = "SELECT id FROM usuarios WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows > 0) {
                    http_response_code(200); // OK, pero no hubo cambios
                    echo json_encode(["success" => true, "message" => "No hubo cambios para actualizar o los datos eran los mismos."]);
                } else {
                    http_response_code(404); // Not Found
                    echo json_encode(["error" => "No se encontró el usuario con el ID proporcionado."]);
                }
                $check_stmt->close();
            }
        } else {
            http_response_code(500);
            error_log("API Error al ejecutar la actualización del usuario: " . $stmt->error);
            echo json_encode(["error" => "Error al actualizar el usuario."]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["error" => "No se proporcionaron datos válidos para actualizar."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);
}

if ($db) {
    $db->close();
}
?>