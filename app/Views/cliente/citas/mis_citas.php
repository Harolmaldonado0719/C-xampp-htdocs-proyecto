
<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\cliente\citas\mis_citas.php
// Variables disponibles: $pageTitle, $citas (array), $active_page

// Definir los posibles estados y sus clases de badge para consistencia
$estados_clases_badge_cliente = [
    'pendiente' => 'warning text-dark',
    'confirmada' => 'info text-dark', // Bootstrap 5 'info' es azul claro, text-dark para contraste
    'completada' => 'success',
    'cancelada por cliente' => 'danger',
    'cancelada por empleado' => 'danger',
    'cancelada por sistema' => 'danger',
    'no asistió' => 'secondary',
    'reprogramada' => 'primary', // Opcional, si el cliente puede ver este estado
];

?>

<div class="container mt-4">
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Mis Citas Programadas'); ?></h1>
    <hr>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_exito_global']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_error_global']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_info_global'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($_SESSION['mensaje_info_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_info_global']); ?>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo BASE_URL . 'citas/reservar'; ?>" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Reservar Nueva Cita
        </a>
    </div>

    <?php if (!empty($citas)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID Cita</th>
                        <th>Servicio</th>
                        <th>Empleado</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Notas Cliente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <?php
                            // CORRECCIÓN: Usar 'id' en lugar de 'id_cita'
                            $cita_id_seguro = htmlspecialchars($cita['id'] ?? 'N/A');
                            $estado_actual_lower = strtolower($cita['estado_cita'] ?? '');
                            $badge_class = $estados_clases_badge_cliente[$estado_actual_lower] ?? 'light text-dark'; // Clase por defecto
                        ?>
                        <tr>
                            <td>#<?php echo $cita_id_seguro; ?></td>
                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(trim(($cita['empleado_nombre'] ?? '') . ' ' . ($cita['empleado_apellido'] ?? '')) ?: 'N/A'); ?></td>
                            <td>
                                <?php 
                                if (!empty($cita['fecha_hora_cita'])) {
                                    try {
                                        $fecha = new DateTime($cita['fecha_hora_cita']);
                                        echo $fecha->format('d/m/Y');
                                    } catch (Exception $e) {
                                        echo 'Fecha inválida';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($cita['fecha_hora_cita'])) {
                                    try {
                                        $fecha = new DateTime($cita['fecha_hora_cita']);
                                        echo $fecha->format('h:i A');
                                    } catch (Exception $e) {
                                        echo 'Hora inválida';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars(ucfirst($cita['estado_cita'] ?? 'Desconocido')); ?>
                                </span>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($cita['notas_cliente'] ?? '')); ?></td>
                            <td>
                                <?php
                                $puedeCancelar = false;
                                if (isset($cita['estado_cita'])) {
                                    $estadoActual = strtolower($cita['estado_cita']);
                                    // Estados en los que el cliente YA NO PUEDE cancelar
                                    $estadosNoCancelablesCliente = ['completada', 'cancelada por cliente', 'cancelada por sistema', 'cancelada por empleado', 'no asistió'];
                                    if (!in_array($estadoActual, $estadosNoCancelablesCliente)) {
                                        $puedeCancelar = true;
                                    }
                                }
                                ?>
                                <?php if ($puedeCancelar && $cita_id_seguro !== 'N/A'): ?>
                                    <form action="<?php echo BASE_URL . 'citas/cancelar/' . $cita_id_seguro; ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres cancelar esta cita? Esta acción no se puede deshacer.');" style="display: inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Cancelar Cita">
                                            <i class="fas fa-times-circle me-1"></i>Cancelar
                                        </button>
                                    </form>
                                <?php elseif ($cita_id_seguro === 'N/A'): ?>
                                     <span class="text-muted fst-italic">Error ID</span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">No cancelable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            Aún no tienes citas programadas. ¡<a href="<?php echo BASE_URL . 'citas/reservar'; ?>" class="alert-link">Reserva una ahora</a>!
        </div>
    <?php endif; ?>
</div>
