<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permite el acceso desde cualquier origen (ajusta según tus necesidades de seguridad)
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// include 'db.php'; // Línea original
include 'conexion.php'; // CORRECCIÓN: Asegúrate de que este archivo contiene tu conexión a la base de datos ($conn)

// Obtener los datos enviados en la solicitud PUT
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Verificar si $conn se estableció correctamente después del include
    if (!$conn) {
        http_response_code(500);
        // No envíes mysqli_connect_error() directamente en producción por seguridad
        error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
        echo json_encode(["message" => "Error interno del servidor: no se pudo conectar a la base de datos."]);
        exit();
    }

    if (isset($data['id']) && !empty($data['id'])) {
        $id = $data['id'];
        $updates = [];
        $params = [];
        $types = "";

        // Campos permitidos para actualizar
        $allowed_fields = ['nombre', 'apellido', 'email', 'telefono', 'rol'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
                $types .= "s"; // Asumimos que todos los campos son strings, ajusta si es necesario (i: integer, d: double, b: blob)
            }
        }

        if (count($updates) > 0) {
            $params[] = $id;
            $types .= "i"; // El ID es un entero

            $sql = "UPDATE usuarios SET " . implode(", ", $updates) . " WHERE id = ?";

            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                http_response_code(500);
                // No envíes $conn->error directamente en producción
                error_log("Error al preparar la consulta UPDATE: " . $conn->error . " SQL: " . $sql);
                echo json_encode(["message" => "Error al preparar la consulta para actualizar."]);
                exit();
            }

            // Usar call_user_func_array para bind_param con un número variable de parámetros
            // Necesitas pasar los parámetros por referencia para bind_param
            $bind_params = [&$types];
            foreach ($params as $key => $value) {
                $bind_params[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_params);


            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    http_response_code(200);
                    echo json_encode(["message" => "Usuario actualizado correctamente."]);
                } else {
                    // Podría ser 200 OK si no hubo error pero no se afectaron filas (datos iguales o ID no encontrado)
                    // O 404 si quieres ser específico sobre el ID no encontrado.
                    // Para simplificar, si no hay error de ejecución pero no hay filas afectadas,
                    // puede ser que el usuario no exista o los datos eran los mismos.
                    http_response_code(200); // O 404 si prefieres
                    echo json_encode(["message" => "No se encontró el usuario o no hubo cambios para actualizar."]);
                }
            } else {
                http_response_code(500);
                // No envíes $stmt->error directamente en producción
                error_log("Error al ejecutar la actualización del usuario: " . $stmt->error);
                echo json_encode(["message" => "Error al actualizar el usuario."]);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(["message" => "No se proporcionaron datos para actualizar."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID de usuario no proporcionado o vacío."]);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(["message" => "Método no permitido."]);
}

if ($conn) { // Solo cierra si la conexión fue exitosa
    $conn->close();
}
?>