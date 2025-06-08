<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = null;

    if (isset($data['id']) && !empty($data['id'])) {
        $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    } elseif (isset($_GET['id']) && !empty($_GET['id'])) { // Permitir ID por GET también
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    }

    if ($id === false || $id === null) { // filter_var devuelve false si falla la validación
        http_response_code(400);
        echo json_encode(["error" => "ID de usuario no proporcionado o inválido."]);
        if ($db) $db->close();
        exit();
    }

    // Obtener el nombre del archivo de la fotografía del usuario para eliminarlo
    $sql_select_photo = "SELECT fotografia FROM usuarios WHERE id = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select_photo);
    $fotografiaNombre = null;

    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id);
        mysqli_stmt_execute($stmt_select);
        mysqli_stmt_bind_result($stmt_select, $fotografiaNombre);
        mysqli_stmt_fetch($stmt_select);
        mysqli_stmt_close($stmt_select);

        if ($fotografiaNombre) {
            // La ruta a la carpeta 'uploads' que está dentro de 'public'
            // __DIR__ es api/v1/users/
            // ../../../ es la raíz del proyecto (Proyecto-clip/)
            // luego public/uploads/
            $filePath = __DIR__ . '/../../../public/uploads/' . $fotografiaNombre;
            
            // Alternativamente, si APP_UPLOAD_DIR está bien definida en config.php
            // y accesible, podrías usarla, pero __DIR__ es más explícito aquí.
            // Ejemplo: $filePath = rtrim(APP_UPLOAD_DIR, '/') . '/' . $fotografiaNombre;
            // (Asegúrate que APP_UPLOAD_DIR apunte a la ruta correcta desde la raíz del sistema)

            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("API: No se pudo eliminar el archivo de fotografía: " . $filePath . " para el usuario ID: " . $id);
                    // No detenemos la ejecución, el usuario aún puede ser eliminado de la BD
                }
            }
        }
    } else {
        error_log("API Error al preparar la consulta para obtener la fotografía del usuario ID: " . $id . " - " . mysqli_error($conn));
    }

    // Eliminar el usuario de la base de datos
    $sql_delete_user = "DELETE FROM usuarios WHERE id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete_user);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        if (mysqli_stmt_execute($stmt_delete)) {
            if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Usuario eliminado correctamente."]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "No se encontró el usuario con el ID proporcionado."]);
            }
        } else {
            http_response_code(500);
            error_log("API Error al ejecutar la eliminación del usuario ID: " . $id . " - " . mysqli_stmt_error($stmt_delete));
            echo json_encode(["error" => "Error al eliminar el usuario."]);
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        http_response_code(500);
        error_log("API Error al preparar la consulta DELETE para el usuario ID: " . $id . " - " . mysqli_error($conn));
        echo json_encode(["error" => "Error al preparar la solicitud para eliminar el usuario."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);
}

if ($db) {
    $db->close();
}
?>