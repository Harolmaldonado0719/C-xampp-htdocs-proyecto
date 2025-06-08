<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permite el acceso desde cualquier origen
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'conexion.php'; // Asegúrate de que este archivo establece $conn correctamente

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Obtener los datos enviados en la solicitud (si los hay, para el ID)
    $data = json_decode(file_get_contents("php://input"), true);
    $id = null;

    // Verificar si el ID se envió en el cuerpo JSON
    if (isset($data['id']) && !empty($data['id'])) {
        $id = $data['id'];
    } 
    // Opcional: Verificar si el ID se envió como parámetro GET (descomentar si se quiere esta opción)
    /*
    else if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
    }
    */

    if ($id !== null) {
        // Antes de eliminar de la base de datos, podrías querer eliminar el archivo de fotografía si existe.
        // Primero, obtenemos el nombre del archivo de la fotografía del usuario.
        $sql_select_photo = "SELECT fotografia FROM usuarios WHERE id = ?";
        $stmt_select = mysqli_prepare($conn, $sql_select_photo);
        if ($stmt_select) {
            mysqli_stmt_bind_param($stmt_select, "i", $id);
            mysqli_stmt_execute($stmt_select);
            mysqli_stmt_bind_result($stmt_select, $fotografiaNombre);
            mysqli_stmt_fetch($stmt_select);
            mysqli_stmt_close($stmt_select); // Cerrar este statement aquí

            if ($fotografiaNombre) {
                $uploadFileDir = './uploads/';
                $filePath = $uploadFileDir . $fotografiaNombre;
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        // Error al eliminar archivo, loguear pero continuar con la eliminación del usuario (opcional)
                        error_log("No se pudo eliminar el archivo de fotografía: " . $filePath . " para el usuario ID: " . $id);
                    }
                }
            }
        } else {
            error_log("Error al preparar la consulta para obtener la fotografía del usuario ID: " . $id . " - " . mysqli_error($conn));
        }


        // Ahora, eliminar el usuario de la base de datos
        $sql_delete_user = "DELETE FROM usuarios WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete_user);

        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $id);

            if (mysqli_stmt_execute($stmt_delete)) {
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    http_response_code(200); // OK
                    echo json_encode(["message" => "Usuario eliminado correctamente."]);
                } else {
                    // No se afectaron filas, puede que el usuario con ese ID no existiera
                    http_response_code(404); // Not Found
                    echo json_encode(["message" => "No se encontró el usuario con el ID proporcionado."]);
                }
            } else {
                http_response_code(500); // Internal Server Error
                error_log("Error al ejecutar la eliminación del usuario ID: " . $id . " - " . mysqli_stmt_error($stmt_delete));
                echo json_encode(["message" => "Error al eliminar el usuario."]);
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            http_response_code(500); // Internal Server Error
            error_log("Error al preparar la consulta DELETE para el usuario ID: " . $id . " - " . mysqli_error($conn));
            echo json_encode(["message" => "Error al preparar la solicitud para eliminar el usuario."]);
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "ID de usuario no proporcionado o vacío."]);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(["message" => "Método no permitido."]);
}

if ($conn) {
    mysqli_close($conn);
}
?>