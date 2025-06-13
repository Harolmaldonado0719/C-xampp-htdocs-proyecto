<?php
// Mensajes de sesión
$mensaje_exito = $_SESSION['mensaje_exito_global'] ?? null;
$mensaje_error = $_SESSION['mensaje_error_global'] ?? null;
unset($_SESSION['mensaje_exito_global'], $_SESSION['mensaje_error_global']);

// Definir los posibles estados y sus clases de badge para consistencia
$estados_clases_badge = [
    'pendiente' => 'warning text-dark',
    'confirmada' => 'primary',
    'completada' => 'success',
    'cancelada empleado' => 'danger',
    'cancelada cliente' => 'danger', // Aunque el empleado no la ponga, puede venir así
    'no asistió' => 'dark',
    'reprogramada' => 'info', 
];

// Estados que el empleado puede asignar desde el modal
$estadosPosiblesParaEmpleado = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada Empleado', 'No Asistió'];

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars($pageTitle ?? 'Mi Agenda de Citas'); ?></h1>
        <!-- Espacio para futuros botones, como "Agendar Nueva Cita" si se implementa -->
    </div>

    <?php if ($mensaje_exito): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($mensaje_exito); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if ($mensaje_error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($mensaje_error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Filtros de Fecha -->
    <form method="GET" action="<?php echo BASE_URL . 'empleado/agenda'; ?>" class="row g-3 mb-4 p-3 border rounded bg-light shadow-sm">
        <div class="col-md-5">
            <label for="fecha_inicio" class="form-label fw-bold">Desde:</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_filtro_inicio ?? ''); ?>" aria-label="Fecha de inicio del filtro">
        </div>
        <div class="col-md-5">
            <label for="fecha_fin" class="form-label fw-bold">Hasta:</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_filtro_fin ?? ''); ?>" aria-label="Fecha de fin del filtro">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
    </form>

    <?php if (empty($citas)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> No tienes citas programadas para el periodo seleccionado.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <?php
                            $estado_actual_lower = strtolower($cita['estado_cita'] ?? '');
                            $badge_class = $estados_clases_badge[$estado_actual_lower] ?? 'secondary';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['fecha_formateada'] ?? 'N/D'); ?></td>
                            <td><?php echo htmlspecialchars($cita['hora_formateada'] ?? 'N/D'); ?></td>
                            <td><?php echo htmlspecialchars(trim(($cita['cliente_nombre'] ?? '') . ' ' . ($cita['cliente_apellido'] ?? '')) ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars(ucfirst($cita['estado_cita'] ?? 'N/A')); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detalleCitaModal-<?php echo $cita['id']; ?>" title="Ver Detalles y Cambiar Estado de la Cita #<?php echo $cita['id']; ?>">
                                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">Ver/Gestionar</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal para Detalles y Edición de Estado de Cita -->
                        <div class="modal fade" id="detalleCitaModal-<?php echo $cita['id']; ?>" tabindex="-1" aria-labelledby="detalleCitaModalLabel-<?php echo $cita['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="<?php echo BASE_URL . 'empleado/citas/actualizar_estado/' . $cita['id']; ?>" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="detalleCitaModalLabel-<?php echo $cita['id']; ?>">
                                                <i class="fas fa-calendar-check me-2"></i> Detalles de Cita #<?php echo htmlspecialchars($cita['id']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong><i class="fas fa-user me-2"></i>Cliente:</strong> <?php echo htmlspecialchars(trim(($cita['cliente_nombre'] ?? '') . ' ' . ($cita['cliente_apellido'] ?? '')) ?: 'N/A'); ?></p>
                                                    <p><strong><i class="fas fa-phone me-2"></i>Teléfono Cliente:</strong> <?php echo htmlspecialchars($cita['cliente_telefono'] ?? 'N/A'); ?></p>
                                                    <p><strong><i class="fas fa-cut me-2"></i>Servicio:</strong> <?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong><i class="fas fa-calendar-day me-2"></i>Fecha:</strong> <?php echo htmlspecialchars($cita['fecha_formateada'] ?? 'N/D'); ?></p>
                                                    <p><strong><i class="fas fa-clock me-2"></i>Hora:</strong> <?php echo htmlspecialchars($cita['hora_formateada'] ?? 'N/D'); ?></p>
                                                    <p><strong><i class="far fa-hourglass me-2"></i>Duración Estimada:</strong> <?php echo htmlspecialchars($cita['duracion_estimada_min'] ?? 'N/A'); ?> minutos</p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <p class="fw-bold"><i class="fas fa-sticky-note me-2"></i>Notas del Cliente:</p>
                                                <div class="bg-light p-2 rounded border">
                                                    <?php echo nl2br(htmlspecialchars($cita['notas_cliente'] ?? 'Ninguna')); ?>
                                                </div>
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="mb-3">
                                                <label for="estado_cita_<?php echo $cita['id']; ?>" class="form-label fw-bold"><strong><i class="fas fa-exchange-alt me-2"></i>Cambiar Estado:</strong></label>
                                                <select class="form-select" id="estado_cita_<?php echo $cita['id']; ?>" name="estado_cita" aria-describedby="estadoCitaHelp-<?php echo $cita['id']; ?>">
                                                    <?php 
                                                    foreach ($estadosPosiblesParaEmpleado as $estado) {
                                                        $selected = (strtolower($cita['estado_cita']) == strtolower($estado)) ? 'selected' : '';
                                                        echo "<option value=\"" . htmlspecialchars($estado) . "\" $selected>" . htmlspecialchars(ucfirst($estado)) . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <small id="estadoCitaHelp-<?php echo $cita['id']; ?>" class="form-text text-muted">Selecciona el nuevo estado para la cita.</small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="notas_empleado_<?php echo $cita['id']; ?>" class="form-label fw-bold"><strong><i class="fas fa-user-edit me-2"></i>Notas del Empleado (opcional):</strong></label>
                                                <textarea class="form-control" id="notas_empleado_<?php echo $cita['id']; ?>" name="notas_empleado" rows="3" aria-describedby="notasEmpleadoHelp-<?php echo $cita['id']; ?>"><?php echo htmlspecialchars($cita['notas_empleado'] ?? ''); ?></textarea>
                                                <small id="notasEmpleadoHelp-<?php echo $cita['id']; ?>" class="form-text text-muted">Añade cualquier nota relevante sobre la cita.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
// Limpiar mensajes temporales si los hubiera para esta vista específica
if (isset($_SESSION['mensaje_temporal_error'])) unset($_SESSION['mensaje_temporal_error']);
if (isset($_SESSION['mensaje_temporal_exito'])) unset($_SESSION['mensaje_temporal_exito']);
?>