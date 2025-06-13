<?php
// Asegúrate de que $pageTitle, $active_page, $horariosRecurrentes, $excepciones, $diasSemanaMap están definidos desde el controlador
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($pageTitle ?? 'Gestionar Mi Horario'); ?></h2>

    <?php include __DIR__ . '/../../partials/mensajes.php'; ?>

    <div class="row">
        <!-- Sección Horarios Recurrentes -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>Horarios Recurrentes</h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>empleado/horario-recurrente/guardar" method="POST" class="mb-3">
                        <div class="form-group mb-2">
                            <label for="dia_semana" class="form-label">Día de la Semana:</label>
                            <select name="dia_semana" id="dia_semana" class="form-select" required>
                                <?php foreach ($diasSemanaMap as $num => $nombre): ?>
                                    <option value="<?php echo $num; ?>"><?php echo htmlspecialchars($nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="hora_inicio" class="form-label">Hora Inicio:</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="hora_fin" class="form-label">Hora Fin:</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="fecha_desde_recurrente" class="form-label">Válido Desde (opcional):</label>
                            <input type="date" name="fecha_desde_recurrente" id="fecha_desde_recurrente" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="fecha_hasta_recurrente" class="form-label">Válido Hasta (opcional):</label>
                            <input type="date" name="fecha_hasta_recurrente" id="fecha_hasta_recurrente" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-2"></i>Añadir Horario Recurrente</button>
                    </form>

                    <h5>Mis Horarios Recurrentes:</h5>
                    <?php if (!empty($horariosRecurrentes)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($horariosRecurrentes as $hr): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($diasSemanaMap[$hr['dia_semana']] ?? 'Día desc.'); ?>:</strong>
                                        <?php echo (isset($hr['hora_inicio']) && is_string($hr['hora_inicio'])) ? htmlspecialchars(date("g:i A", strtotime($hr['hora_inicio']))) : 'N/A'; ?> -
                                        <?php echo (isset($hr['hora_fin']) && is_string($hr['hora_fin'])) ? htmlspecialchars(date("g:i A", strtotime($hr['hora_fin']))) : 'N/A'; ?>
                                        <small class="d-block text-muted">
                                            <?php 
                                            $fecha_desde_valida = isset($hr['fecha_desde']) && !empty($hr['fecha_desde']);
                                            $fecha_hasta_valida = isset($hr['fecha_hasta']) && !empty($hr['fecha_hasta']);
                                            if ($fecha_desde_valida || $fecha_hasta_valida): 
                                            ?>
                                                (Válido: 
                                                <?php echo $fecha_desde_valida ? htmlspecialchars(date("d/m/y", strtotime($hr['fecha_desde']))) : 'Siempre'; ?>
                                                - 
                                                <?php echo $fecha_hasta_valida ? htmlspecialchars(date("d/m/y", strtotime($hr['fecha_hasta']))) : 'Indef.'; ?>
                                                )
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <form action="<?php echo BASE_URL; ?>empleado/horario-recurrente/eliminar/<?php echo htmlspecialchars($hr['id_horario_recurrente'] ?? ''); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este horario recurrente?');">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No tienes horarios recurrentes definidos.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sección Excepciones de Horario -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>Excepciones de Horario</h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>empleado/excepcion-horario/guardar" method="POST" class="mb-3">
                        <div class="form-group mb-2">
                            <label for="fecha_excepcion" class="form-label">Fecha:</label>
                            <input type="date" name="fecha_excepcion" id="fecha_excepcion" class="form-control" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="tipo_excepcion" class="form-label">Tipo:</label>
                            <select name="tipo_excepcion" id="tipo_excepcion" class="form-select" required>
                                <option value="NO_DISPONIBLE">No Disponible (Todo el día o rango)</option>
                                <option value="DISPONIBLE_EXTRA">Disponible (Extra)</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="hora_inicio_excepcion" class="form-label">Hora Inicio (opcional):</label>
                            <input type="time" name="hora_inicio_excepcion" id="hora_inicio_excepcion" class="form-control">
                            <small class="form-text text-muted">Dejar en blanco si es todo el día para "No Disponible". Obligatorio para "Disponible (Extra)".</small>
                        </div>
                        <div class="form-group mb-2">
                            <label for="hora_fin_excepcion" class="form-label">Hora Fin (opcional):</label>
                            <input type="time" name="hora_fin_excepcion" id="hora_fin_excepcion" class="form-control">
                             <small class="form-text text-muted">Dejar en blanco si es todo el día para "No Disponible". Obligatorio para "Disponible (Extra)".</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="descripcion_excepcion" class="form-label">Descripción (opcional):</label>
                            <textarea name="descripcion_excepcion" id="descripcion_excepcion" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-info w-100"><i class="fas fa-calendar-plus me-2"></i>Añadir Excepción</button>
                    </form>

                    <h5>Mis Excepciones Programadas:</h5>
                    <?php if (!empty($excepciones)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($excepciones as $ex): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo (isset($ex['fecha']) && is_string($ex['fecha'])) ? htmlspecialchars(date("d/m/Y", strtotime($ex['fecha']))) : 'Fecha N/A'; ?>:</strong>
                                        <?php $tipo_excepcion = $ex['tipo_excepcion'] ?? 'DESCONOCIDO'; ?>
                                        <?php if ($tipo_excepcion == 'NO_DISPONIBLE'): ?>
                                            <span class="badge bg-danger">No Disponible</span>
                                        <?php elseif ($tipo_excepcion == 'DISPONIBLE_EXTRA'): ?>
                                            <span class="badge bg-success">Disponible (Extra)</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars(str_replace('_', ' ', $tipo_excepcion)); ?></span>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $hora_inicio_ex = isset($ex['hora_inicio_excepcion']) && is_string($ex['hora_inicio_excepcion']) && !empty($ex['hora_inicio_excepcion']);
                                        $hora_fin_ex = isset($ex['hora_fin_excepcion']) && is_string($ex['hora_fin_excepcion']) && !empty($ex['hora_fin_excepcion']);
                                        ?>
                                        <?php if ($hora_inicio_ex && $hora_fin_ex): ?>
                                            <small class="d-block text-muted">
                                                <?php echo htmlspecialchars(date("g:i A", strtotime($ex['hora_inicio_excepcion']))); ?> - 
                                                <?php echo htmlspecialchars(date("g:i A", strtotime($ex['hora_fin_excepcion']))); ?>
                                            </small>
                                        <?php elseif ($hora_inicio_ex): ?>
                                             <small class="d-block text-muted">Desde <?php echo htmlspecialchars(date("g:i A", strtotime($ex['hora_inicio_excepcion']))); ?></small>
                                        <?php elseif ($tipo_excepcion == 'NO_DISPONIBLE' && !$hora_inicio_ex && !$hora_fin_ex): ?>
                                            <small class="d-block text-muted">Todo el día</small>
                                        <?php endif; ?>

                                        <?php if (!empty($ex['descripcion'])): ?>
                                            <small class="d-block text-info fst-italic">Motivo: <?php echo htmlspecialchars($ex['descripcion']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?php echo BASE_URL; ?>empleado/excepcion-horario/eliminar/<?php echo htmlspecialchars($ex['id_excepcion'] ?? ''); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta excepción?');">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No tienes excepciones de horario programadas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>empleado/horarios" class="btn btn-secondary"><i class="fas fa-eye me-2"></i>Ver Mis Horarios (Solo Lectura)</a>
        <a href="<?php echo BASE_URL; ?>empleado/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Dashboard</a>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header h3 {
        margin-bottom: 0;
        font-size: 1.25rem;
    }
    .form-label {
        font-weight: 500;
    }
    .list-group-item small {
        font-size: 0.85em;
    }
    .btn-primary, .btn-info {
        color: #fff;
    }
    .me-2 {
        margin-right: 0.5rem !important;
    }
</style>