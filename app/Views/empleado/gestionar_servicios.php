<?php
// Variables esperadas: $pageTitle, $active_page, $empleado, $todosLosServicios, $serviciosAsignadosIds
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

    <?php include __DIR__ . '/../../partials/mensajes.php'; ?>

    <p>Selecciona los servicios que <strong><?php echo htmlspecialchars($empleado['nombre'] . " " . ($empleado['apellido'] ?? '')); ?></strong> puede realizar.</p>

    <?php if (!empty($todosLosServicios)): ?>
        <form action="<?php echo BASE_URL; ?>admin/empleado/<?php echo $empleado['id']; ?>/servicios/guardar" method="POST">
            <div class="list-group mb-3">
                <?php foreach ($todosLosServicios as $servicio): ?>
                    <label class="list-group-item">
                        <input class="form-check-input me-1" type="checkbox" name="servicios[]" value="<?php echo $servicio['id']; ?>"
                            <?php echo in_array($servicio['id'], $serviciosAsignadosIds) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($servicio['nombre']); ?>
                        (<?php echo htmlspecialchars($servicio['duracion_minutos']); ?> min - $<?php echo htmlspecialchars(number_format($servicio['precio'], 2)); ?>)
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-secondary">Volver a Usuarios</a> <!-- Ajustado para ir al dashboard o lista de usuarios -->
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            No hay servicios disponibles en el sistema para asignar. Por favor, <a href="<?php echo BASE_URL; ?>admin/servicios/crear">crea servicios primero</a>.
        </div>
        <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-secondary">Volver a Usuarios</a> <!-- Ajustado para ir al dashboard o lista de usuarios -->
    <?php endif; ?>
</div>