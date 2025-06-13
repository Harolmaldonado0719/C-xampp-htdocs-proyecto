<!-- filepath: app/Views/atencion_cliente/nueva_solicitud_form.php -->
<h1><?php echo htmlspecialchars($pageTitle ?? "Nueva Solicitud"); ?></h1>
<p>Envíanos tu consulta, queja, reclamo o sugerencia. Te responderemos lo antes posible.</p>

<?php if (isset($_SESSION['mensaje_error_solicitud'])): ?>
    <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_solicitud']); unset($_SESSION['mensaje_error_solicitud']); ?></div>
<?php endif; ?>
<?php $formData = $_SESSION['form_data_solicitud'] ?? []; unset($_SESSION['form_data_solicitud']); ?>

<form action="<?php echo BASE_URL; ?>atencion-cliente/guardar" method="POST" class="form-pqr">
    <div class="form-group">
        <label for="tipo_solicitud">Tipo de Solicitud: <span class="required">*</span></label>
        <select name="tipo_solicitud" id="tipo_solicitud" required>
            <option value="">Selecciona un tipo...</option>
            <?php foreach ($tipos_solicitud ?? [] as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo (isset($formData['tipo_solicitud']) && $formData['tipo_solicitud'] == $tipo) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tipo); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="email_contacto">Tu Email de Contacto: <span class="required">*</span></label>
        <input type="email" name="email_contacto" id="email_contacto" value="<?php echo htmlspecialchars($formData['email_contacto'] ?? $email_usuario ?? ''); ?>" required>
        <small>Usaremos este email para responderte sobre esta solicitud.</small>
    </div>

    <div class="form-group">
        <label for="asunto">Asunto: <span class="required">*</span></label>
        <input type="text" name="asunto" id="asunto" value="<?php echo htmlspecialchars($formData['asunto'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="descripcion">Descripción Detallada: <span class="required">*</span></label>
        <textarea name="descripcion" id="descripcion" rows="6" required><?php echo htmlspecialchars($formData['descripcion'] ?? ''); ?></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
    <a href="<?php echo BASE_URL; ?>portal_servicios" class="btn btn-secondary">Cancelar</a>
</form>

<style>
    .form-pqr { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;}
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group select,
    .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .form-group textarea { resize: vertical; }
    .form-group .required { color: red; }
    .form-group small { font-size: 0.85em; color: #555; }
    .form-pqr .btn { margin-right: 10px; }
</style>