
<!-- filepath: app/Views/atencion_cliente/mis_solicitudes.php -->
<h1><?php echo htmlspecialchars($pageTitle ?? "Mis Solicitudes"); ?></h1>

<?php 
// Mensaje global de error/éxito (si se usa $_SESSION['mensaje_error_global'] o $_SESSION['mensaje_exito_global'] desde el controlador)
if (isset($_SESSION['mensaje_error_global'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_exito_global'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
<?php endif; ?>

<p><a href="<?php echo BASE_URL; ?>atencion-cliente/nueva" class="btn btn-success" style="margin-bottom: 15px;">Enviar Nueva Solicitud</a></p>

<?php if (empty($solicitudes)): ?>
    <p>No has enviado ninguna solicitud de atención todavía.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table-solicitudes">
            <thead>
                <tr>
                    <th>ID Solicitud</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Última Actualización</th>
                    <th>Respuesta del Administrador</th> <!-- Nueva columna -->
                    <!-- <th>Acciones</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $solicitud): ?>
                    <tr>
                        <td>PQR-<?php echo htmlspecialchars($solicitud['id']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['tipo_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['asunto']); ?></td>
                        <td><span class="status status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $solicitud['estado']))); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></td>
                        <td><?php echo $solicitud['fecha_actualizacion'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion']))) : 'N/A'; ?></td>
                        <td>
                            <?php // Mostrar respuesta solo si existe y el estado es relevante (ej. Resuelta) o siempre si existe
                                if (!empty($solicitud['respuesta_admin'])):
                                    echo nl2br(htmlspecialchars($solicitud['respuesta_admin'])); // nl2br para respetar saltos de línea
                                elseif (in_array($solicitud['estado'], ['Resuelta', 'Cerrada']) && empty($solicitud['respuesta_admin'])):
                                    echo '<i>(Resuelta sin comentarios adicionales)</i>';
                                else:
                                    echo '<i>(Pendiente de respuesta o no aplica)</i>';
                                endif;
                            ?>
                        </td>
                        <!-- <td>
                            <a href="<?php echo BASE_URL . 'atencion-cliente/ver/' . htmlspecialchars($solicitud['id']); ?>" class="btn btn-sm btn-info">Ver Detalle</a>
                        </td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<style>
    .table-responsive { overflow-x: auto; }
    .table-solicitudes { width: 100%; border-collapse: collapse; margin-top: 20px; min-width: 750px; /* Ajustado para nueva columna */ }
    .table-solicitudes th, .table-solicitudes td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; /* Para respuestas largas */ }
    .table-solicitudes th { background-color: #f2f2f2; }
    .status { padding: 3px 7px; border-radius: 4px; color: white; font-size: 0.9em; display: inline-block; }
    .status-abierta { background-color: #007bff; } /* Azul */
    .status-en-proceso { background-color: #ffc107; color: #212529;} /* Amarillo */
    .status-resuelta { background-color: #28a745; } /* Verde */
    .status-cerrada { background-color: #6c757d; } /* Gris */
    /* Añade más clases de estado si es necesario */

    .alert { padding: .75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: .25rem; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
</style>
