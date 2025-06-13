<?php
// Variables esperadas: $pageTitle, $active_page, $servicios
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        <a href="<?php echo BASE_URL; ?>admin/servicios/crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Crear Nuevo Servicio
        </a>
    </div>

    <?php include __DIR__ . '/../../partials/mensajes.php'; ?>

    <?php if (!empty($servicios)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Duración (min)</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $servicio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($servicio['id']); ?></td>
                            <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($servicio['duracion_minutos']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($servicio['precio'], 2)); ?></td>
                            <td>
                                <?php if ($servicio['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/servicios/editar/<?php echo $servicio['id']; ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>admin/servicios/eliminar/<?php echo $servicio['id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres cambiar el estado de este servicio?');">
                                    <button type="submit" class="btn btn-sm btn-warning" title="<?php echo $servicio['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                        <i class="fas fa-<?php echo $servicio['activo'] ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No hay servicios registrados.</div>
    <?php endif; ?>
</div>