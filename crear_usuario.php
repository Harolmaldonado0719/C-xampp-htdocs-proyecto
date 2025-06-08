<?php
header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de que este archivo establece $conn correctamente
ob_clean(); // Limpia cualquier salida previa que pueda interferir

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Solo se permiten peticiones POST']);
    exit;
}

// Validar campos de texto obligatorios
// Como no hay subida de archivos, solo esperamos datos de $_POST
if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Faltan datos obligatorios: nombre, email y password son requeridos.']);
    exit;
}

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$fotografiaPath = null; // Siempre será null ya que no procesamos subida de archivos

// Verificar si el correo ya existe
$sql_check_email = "SELECT id FROM usuarios WHERE email = ?";
$stmt_check = mysqli_prepare($conn, $sql_check_email);
if ($stmt_check) {
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        http_response_code(409); // Conflicto
        echo json_encode(['error' => 'El correo electrónico ya está registrado.']);
        mysqli_stmt_close($stmt_check);
        if ($conn) { mysqli_close($conn); }
        exit;
    }
    mysqli_stmt_close($stmt_check);
} else {
    http_response_code(500);
    error_log("Error al preparar la consulta para verificar email: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error interno del servidor al verificar el email.']);
    if ($conn) { mysqli_close($conn); }
    exit;
}

// Insertar datos en la base de datos
// La columna 'fotografia' en tu tabla 'usuarios' debe permitir valores NULL.
// La columna 'fecha_registro' se asume que tiene un valor predeterminado o se maneja con NOW().
$sql = "INSERT INTO usuarios (nombre, email, password, fotografia, fecha_registro) 
        VALUES (?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // 'ssss' indica que los cuatro parámetros son strings.
    // $fotografiaPath es null.
    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password_hash, $fotografiaPath);
    
    if (mysqli_stmt_execute($stmt)) {
        http_response_code(201); // Creado
        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente', 'id' => mysqli_insert_id($conn)]);
    } else {
        http_response_code(500);
        error_log("Error al guardar en la base de datos: " . mysqli_stmt_error($stmt));
        echo json_encode(['error' => 'Error al guardar los datos del usuario.']);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    error_log("Error al preparar la consulta INSERT: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error al preparar la solicitud a la base de datos.']);
}

if ($conn) {
    mysqli_close($conn);
}
?>