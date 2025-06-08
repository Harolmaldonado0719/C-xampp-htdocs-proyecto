<?php
session_start();
include 'conexion.php';
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Verificar si el correo ya existe
    $stmt = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $mensaje = "El correo ya está registrado";
    } else {
        // SUBIR FOTO
        $ruta_destino = null;
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
            $nombre_imagen = $_FILES['fotografia']['name'];
            $tmp_imagen = $_FILES['fotografia']['tmp_name'];
            $ruta_destino = "uploads/" . uniqid() . "_" . basename($nombre_imagen);

            if (!move_uploaded_file($tmp_imagen, $ruta_destino)) {
                $mensaje = "Error al subir la imagen.";
            }
        }

        if (!$mensaje) {
            // Insertar usuario con campo fotografia
            $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nombre, email, password, fotografia) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password_hash, $ruta_destino);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['usuario_id'] = mysqli_insert_id($conn);
                $_SESSION['usuario_nombre'] = $nombre;
                header("Location: dashboard.php");
                exit;
            } else {
                $mensaje = "Error al registrar: " . mysqli_error($conn);
            }
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            text-align: center;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #6dd5ed, #2193b0, #f7971e, #ffd200);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        h2 {
            color: #fff;
            margin-top: 60px;
            text-shadow: 2px 2px 8px #00000055;
        }
        form {
            display: inline-block;
            text-align: left;
            background: rgba(255,255,255,0.9);
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 16px #00000022;
        }
        input, button {
            padding: 10px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%);
            color: #fff;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #0056b3 60%, #00aaff 100%);
            transform: scale(1.04);
        }
        .msg { color: #d8000c; font-weight: bold; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        footer {
            margin-top: auto;
            padding: 20px 0 10px 0;
            background: rgba(0,0,0,0.15);
            color: #222;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 16px;
            box-shadow: 0 -2px 8px #00000022;
        }
    </style>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <?php if ($mensaje) echo "<p class='msg'>$mensaje</p>"; ?>
    <form action="registro.php" method="POST" enctype="multipart/form-data">
        <label>Nombre:</label>
        <input type="text" name="nombre" required><br>
        <label>Correo electrónico:</label>
        <input type="email" name="email" required><br>
        <label>Contraseña:</label>
        <input type="password" name="password" required><br>
        <label>Foto de perfil:</label>
        <input type="file" name="fotografia" accept="image/*"><br>
        <button type="submit">Registrar</button>
    </form>
    <p><a href="index.php">Volver al inicio</a></p>
    <footer>
        &copy; Harol Maldonado 2025
    </footer>
</body>
</html>
