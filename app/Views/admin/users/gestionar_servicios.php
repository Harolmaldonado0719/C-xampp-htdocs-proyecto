<?php
// Variables esperadas desde AdminController::gestionarServiciosEmpleadoForm():
// $pageTitle (string) - Título de la página
// $active_page (string) - Para el menú activo
// $empleado (array) - Datos del empleado (id, nombre, apellido)
// $todosLosServicios (array) - Lista de todos los servicios activos (id, nombre)
// $serviciosAsignadosIds (array) - Lista de IDs de los servicios ya asignados al empleado
// $viewPath (string) - Ruta a esta vista (usada por el layout)
?>
<div class="container mt-4">
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Gestionar Servicios del Empleado'; ?></h2>

    <?php include __DIR__ . '/../../partials/mensajes.php'; // Para mostrar mensajes de éxito/error ?>

    <?php if (isset($empleado) && is_array($empleado)): ?>
        <div class="card mb-4">
            <div class="card-header">
                Empleado: <strong><?php echo htmlspecialchars($empleado['nombre'] . " " . ($empleado['apellido'] ?? '')); ?></strong>
                (ID: <?php echo htmlspecialchars($empleado['id']); ?>)
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>admin/empleado/<?php echo htmlspecialchars($empleado['id']); ?>/servicios/guardar" method="POST">
                    <?php if (isset($todosLosServicios) && !empty($todosLosServicios)): ?>
                        <p>Selecciona los servicios que este empleado puede realizar:</p>
                        <div class="row">
                            <?php foreach ($todosLosServicios as $servicio): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="servicios[]" 
                                               value="<?php echo htmlspecialchars($servicio['id']); ?>" 
                                               id="servicio_<?php echo htmlspecialchars($servicio['id']); ?>"
                                               <?php 
                                               if (isset($serviciosAsignadosIds) && is_array($serviciosAsignadosIds)) {
                                                   echo in_array($servicio['id'], $serviciosAsignadosIds) ? 'checked' : '';
                                               }
                                               ?>>
                                        <label class="form-check-label" for="servicio_<?php echo htmlspecialchars($servicio['id']); ?>">
                                            <?php echo htmlspecialchars($servicio['nombre']); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
                    <?php elseif (isset($todosLosServicios) && empty($todosLosServicios)): ?>
                        <div class="alert alert-info">
                            No hay servicios activos registrados en el sistema para asignar. 
                            <a href="<?php echo BASE_URL; ?>admin/servicios/crear">Crear un servicio</a>.
                        </div>
                    <?php else: ?>
                         <div class="alert alert-warning">
                            No se pudieron cargar los servicios. Verifique la configuración o contacte al administrador.
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>admin/usuarios" class="btn btn-secondary mt-3">Volver a Usuarios</a>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            No se pudo cargar la información del empleado.
        </div>
        <a href="<?php echo BASE_URL; ?>admin/usuarios" class="btn btn-secondary mt-3">Volver a Usuarios</a>
    <?php endif; ?>
</div>