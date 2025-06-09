
<div class="portal-container">
    <div class="portal-header">
        <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Portal de Servicios'; ?></h1>
    </div>

    <?php if (isset($_SESSION['mensaje_error_global']) && !empty($_SESSION['mensaje_error_global'])): // Mensaje global si es necesario ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_error_portal) && !empty($mensaje_error_portal)): // Mensaje específico del portal ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($mensaje_error_portal); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_exito_portal'])): // Para futuros mensajes de éxito ?>
        <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito_portal']); unset($_SESSION['mensaje_exito_portal']); ?></div>
    <?php endif; ?>


    <p class="portal-welcome">Bienvenido a tu portal, <?php echo isset($usuario_nombre) ? htmlspecialchars($usuario_nombre) : 'Usuario'; ?>.</p>

    <div class="portal-actions" style="margin-bottom: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px;">
        <h3>Tu Cuenta</h3>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>cliente/servicios">Mis Servicios Contratados</a></li> <!-- Ejemplo de enlace -->
            <li><a href="<?php echo BASE_URL; ?>cliente/pedidos">Mis Pedidos</a></li> <!-- Ejemplo de enlace -->
            <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li> <!-- Enlace a Mi Perfil -->
            <!-- Puedes añadir más enlaces a acciones de cliente aquí -->
        </ul>
    </div>

    <div class="portal-content">
        <h2>Tus Servicios y Contenido</h2>
        <p>Esta es tu área personal de servicios.</p>
        <p>Aquí podrías ver información sobre tus suscripciones, descargar archivos, acceder a contenido exclusivo, etc.</p>
        
        <?php /*
        // Ejemplo de cómo podrías listar servicios si los tuvieras y los pasaras desde el controlador
        if (isset($servicios_cliente) && !empty($servicios_cliente)) {
            echo "<h3>Mis Servicios Activos:</h3>";
            echo "<ul>";
            foreach ($servicios_cliente as $servicio) {
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
.portal-actions ul {
    list-style: none;
    padding: 0;
}
.portal-actions li {
    margin-bottom: 8px;
}
.portal-actions a {
    text-decoration: none;
    color: #007bff;
}
.portal-actions a:hover {
    text-decoration: underline;
}
</style>
*/
?>