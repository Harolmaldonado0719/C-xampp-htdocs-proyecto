<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\admin\citas\calendario_general.php
// Variables disponibles: $pageTitle, $citas_agrupadas (array), $active_page

// Helper para traducir nombres de días y meses si es necesario
if (!function_exists('traducirDia')) {
    function traducirDia($diaEnIngles) {
        $dias = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
        ];
        return $dias[$diaEnIngles] ?? $diaEnIngles;
    }
}

if (!function_exists('traducirMes')) {
    function traducirMes($mesEnIngles) {
        $meses = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
            'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
            'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        return $meses[$mesEnIngles] ?? $mesEnIngles;
    }
}

?>

<div class="container mt-4">

    <h1><?php echo htmlspecialchars($pageTitle ?? 'Calendario General de Citas'); ?></h1>
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

    <?php if (!empty($citas_agrupadas)): ?>
        <div class="accordion" id="accordionCalendarioCitas">
            <?php foreach ($citas_agrupadas as $fecha_iso => $citas_del_dia): ?>
                <?php
                    try {
                        $fechaObj = new DateTime($fecha_iso);
                        $diaSemana = traducirDia($fechaObj->format('l'));
                        $diaMes = $fechaObj->format('d');
                        $mes = traducirMes($fechaObj->format('F'));
                        $anio = $fechaObj->format('Y');
                        $fecha_formateada_titulo = "$diaSemana, $diaMes de $mes de $anio";
                    } catch (Exception $e) {
                        $fecha_formateada_titulo = $fecha_iso; // Fallback
                    }
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?php echo htmlspecialchars($fecha_iso); ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo htmlspecialchars($fecha_iso); ?>" aria-expanded="false" aria-controls="collapse-<?php echo htmlspecialchars($fecha_iso); ?>">
                            <?php echo htmlspecialchars($fecha_formateada_titulo); ?>
                            <span class="badge bg-primary rounded-pill ms-2"><?php echo count($citas_del_dia); ?> cita(s)</span>
                        </button>
                    </h2>
                    <div id="collapse-<?php echo htmlspecialchars($fecha_iso); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo htmlspecialchars($fecha_iso); ?>" data-bs-parent="#accordionCalendarioCitas">
                        <div class="accordion-body">
                            <ul class="list-group">
                                <?php foreach ($citas_del_dia as $cita): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($cita['hora_formateada'] ?? 'N/A'); ?></strong> - 
                                        Servicio: <?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?><br>
                                        Cliente: <?php echo htmlspecialchars($cita['cliente_nombre'] ?? 'N/A'); ?><br>
                                        Empleado: <?php echo htmlspecialchars($cita['empleado_nombre'] ?? 'N/A'); ?><br>
                                        Estado: <span class="badge 
                                            <?php 
                                                switch (strtolower($cita['estado_cita'] ?? '')) {
                                                    case 'pendiente': echo 'bg-warning text-dark'; break;
                                                    case 'confirmada': echo 'bg-info text-dark'; break;
                                                    case 'completada': echo 'bg-success'; break;
                                                    case 'cancelada por cliente':
                                                    case 'cancelada por empleado':
                                                    case 'cancelada por sistema': echo 'bg-danger'; break;
                                                    case 'no asistió': echo 'bg-secondary'; break;
                                                    default: echo 'bg-light text-dark'; break;
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars(ucfirst($cita['estado_cita'] ?? 'Desconocido')); ?>
                                        </span><br>
                                        <?php if (!empty($cita['notas_cliente'])): ?>
                                            Notas: <?php echo nl2br(htmlspecialchars($cita['notas_cliente'])); ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay citas programadas para mostrar en el calendario.
        </div>
    <?php endif; ?>
</div>