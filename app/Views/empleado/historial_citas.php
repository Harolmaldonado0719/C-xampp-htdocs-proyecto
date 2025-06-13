<?php
// Variables disponibles:
// $pageTitle (string) - Título de la página
// $citas (array) - Array de citas
// $active_page (string) - Para marcar el item activo en el sidebar/nav
// $valores_filtros (array) - Valores actuales de los filtros para rellenar el form
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php 
            // Incluir el sidebar del empleado si existe
            $sidebarPath = __DIR__ . '/../layouts/sidebar_empleado.php';
            if (file_exists($sidebarPath)) {
                include $sidebarPath;
            }
            ?>
        </div>
        <div class="col-md-9">
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <hr>

            <!-- Formulario de Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtrar Historial
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo BASE_URL; ?>empleado/historial-citas" class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_desde" class="form-label">Desde Fecha:</label>
                            <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($valores_filtros['fecha_desde'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_hasta" class="form-label">Hasta Fecha:</label>
                            <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($valores_filtros['fecha_hasta'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado:</label>
                            <select class="form-select form-select-sm" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Pendiente" <?php echo (isset($valores_filtros['estado']) && $valores_filtros['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Confirmada" <?php echo (isset($valores_filtros['estado']) && $valores_filtros['estado'] == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                <option value="Cancelada" <?php echo (isset($valores_filtros['estado']) && $valores_filtros['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                <option value="Completada" <?php echo (isset($valores_filtros['estado']) && $valores_filtros['estado'] == 'Completada') ? 'selected' : ''; ?>>Completada</option>
                                <option value="No Asistio" <?php echo (isset($valores_filtros['estado']) && $valores_filtros['estado'] == 'No Asistio') ? 'selected' : ''; ?>>No Asistió</option>
                            </select>
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Filtrar</button>
                            <a href="<?php echo BASE_URL; ?>empleado/historial-citas" class="btn btn-secondary btn-sm"><i class="fas fa-times me-1"></i>Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Citas -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Listado de Citas
                </div>
                <div class="card-body">
                    <?php if (empty($citas)): ?>
                        <div class="alert alert-info" role="alert">
                            No se encontraron citas para los filtros seleccionados o no tienes citas en tu historial.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha Cita</th>
                                        <th>Hora Cita</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th>Estado</th>
                                        <th>Notas Cliente</th>
                                        <th>Notas Empleado</th>
                                        <th>Creada</th>
                                        <th>Actualizada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citas as $cita): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cita['id']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['fecha_formateada'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($cita['hora_formateada'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($cita['cliente_nombre'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    switch ($cita['estado_cita']) {
                                                        case 'Pendiente': echo 'warning text-dark'; break;
                                                        case 'Confirmada': echo 'success'; break;
                                                        case 'Cancelada': echo 'danger'; break;
                                                        case 'Completada': echo 'primary'; break;
                                                        case 'No Asistio': echo 'secondary'; break;
                                                        default: echo 'light text-dark'; break;
                                                    }
                                                ?>"><?php echo htmlspecialchars($cita['estado_cita']); ?></span>
                                            </td>
                                            <td><?php echo nl2br(htmlspecialchars($cita['notas_cliente'] ?? '')); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($cita['notas_empleado'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars($cita['fecha_creacion_formateada'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($cita['fecha_actualizacion_formateada'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>