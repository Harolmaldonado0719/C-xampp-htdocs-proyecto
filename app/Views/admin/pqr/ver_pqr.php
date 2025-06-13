<!-- filepath: app/Views/admin/pqr/ver_pqr.php -->
<h1><?php echo htmlspecialchars($pageTitle ?? "Detalle Solicitud"); ?></h1>

<a href="<?php echo BASE_URL; ?>admin/pqr" class="btn btn-secondary" style="margin-bottom:20px;">Volver al Listado</a>

<?php if (isset($_SESSION['mensaje_error_pqr_detalle'])): ?>
    <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_pqr_detalle']); unset($_SESSION['mensaje_error_pqr_detalle']); ?></div>
<?php endif; ?>

<?php if ($solicitud): ?>
    <div class="pqr-details-grid">
        <div class="pqr-detail-item"><strong>ID Solicitud:</strong> PQR-<?php echo htmlspecialchars($solicitud['id']); ?></div>
        <div class="pqr-detail-item"><strong>Cliente:</strong> <?php echo htmlspecialchars(($solicitud['nombre_usuario'] ?? 'N/A') . ' ' . ($solicitud['apellido_usuario'] ?? '')); ?> (ID Usuario: <?php echo htmlspecialchars($solicitud['usuario_id']); ?>)</div>
        <div class="pqr-detail-item"><strong>Email Contacto Cliente:</strong> <?php echo htmlspecialchars($solicitud['email_contacto']); ?></div>
        <div class="pqr-detail-item"><strong>Tipo:</strong> <?php echo htmlspecialchars($solicitud['tipo_solicitud']); ?></div>
        <div class="pqr-detail-item"><strong>Asunto:</strong> <?php echo htmlspecialchars($solicitud['asunto']); ?></div>
        <div class="pqr-detail-item"><strong>Estado Actual:</strong> <span class="status status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $solicitud['estado']))); ?>"><?php echo htmlspecialchars($solicitud['estado']); ?></span></div>
        <div class="pqr-detail-item"><strong>Fecha Creación:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_creacion']))); ?></div>
        <div class="pqr-detail-item"><strong>Última Actualización:</strong> <?php echo $solicitud['fecha_actualizacion'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion']))) : 'N/A'; ?></div>
        
        <div class="pqr-detail-item full-width">
            <strong>Descripción del Cliente:</strong>
            <div class="description-box"><?php echo nl2br(htmlspecialchars($solicitud['descripcion'])); ?></div>
        </div>

        <?php if ($solicitud['respuesta_admin']): ?>
        <div class="pqr-detail-item full-width">
            <strong>Respuesta Anterior del Administrador (ID: <?php echo htmlspecialchars($solicitud['admin_id_respuesta'] ?? 'N/A'); ?>):</strong>
            <div class="description-box admin-response"><?php echo nl2br(htmlspecialchars($solicitud['respuesta_admin'])); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <hr style="margin: 20px 0;">
    
    <h3>Responder / Actualizar Estado</h3>
    <form action="<?php echo BASE_URL; ?>admin/pqr/actualizar/<?php echo htmlspecialchars($solicitud['id']); ?>" method="POST" class="form-admin-pqr-response">
        <div class="form-group">
            <label for="estado">Nuevo Estado: <span class="required">*</span></label>
            <select name="estado" id="estado" required>
                <?php foreach ($estados_posibles ?? [] as $estado_opcion): ?>
                    <option value="<?php echo htmlspecialchars($estado_opcion); ?>" <?php echo ($solicitud['estado'] == $estado_opcion) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($estado_opcion); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="respuesta_admin">Respuesta para el Cliente: <span class="required">*</span></label>
            <textarea name="respuesta_admin" id="respuesta_admin" rows="5" required><?php echo htmlspecialchars($solicitud['respuesta_admin'] ?? ''); ?></textarea>
            <small>Esta respuesta será visible para el cliente (si se implementa la vista de detalle para él) y se podría enviar una notificación.</small>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Solicitud</button>
    </form>

<?php else: ?>
    <p>No se pudo cargar la información de la solicitud.</p>
<?php endif; ?>

<style>
    .pqr-details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-bottom: 20px; padding:15px; border:1px solid #eee; background-color:#fdfdfd; border-radius:5px;}
    .pqr-detail-item { padding: 8px; background-color: #f9f9f9; border: 1px solid #eaeaea; border-radius: 3px;}
    .pqr-detail-item strong { color: #333; }
    .pqr-detail-item.full-width { grid-column: 1 / -1; } /* Ocupa todo el ancho */
    .description-box { margin-top: 5px; padding: 10px; background-color: #fff; border: 1px solid #ddd; border-radius: 3px; min-height: 60px; white-space: pre-wrap; }
    .description-box.admin-response { background-color: #eef7ff; border-color: #cce0ff; }

    .form-admin-pqr-response { margin-top: 20px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;}
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group select,
    .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .form-group textarea { resize: vertical; }
    .form-group .required { color: red; }
    .form-group small { font-size: 0.85em; color: #555; display: block; margin-top: 3px;}
    
    .status { padding: 3px 7px; border-radius: 4px; color: white; font-size: 0.9em; display: inline-block; text-align: center; min-width: 80px;}
    .status-abierta { background-color: #007bff; }
    .status-en-proceso { background-color: #ffc107; color: #212529;}
    .status-resuelta { background-color: #28a745; }
    .status-cerrada { background-color: #6c757d; }
    .status-requiere-información-adicional { background-color: #fd7e14; }

    .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    .btn { display: inline-block; padding: 8px 15px; margin: 5px 0; border: 1px solid transparent; border-radius: 4px; text-decoration: none; color: white; text-align: center; cursor: pointer; }
    .btn-primary { background-color: #007bff; border-color: #007bff; }
    .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
</style>