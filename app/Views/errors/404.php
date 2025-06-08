<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\errors\404.php
<div class="error-container" style="text-align: center; padding: 40px;">
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : '404 - Página No Encontrada'; ?></h1>
    <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
    <?php if (isset($route) && !empty($route)): ?>
        <p>Ruta intentada: <code>/<?php echo htmlspecialchars($route); ?></code></p>
    <?php endif; ?>
    <p><a href="<?php echo BASE_URL; ?>">Volver a la página de inicio</a></p>
</div>