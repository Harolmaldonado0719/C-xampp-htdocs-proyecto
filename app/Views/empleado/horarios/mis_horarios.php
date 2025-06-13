<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\empleado\horarios\mis_horarios.php
// Variables disponibles: $pageTitle, $active_page, $horariosRecurrentes, $excepciones, $diasSemanaMap
?>

<div class="container mt-4">
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Mis Horarios de Trabajo'); ?></h1>
    <p class="lead">Aquí puedes consultar tus horarios de trabajo asignados. Esta sección es solo de visualización.</p>
    <hr>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_exito_global']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_error_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_error_global']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Horario Semanal Recurrente</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($horariosRecurrentes)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Día de la Semana</th>
                                <th>Hora de Inicio</th>
                                <th>Hora de Fin</th>
                                <th>Vigencia Desde</th>
                                <th>Vigencia Hasta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horariosRecurrentes as $hr): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($diasSemanaMap[$hr['dia_semana']] ?? 'Día Desconocido'); ?></td>
                                    <td><?php echo htmlspecialchars(date("h:i A", strtotime($hr['hora_inicio']))); ?></td>
                                    <td><?php echo htmlspecialchars(date("h:i A", strtotime($hr['hora_fin']))); ?></td>
                                    <td><?php echo $hr['fecha_desde'] ? htmlspecialchars(date("d/m/Y", strtotime($hr['fecha_desde']))) : 'Siempre'; ?></td>
                                    <td><?php echo $hr['fecha_hasta'] ? htmlspecialchars(date("d/m/Y", strtotime($hr['fecha_hasta']))) : 'Indefinido'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No tienes horarios recurrentes configurados.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Excepciones de Horario (Próximos 2 meses)</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($excepciones)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Hora de Inicio</th>
                                <th>Hora de Fin</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($excepciones as $ex): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($ex['fecha']))); ?></td>
                                    <td>
                                        <?php 
                                            // Asumiendo que $ex['tipo_excepcion'] puede ser 'NO_DISPONIBLE' o 'DISPONIBLE_EXTRA'
                                            // y $ex['esta_disponible'] es un booleano o 0/1 que ya tienes.
                                            // Si no tienes $ex['esta_disponible'] directamente, puedes derivarlo de $ex['tipo_excepcion']
                                            $esta_disponible = false; // Valor por defecto
                                            if (isset($ex['esta_disponible'])) {
                                                $esta_disponible = (bool) $ex['esta_disponible'];
                                            } elseif (isset($ex['tipo_excepcion']) && $ex['tipo_excepcion'] === 'DISPONIBLE_EXTRA') {
                                                $esta_disponible = true;
                                            }
                                        ?>
                                        <?php if ($esta_disponible): ?>
                                            <span class="badge bg-success">Disponible (Extra)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No Disponible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($ex['hora_inicio_excepcion']) && $ex['hora_inicio_excepcion'] ? htmlspecialchars(date("h:i A", strtotime($ex['hora_inicio_excepcion']))) : 'Todo el día'; ?></td>
                                    <td><?php echo isset($ex['hora_fin_excepcion']) && $ex['hora_fin_excepcion'] ? htmlspecialchars(date("h:i A", strtotime($ex['hora_fin_excepcion']))) : 'Todo el día'; ?></td>
                                    <td><?php echo htmlspecialchars($ex['descripcion'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No hay excepciones de horario programadas para las próximas fechas.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>empleado/dashboard" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Dashboard</a>
        <a href="<?php echo BASE_URL; ?>empleado/gestionar-horario" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Gestionar Mi Horario (Editable)</a>
    </div>
</div>

<style>
    .card {
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem; /* Bootstrap 5 default */
    }
    .card-header {
        background-color: #f8f9fa; /* Un gris claro */
        border-bottom: 1px solid #dee2e6;
        padding: 0.75rem 1.25rem;
    }
    .card-header h3 {
        margin-bottom: 0;
        font-size: 1.25rem;
        color: #0056b3; /* Un azul oscuro para el título */
    }
    .table th {
        font-weight: 600; /* Hacer los encabezados de tabla un poco más audaces */
    }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075); /* Efecto hover sutil */
    }
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.6em;
    }
    .lead {
        font-size: 1.1rem;
        font-weight: 300;
        margin-bottom: 1rem;
    }
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    .me-2 {
        margin-right: 0.5rem !important;
    }
</style>