<?php
// Variables esperadas:
// $pageTitle (string)
// $active_page (string)
// $empleado (array) - Datos del empleado (id, nombre, apellido)
// $horariosRecurrentes (array) - Lista de horarios recurrentes del empleado
// $excepciones (array) - Lista de excepciones del empleado
// $diasSemanaMap (array) - Mapa de dia_semana (int) a nombre del día (string)

$base_url_empleado_horario = BASE_URL . "admin/empleado/" . htmlspecialchars($empleado['id']) . "/horario";
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    <p>Gestionando horarios para: <strong><?php echo htmlspecialchars($empleado['nombre'] . " " . ($empleado['apellido'] ?? '')); ?></strong></p>
    <hr>

    <a href="<?php echo BASE_URL; ?>admin/usuarios" class="btn btn-secondary mb-3">Volver a Lista de Usuarios</a>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
    <?php endif; ?>

    <!-- Sección de Horarios Recurrentes -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Horarios Recurrentes</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo $base_url_empleado_horario; ?>/recurrente/guardar" method="POST" class="mb-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="dia_semana" class="form-label">Día de la Semana:</label>
                        <select name="dia_semana" id="dia_semana" class="form-select" required>
                            <?php foreach ($diasSemanaMap as $num => $nombreDia): ?>
                                <option value="<?php echo $num; ?>"><?php echo htmlspecialchars($nombreDia); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="hora_inicio_recurrente" class="form-label">Hora Inicio:</label>
                        <input type="time" name="hora_inicio_recurrente" id="hora_inicio_recurrente" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label for="hora_fin_recurrente" class="form-label">Hora Fin:</label>
                        <input type="time" name="hora_fin_recurrente" id="hora_fin_recurrente" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_desde_recurrente" class="form-label">Desde (Opcional):</label>
                        <input type="date" name="fecha_desde_recurrente" id="fecha_desde_recurrente" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_hasta_recurrente" class="form-label">Hasta (Opcional):</label>
                        <input type="date" name="fecha_hasta_recurrente" id="fecha_hasta_recurrente" class="form-control">
                    </div>
                    <div class="col-md-1 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Guardar</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($horariosRecurrentes)): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horariosRecurrentes as $hr): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($diasSemanaMap[$hr['dia_semana']]); ?></td>
                                <td><?php echo htmlspecialchars(date("H:i", strtotime($hr['hora_inicio']))); ?></td>
                                <td><?php echo htmlspecialchars(date("H:i", strtotime($hr['hora_fin']))); ?></td>
                                <td><?php echo $hr['fecha_desde'] ? htmlspecialchars(date("d/m/Y", strtotime($hr['fecha_desde']))) : 'N/A'; ?></td>
                                <td><?php echo $hr['fecha_hasta'] ? htmlspecialchars(date("d/m/Y", strtotime($hr['fecha_hasta']))) : 'N/A'; ?></td>
                                <td>
                                    <form action="<?php echo $base_url_empleado_horario; ?>/recurrente/eliminar/<?php echo $hr['id']; ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este horario recurrente?');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay horarios recurrentes definidos para este empleado.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección de Excepciones de Horario -->
    <div class="card">
        <div class="card-header">
            <h3>Excepciones de Horario</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo $base_url_empleado_horario; ?>/excepcion/guardar" method="POST" class="mb-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="fecha_excepcion" class="form-label">Fecha:</label>
                        <input type="date" name="fecha_excepcion" id="fecha_excepcion" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo_excepcion" class="form-label">Tipo:</label>
                        <select name="tipo_excepcion" id="tipo_excepcion" class="form-select" required>
                            <option value="NO_DISPONIBLE">No Disponible</option>
                            <option value="DISPONIBLE_EXTRA">Disponible (Horario Extra)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="hora_inicio_excepcion" class="form-label">Hora Inicio (Opcional):</label>
                        <input type="time" name="hora_inicio_excepcion" id="hora_inicio_excepcion" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="hora_fin_excepcion" class="form-label">Hora Fin (Opcional):</label>
                        <input type="time" name="hora_fin_excepcion" id="hora_fin_excepcion" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="descripcion_excepcion" class="form-label">Descripción (Opcional):</label>
                        <input type="text" name="descripcion_excepcion" id="descripcion_excepcion" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="row mt-2">
                     <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary">Guardar Excepción</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($excepciones)): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Descripción</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($excepciones as $ex): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($ex['fecha']))); ?></td>
                                <td><?php echo $ex['esta_disponible'] ? 'Disponible (Extra)' : 'No Disponible'; ?></td>
                                <td><?php echo $ex['hora_inicio'] ? htmlspecialchars(date("H:i", strtotime($ex['hora_inicio']))) : 'Todo el día'; ?></td>
                                <td><?php echo $ex['hora_fin'] ? htmlspecialchars(date("H:i", strtotime($ex['hora_fin']))) : ''; ?></td>
                                <td><?php echo htmlspecialchars($ex['descripcion'] ?? ''); ?></td>
                                <td>
                                    <form action="<?php echo $base_url_empleado_horario; ?>/excepcion/eliminar/<?php echo $ex['id']; ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta excepción?');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay excepciones de horario definidas para este empleado en el rango visible.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Script opcional para mejorar la UX, por ejemplo, mostrar/ocultar horas de excepción según el tipo.
document.addEventListener('DOMContentLoaded', function() {
    const tipoExcepcionSelect = document.getElementById('tipo_excepcion');
    const horaInicioExInput = document.getElementById('hora_inicio_excepcion');
    const horaFinExInput = document.getElementById('hora_fin_excepcion');

    function toggleHorasExcepcion() {
        if (tipoExcepcionSelect.value === 'DISPONIBLE_EXTRA') {
            horaInicioExInput.required = true;
            horaFinExInput.required = true;
            // Podrías también hacerlos visibles si estaban ocultos
        } else { // NO_DISPONIBLE
            horaInicioExInput.required = false;
            horaFinExInput.required = false;
            // Podrías también limpiarlos u ocultarlos
        }
    }

    if (tipoExcepcionSelect) {
        tipoExcepcionSelect.addEventListener('change', toggleHorasExcepcion);
        toggleHorasExcepcion(); // Llamar al inicio por si hay valores precargados
    }
});
</script>