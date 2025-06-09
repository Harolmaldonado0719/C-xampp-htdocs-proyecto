
<div class="container">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Panel de Empleado'; ?></h2>
    <p>Bienvenido/a al panel de empleado, <?php echo isset($_SESSION['user_nombre']) ? htmlspecialchars($_SESSION['user_nombre']) : 'Empleado'; ?>.</p>
    
    <div class="empleado-actions">
        <h3>Acciones Rápidas</h3>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>empleado/citas/ver">Ver Mis Citas Programadas</a></li>
            <li><a href="<?php echo BASE_URL; ?>empleado/servicios/asignados">Ver Servicios Asignados</a></li>
            <li><a href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
            <!-- Añade más enlaces a funcionalidades específicas del empleado -->
        </ul>
    </div>

    <div class="empleado-info">
        <h3>Información General</h3>
        <p>Aquí podrás gestionar tus citas, ver los servicios que tienes asignados y actualizar tu información personal.</p>
        <!-- Aquí podrías mostrar un resumen de citas pendientes, etc. -->
        <?php
        /*
        if (isset($citasPendientes) && !empty($citasPendientes)) {
            echo "<h4>Citas Pendientes:</h4>";
            echo "<ul>";
            foreach ($citasPendientes as $cita) {
                echo "<li>" . htmlspecialchars($cita['descripcion']) . " - " . htmlspecialchars($cita['fecha_hora']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No tienes citas pendientes.</p>";
        }
        */
        ?>
    </div>
</div>