<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\empleado\dashboard_empleado.php
// Variables disponibles: $pageTitle, $active_page, $notificaciones_no_leidas_resumen (array)
?>
<div class="dashboard-container">
    <div class="dashboard-header">
        <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Panel de Empleado'; ?></h2>
        <p>Bienvenido/a, <?php echo isset($_SESSION['user_nombre']) ? htmlspecialchars($_SESSION['user_nombre']) : 'Empleado'; ?>.</p>
    </div>
    
    <?php // Sección de Mensajes Globales ?>
    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="message success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_info_global'])): ?>
        <div class="message info"><?php echo htmlspecialchars($_SESSION['mensaje_info_global']); unset($_SESSION['mensaje_info_global']); ?></div>
    <?php endif; ?>

    
    <div class="dashboard-section">
        <h3>Acciones Rápidas</h3>
        <ul class="action-list">
            <li><a href="<?php echo BASE_URL; ?>empleado/agenda" class="btn btn-primary">Ver Mi Agenda</a></li>
            <li><a href="<?php echo BASE_URL; ?>empleado/horarios" class="btn btn-dark">Mis Horarios de Trabajo</a></li> 
            <li><a href="<?php echo BASE_URL; ?>empleado/historial-citas" class="btn btn-info">Historial de Citas</a></li>
            <li><a href="<?php echo BASE_URL; ?>empleado/facturas" class="btn btn-warning">Mis Facturas</a></li> <!-- NUEVO ENLACE -->
            <li><a href="<?php echo BASE_URL; ?>empleado/productos" class="btn btn-success">Gestionar Productos</a></li>
            <li><a href="<?php echo BASE_URL; ?>empleado/reportes" class="btn btn-secondary">Reportes</a></li>
            
        </ul>
    </div>

    <div class="dashboard-section">
        <h3>Información General</h3>
        <p>Desde este panel puedes acceder a tu agenda, consultar tus horarios de trabajo, revisar el historial de tus citas, administrar el catálogo de productos y, próximamente, generar reportes de tu actividad.</p>
    </div>
    
    <?php if (isset($notificaciones_no_leidas_resumen) && !empty($notificaciones_no_leidas_resumen)): ?>
        <div class="dashboard-section notification-section">
            <h4>Notificaciones Pendientes</h4>
            <ul class="notification-list">
                <?php foreach($notificaciones_no_leidas_resumen as $notif): ?>
                    <li title="Recibida: <?php echo isset($notif['fecha_creacion']) ? htmlspecialchars((new DateTime($notif['fecha_creacion']))->format('d/m/Y H:i:s')) : 'Fecha desconocida'; ?>">
                        <a href="<?php echo htmlspecialchars(!empty($notif['url_destino']) ? $notif['url_destino'] : BASE_URL.'notificaciones'); ?>">
                            <?php 
                                $mensajeCorto = mb_substr($notif['mensaje'], 0, 70);
                                echo htmlspecialchars($mensajeCorto);
                                if (mb_strlen($notif['mensaje']) > 70) {
                                    echo "...";
                                }
                            ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?php echo BASE_URL; ?>notificaciones" class="btn btn-warning btn-sm mt-2">Ver todas las notificaciones</a>
        </div>
    <?php elseif (isset($notificaciones_no_leidas_resumen) && empty($notificaciones_no_leidas_resumen)): ?>
        <div class="dashboard-section info-section">
            <p>No tienes notificaciones nuevas.</p>
        </div>
    <?php endif; ?>
    
    <?php
    // Ejemplo de cómo podrías mostrar un resumen de próximas citas (si pasas $proximas_citas_resumen desde el controlador)
    /*
    if (isset($proximas_citas_resumen) && !empty($proximas_citas_resumen)) {
        // ... (código para mostrar próximas citas) ...
    }
    */
    ?>
</div>

<style>
/* Estilos Comunes para Dashboards (Idealmente en un archivo CSS externo) */
body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
.dashboard-container { max-width: 960px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.dashboard-header { border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px; }
.dashboard-header h2 { margin-top: 0; color: #333; font-size: 1.8em; }
.dashboard-header p { color: #555; font-size: 0.95em; }

.message { padding: 12px 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 5px; font-size: 0.9em; }
.message.success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
.message.error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
.message.info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }

.dashboard-section { margin-bottom: 20px; padding: 20px; background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px; }
.dashboard-section h3 { margin-top: 0; color: #0056b3; font-size: 1.4em; margin-bottom: 15px; }
.dashboard-section h4 { margin-top: 0; color: #333; font-size: 1.2em; margin-bottom: 10px; }
.dashboard-section p { color: #666; line-height: 1.6; }
.dashboard-section ul { list-style: none; padding: 0; margin: 0; }
.dashboard-section ul.action-list li, 
.dashboard-section ul.notification-list li { margin-bottom: 10px; }
.dashboard-section ul.notification-list a { color: #0056b3; text-decoration: none; }
.dashboard-section ul.notification-list a:hover { text-decoration: underline; }

.notification-section { background-color: #fff3cd; border-color: #ffeeba; }
.notification-section h4 { color: #856404; } /* Texto del título de notificaciones */
.notification-section ul.notification-list a { color: #856404; } /* Enlaces dentro de la lista de notificaciones */
.notification-section .btn-warning { /* Estilo específico para el botón "Ver todas las notificaciones" */
    color: #856404; /* Color de texto del botón */
    background-color: #ffeeba; /* Fondo del botón, puede ser transparente o un color que combine */
    border-color: #856404; /* Borde del botón */
}
.notification-section .btn-warning:hover {
    background-color: #ffda6a; /* Un color ligeramente más oscuro para el hover */
}

.info-section { background-color: #e2e3e5; border-color: #d6d8db; color: #383d41; }
.info-section p { margin:0; }

.btn { display: inline-block; padding: 10px 18px; font-size: 0.95em; font-weight: bold; color: #fff; background-color: #007bff; border: none; border-radius: 5px; text-align: center; text-decoration: none; transition: background-color 0.3s ease; cursor: pointer; margin-right: 5px; margin-bottom: 5px; }
.btn:hover { opacity: 0.9; }
.btn-primary { background-color: #007bff; }
.btn-secondary { background-color: #6c757d; }
.btn-success { background-color: #28a745; }
.btn-info { background-color: #17a2b8; }
.btn-warning { background-color: #ffc107; color: #212529; } /* Estilo general para btn-warning */
.btn-danger { background-color: #dc3545; }
.btn-dark { background-color: #343a40; } /* Estilo para el nuevo botón */
.btn-sm { padding: 6px 12px; font-size: 0.85em; }
.mt-2 { margin-top: 0.5rem !important; } /* Clase de utilidad para margen superior */

.stat-card-container { display: flex; justify-content: space-around; flex-wrap: wrap; margin-bottom: 20px; gap: 15px; }
.stat-card { background-color: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex: 1; min-width: 200px; }
.stat-card h4 { font-size: 1em; margin-top:0; margin-bottom: 8px; color: #495057; }
.stat-card p { font-size: 2.2em; margin:0; color: #0056b3; font-weight: bold; }
.badge { display: inline-block; padding: .35em .65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .375rem; margin-left: 5px;}
.bg-danger { background-color: #dc3545 !important; }
</style>