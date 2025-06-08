<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexion.php';

// Consulta preparada que incluye el campo de la foto
$stmt = mysqli_prepare($conn, "SELECT id, nombre, email, fecha_registro, fotografia FROM usuarios");
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $nombre, $email, $fecha_registro, $fotografia);

$usuarios = [];
while (mysqli_stmt_fetch($stmt)) {
    $usuarios[] = [
        'id' => $id,
        'nombre' => $nombre,
        'email' => $email,
        'fecha_registro' => $fecha_registro,
        'fotografia' => $fotografia
    ];
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard de Usuarios</title>
    <style>
        /* Reset y base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #6dd5ed, #2193b0, #f7971e, #ffd200);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* Menú lateral */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background: #222d40cc; /* fondo semitransparente oscuro */
            box-shadow: 3px 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            padding-top: 40px;
            z-index: 100;
        }
        nav h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1.2px;
            font-size: 22px;
            text-shadow: 1px 1px 3px #00000099;
        }
        nav a {
            color: #ddd;
            text-decoration: none;
            padding: 14px 20px;
            font-size: 16px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        nav a:hover {
            background-color: #2193b0;
            color: #fff;
            border-left: 4px solid #f7971e;
            cursor: pointer;
        }
        nav a.active {
            background-color: #f7971e;
            color: #222;
            border-left: 4px solid #ffd200;
        }

        /* Contenido principal */
        main {
            margin-left: 220px;
            padding: 30px 40px 40px 40px;
            flex-grow: 1;
            overflow-y: auto;
            min-height: 100vh;
            background: rgba(255,255,255,0.9);
            box-shadow: inset 0 0 40px #0000001a;
            border-radius: 0 12px 12px 0;
        }

        h2.bienvenida {
            color: #222;
            margin-bottom: 10px;
            font-weight: 700;
        }

        h3 {
            color: #333;
            margin: 20px 0 20px 0;
            font-weight: 600;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px #00000022;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 700;
            color: #444;
        }
        img.foto-usuario {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #2193b0;
        }

        footer {
            margin-top: 40px;
            text-align: center;
            color: #555;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
            padding-bottom: 10px;
            user-select: none;
        }

        /* Botón cerrar sesión en menú */
        nav form {
            margin-top: auto;
            padding: 20px;
        }
        nav button.logout {
            width: 100%;
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 12px 0;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        nav button.logout:hover {
            background: #b52a37;
        }
    </style>
</head>
<body>
    <nav>
        <h2>Clip Techs</h2>
        <a href="consulta.php">📋 consulta API</a>
       <a href="registro.php">➕ Registrar Usuario</a>
        <form action="logout.php" method="post">
            <button class="logout" type="submit">Cerrar Sesión</button>
        </form>
    </nav>
    <main>
        <h2 class="bienvenida">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
        <h3>Usuarios Registrados</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha de Registro</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['fecha_registro']); ?></td>
                    <td>
                        <?php
$ruta = $usuario['fotografia']; // ya incluye uploads/ en la BD

if (!empty($ruta) && file_exists($ruta)) {
    echo '<img src="' . htmlspecialchars($ruta) . '" alt="Foto de ' . htmlspecialchars($usuario['nombre']) . '" class="foto-usuario">';
} else {
    echo '<span>Sin foto o ruta incorrecta</span>';
}
?>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <footer>
            &copy; Harol Maldonado 2025
        </footer>
    </main>
</body>
</html>
