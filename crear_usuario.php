<?php
header('Content-Type: application/json');
require 'conexion.php';
ob_clean(); // limpia cualquier salida previa que pueda interferir

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Solo se permiten peticiones POST']);
    exit;
}

// Validar campos enviados
if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

$nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encripta la contraseña

// Procesar la imagen
if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['fotografia']['tmp_name'];
    $fileName = $_FILES['fotografia']['name'];
    $fileSize = $_FILES['fotografia']['size'];
    $fileType = $_FILES['fotografia']['type'];

    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Extensiones permitidas
    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Crear nombre único para evitar sobreescritura
        $newFileName = uniqid('img_', true) . '.' . $fileExtension;

        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            // Insertar datos en la base de datos
            $sql = "INSERT INTO usuarios (nombre, email, password, fotografia, fecha_registro) 
                    VALUES ('$nombre', '$email', '$password', '$newFileName', NOW())";

            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
            } else {
                // Si error en insert, eliminar archivo subido para no dejar basura
                unlink($dest_path);
                echo json_encode(['error' => 'Error al guardar en la base de datos: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['error' => 'Error moviendo el archivo']);
        }
    } else {
        echo json_encode(['error' => 'Tipo de archivo no permitido']);
    }
} else {
    echo json_encode(['error' => 'No se recibió archivo o hubo un error']);
}
?>
