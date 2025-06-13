<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\partials\mensajes.php

if (session_status() == PHP_SESSION_NONE) {
    // session_start(); // Descomenta esto si mensajes.php se puede incluir en contextos donde la sesión aún no está iniciada.
                     // Sin embargo, es mejor asegurarse de que la sesión siempre se inicie en tu front controller (index.php).
}
?>

<?php if (isset($_SESSION['mensaje_exito_global'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 15px; margin-bottom: 15px;">
        <?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['mensaje_exito_global']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['mensaje_error_global'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-top: 15px; margin-bottom: 15px;">
        <?php echo htmlspecialchars($_SESSION['mensaje_error_global']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['mensaje_error_global']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['mensaje_info_global'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-top: 15px; margin-bottom: 15px;">
        <?php echo htmlspecialchars($_SESSION['mensaje_info_global']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['mensaje_info_global']); ?>
<?php endif; ?>

<?php
// También puedes tener mensajes específicos del formulario si los pasas a la vista
// Por ejemplo, si $form_errors es un array de errores específicos del formulario
if (isset($form_errors) && !empty($form_errors) && is_array($form_errors)):
    foreach ($form_errors as $field => $error_message):
        if (!empty($error_message)): // Asegurarse de que el mensaje de error no esté vacío
?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-top: 10px; margin-bottom: 10px;">
                <strong>Error en <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $field))); ?>:</strong> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
<?php
        endif;
    endforeach;
endif;
?>