
<?php
// Este es el único comentario filepath necesario, al inicio del bloque PHP.
// Si tienes alguna lógica PHP que necesites ejecutar antes del HTML, iría aquí.
?>
<div class="error-container" style="text-align: center; padding: 40px;">
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : '404 - Página No Encontrada'; ?></h1>
    <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
    <?php if (isset($route) && !empty($route)): ?>
        <p>Ruta intentada: <code>/<?php echo htmlspecialchars($route); ?></code></p>
    <?php endif; ?>
    <p><a href="<?php echo BASE_URL; ?>">Volver a la página de inicio</a></p>
</div>