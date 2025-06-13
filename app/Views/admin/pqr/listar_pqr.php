<!-- filepath: app/Views/admin/pqr/listar_pqr.php -->
<h1><?php echo htmlspecialchars($pageTitle ?? "Gestionar Solicitudes (PQR)"); ?></h1>

<?php if (isset($_SESSION['mensaje_error_pqr_admin'])): ?>
    <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_pqr_admin']); unset($_SESSION['mensaje_error_pqr_admin']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['mensaje_exito_global'])): ?>
    <div class="message success"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
<?php endif; ?>


<?php if (empty($solicitudes)): ?>
    <p>No hay solicitudes de atención pendientes o registradas.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table-admin-pqr">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Última Actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $solicitud): ?>
                    <tr>
                        <td>PQR-<?php echo htmlspecialchars($solicitud['id']); ?></td>
                        <td><?php echo htmlspecialchars(($solicitud['nombre_cliente'] ?? 'N/A') . ' ' . ($solicitud['apellido_cliente'] ?? '')); ?> (ID: <?php echo htmlspecialchars($solicitud['usuario_id']); ?>)</td>
                        <td><?php echo htmlspecialchars($solicitud['tipo_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['asunto']); ?></td>
                        <td><span class="status status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $solicitud['estado']))); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></td>
                        <td><?php echo $solicitud['fecha_actualizacion'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion']))) : 'N/A'; ?></td>
                        <td>
                            <a href="<?php echo BASE_URL . 'admin/pqr/ver/' . htmlspecialchars($solicitud['id']); ?>" class="btn btn-sm btn-info">Ver/Responder</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Aquí podrías añadir lógica de paginación si la implementas en el modelo/controlador -->
<?php endif; ?>

<style>
    .table-responsive { overflow-x: auto; }
    .table-admin-pqr { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
    .table-admin-pqr th, .table-admin-pqr td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .table-admin-pqr th { background-color: #f2f2f2; }
    .status { padding: 3px 7px; border-radius: 4px; color: white; font-size: 0.9em; display: inline-block; text-align: center; min-width: 80px;}
    .status-abierta { background-color: #007bff; } /* Azul */
    .status-en-proceso { background-color: #ffc107; color: #212529;} /* Amarillo */
    .status-resuelta { background-color: #28a745; } /* Verde */
    .status-cerrada { background-color: #6c757d; } /* Gris */
    .status-requiere-información-adicional { background-color: #fd7e14; } /* Naranja */
    .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
    .btn-sm { padding: 0.25rem 0.5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
    .btn-info { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }
</style>