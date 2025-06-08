
<div class="portal-container">
    <div class="portal-header">
        <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Portal de Servicios'; ?></h1>
    </div>

    <?php if (isset($mensaje_error_portal) && !empty($mensaje_error_portal)): ?>
        <div class="message error"><?php echo htmlspecialchars($mensaje_error_portal); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_exito_portal'])): // Para futuros mensajes de éxito ?>
        <div class="message success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_portal']); unset($_SESSION['mensaje_exito_portal']); ?></div>
    <?php endif; ?>


    <p class="portal-welcome">Bienvenido a tu portal, <?php echo isset($usuario_nombre) ? htmlspecialchars($usuario_nombre) : 'Usuario'; ?>.</p>

    <div class="portal-content">
        <h2>Tus Servicios y Contenido</h2>
        <p>Esta es tu área personal de servicios.</p>
        <p>Aquí podrías ver información sobre tus suscripciones, descargar archivos, acceder a contenido exclusivo, etc.</p>
        
        <!-- Ejemplo de cómo podrías listar servicios si los tuvieras -->
        <?php /*
        if (!empty($servicios)) {
            echo "<h3>Mis Servicios Activos:</h3>";
            echo "<ul>";
            foreach ($servicios as $servicio) {
                echo "<li>" . htmlspecialchars($servicio['nombre_servicio']) . " - Estado: " . htmlspecialchars($servicio['estado']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aún no tienes servicios activos.</p>";
        }
        */ ?>
    </div>
</div>

<?php
// Puedes añadir estilos específicos para el portal aquí o, preferiblemente, en tu style.css principal
// Si los pones aquí, asegúrate de que estén dentro de etiquetas <style>
/*
<style>
.portal-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.portal-header h1 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}
.portal-welcome {
    font-size: 1.1em;
    color: #555;
    margin-bottom: 20px;
}
.portal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
}
.portal-content h2 {
    margin-top: 0;
    color: #007bff;
}
</style>
*/
?>