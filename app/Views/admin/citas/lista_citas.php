<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\admin\citas\lista_citas.php
// Variables disponibles: $pageTitle, $citas (array), $active_page
?>

<div class="container mt-4">
    <h1><?php echo htmlspecialchars($pageTitle ?? 'Gestionar Todas las Citas'); ?></h1>
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
    <?php if (isset($_SESSION['mensaje_info_global'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_info_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje_info_global']); ?>
    <?php endif; ?>


    <?php if (!empty($citas)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID Cita</th>
                        <th>Cliente</th>
                        <th>Empleado</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Notas Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['id'] ?? 'N/A'); ?></td> 
                            <td><?php echo htmlspecialchars(($cita['cliente_nombre'] ?? '') . ' ' . ($cita['cliente_apellido'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars(($cita['empleado_nombre'] ?? '') . ' ' . ($cita['empleado_apellido'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($cita['servicio_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cita['fecha_formateada'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cita['hora_formateada'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        switch (strtolower($cita['estado_cita'] ?? '')) {
                                            case 'pendiente': echo 'bg-warning text-dark'; break;
                                            case 'confirmada': echo 'bg-info text-dark'; break;
                                            case 'completada': echo 'bg-success'; break;
                                            case 'cancelada por cliente':
                                            case 'cancelada': // Unificar cancelaciones para el color
                                            case 'cancelada por empleado':
                                            case 'cancelada por sistema': echo 'bg-danger'; break;
                                            case 'no asistio': 
                                            case 'no asistió': echo 'bg-secondary'; break;
                                            default: echo 'bg-light text-dark'; break;
                                        }
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($cita['estado_cita'] ?? 'Desconocido')); ?>
                                </span>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($cita['notas_cliente'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay citas para mostrar.
        </div>
    <?php endif; ?>
</div>