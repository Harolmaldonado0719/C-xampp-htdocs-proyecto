<?php
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cerrando sesión...</title>
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
    <footer>
        &copy; Harol Maldonado 2025
    </footer>
</body>
</html>