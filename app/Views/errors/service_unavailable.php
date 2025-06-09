<?php
// Este es el único comentario filepath necesario, al inicio del bloque PHP.
// Si tienes alguna lógica PHP que necesites ejecutar antes del HTML, iría aquí.
?>
<div class="container error-page">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Servicio No Disponible'; ?></h2>
    <p><?php echo isset($mensaje_error) ? htmlspecialchars($mensaje_error) : 'El servicio no está disponible actualmente. Por favor, inténtalo más tarde.'; ?></p>
    <p><a href="<?php echo BASE_URL; ?>">Volver al inicio</a></p>
</div>