<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    // Si ya está logueado, redirige directo al dashboard
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Bienvenido - Clip Techs Sistem</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(135deg, #6dd5ed, #2193b0, #f7971e, #ffd200);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        margin: 0;
        display: flex;
        flex-direction: column;
        height: 100vh;
        justify-content: center;
        align-items: center;
        color: #fff;
        text-align: center;
    }
    @keyframes gradientBG {
        0% {background-position: 0% 50%;}
        50% {background-position: 100% 50%;}
        100% {background-position: 0% 50%;}
    }
    h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        text-shadow: 2px 2px 8px #00000055;
        user-select: none;
    }
    .buttons {
        display: flex;
        gap: 20px;
    }
    a.button {
        background: rgba(255,255,255,0.9);
        color: #2193b0;
        font-weight: 700;
        padding: 15px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px #00000033;
        transition: background 0.3s, color 0.3s;
        user-select: none;
    }
    a.button:hover {
        background: #2193b0;
        color: #fff;
    }
    footer {
        position: fixed;
        bottom: 10px;
        width: 100%;
        text-align: center;
        font-weight: bold;
        letter-spacing: 1px;
        font-size: 16px;
        color: #222;
        user-select: none;
        text-shadow: 1px 1px 2px #fff;
    }
</style>
</head>
<body>
    <h1>Bienvenido a Clip Techs Sistem</h1>
    <div class="buttons">
        <a href="login.php" class="button">Iniciar Sesión</a>
        <a href="registro.php" class="button">Registrarse</a>
    </div>
    <footer>&copy; Harol Maldonado 2025</footer>
</body>
</html>
