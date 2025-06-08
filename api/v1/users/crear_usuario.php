<?php
header('Content-Type: application/json');
// Permitir CORS si es necesario para pruebas desde diferentes orígenes (ajusta en producción)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ob_start(); // Iniciar buffer de salida para limpiar si es necesario

// Incluir configuración y clase de base de datos
// La ruta es desde api/v1/users/ hasta app/
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/Core/Database.php';

$db = null; // Para asegurar que se pueda cerrar en el bloque finally si es necesario

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    ob_end_clean(); // Limpiar buffer antes de enviar error
    http_response_code(503); // Service Unavailable
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

ob_clean(); // Limpia cualquier salida previa (como warnings de conexión si los hubo antes del try-catch)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Solo se permiten peticiones POST']);
    if ($db) $db->close();
    exit;
}

// Validar campos de texto obligatorios
if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Faltan datos obligatorios: nombre, email y password son requeridos.']);
    if ($db) $db->close();
    exit;
}

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password_input = $_POST['password']; // No hacer trim a la contraseña directamente

if (empty($nombre) || empty($email) || empty($password_input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre, email y password no pueden estar vacíos.']);
    if ($db) $db->close();
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de correo electrónico inválido.']);
    if ($db) $db->close();
    exit;
}


$password_hash = password_hash($password_input, PASSWORD_DEFAULT);
$fotografiaPath = null; // Siempre será null ya que no procesamos subida de archivos aquí

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
        if ($db) $db->close();
        exit;
    }
    mysqli_stmt_close($stmt_check);
} else {
    http_response_code(500);
    error_log("API Error al preparar la consulta para verificar email: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error interno del servidor al verificar el email.']);
    if ($db) $db->close();
    exit;
}

// Insertar datos en la base de datos
$sql = "INSERT INTO usuarios (nombre, email, password, fotografia, fecha_registro) 
        VALUES (?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password_hash, $fotografiaPath);
    
    if (mysqli_stmt_execute($stmt)) {
        http_response_code(201); // Creado
        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente', 'id' => mysqli_insert_id($conn)]);
    } else {
        http_response_code(500);
        error_log("API Error al guardar en la base de datos: " . mysqli_stmt_error($stmt));
        echo json_encode(['error' => 'Error al guardar los datos del usuario.']);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    error_log("API Error al preparar la consulta INSERT: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error al preparar la solicitud a la base de datos.']);
}

if ($db) {
    $db->close(); // La conexión se cierra a través del objeto Database
}
?>