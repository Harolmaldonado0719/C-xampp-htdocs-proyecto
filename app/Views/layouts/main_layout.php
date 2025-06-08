
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Clip techs Sistem' : 'Clip techs Sistem'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>img/logo-pestaña.png" type="image/x-icon">
    <!-- Puedes añadir más meta tags, fuentes, o librerías JS/CSS aquí -->
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>" class="logo-link">
                    <img src="<?php echo BASE_URL; ?>img/logo.png" alt="Clip techs Sistem Logo" class="logo-img">
                    <h1>Clip techs Sistem</h1>
                </a>
            </div>
            <nav class="site-nav">
                <ul>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php // Usuario Logueado ?>
                        <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                            <li><a href="<?php echo BASE_URL; ?>dashboard">Dashboard</a></li>
                        <?php else: // Para 'cliente' u otros roles ?>
                            <li><a href="<?php echo BASE_URL; ?>portal_servicios">Portal de Servicios</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>logout" class="btn-logout">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>)</a></li>
                    <?php else: ?>
                        <?php // Usuario No Logueado ?>
                        <li><a href="<?php echo BASE_URL; ?>login">Iniciar Sesión</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php 
            if (isset($viewPath) && file_exists($viewPath)) {
                include $viewPath;
            } elseif (isset($viewPath)) {
                echo "<p class='message error'>Error: La vista especificada no fue encontrada: " . htmlspecialchars($viewPath) . "</p>";
            }
            ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Clip techs Sistem. Todos los derechos reservados.</p>
            <p>
                <a href="#">Política de Privacidad</a> | 
                <a href="#">Términos de Servicio</a>
            </p>
        </div>
    </footer>

    <!-- Scripts JS (si los tienes) -->
    <!-- <script src="<?php echo BASE_URL; ?>js/main.js"></script> -->
</body>
</html>