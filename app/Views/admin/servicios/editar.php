<?php
// Variables esperadas: $pageTitle, $active_page, $servicio (datos originales), $form_data (datos del intento fallido o originales), $form_errors
?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($pageTitle); ?></h2>

    <?php include __DIR__ . '/../../partials/mensajes.php'; ?>

    <form action="<?php echo BASE_URL; ?>admin/servicios/actualizar/<?php echo $servicio['id']; ?>" method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?php echo isset($form_errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo htmlspecialchars($form_data['nombre'] ?? $servicio['nombre']); ?>" required>
            <?php if (isset($form_errors['nombre'])): ?>
                <div class="invalid-feedback"><?php echo $form_errors['nombre']; ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? $servicio['descripcion']); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="duracion_minutos" class="form-label">Duración (minutos) <span class="text-danger">*</span></label>
                <input type="number" class="form-control <?php echo isset($form_errors['duracion_minutos']) ? 'is-invalid' : ''; ?>" id="duracion_minutos" name="duracion_minutos" value="<?php echo htmlspecialchars($form_data['duracion_minutos'] ?? $servicio['duracion_minutos']); ?>" required min="1">
                <?php if (isset($form_errors['duracion_minutos'])): ?>
                    <div class="invalid-feedback"><?php echo $form_errors['duracion_minutos']; ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
                 <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control <?php echo isset($form_errors['precio']) ? 'is-invalid' : ''; ?>" id="precio" name="precio" value="<?php echo htmlspecialchars($form_data['precio'] ?? $servicio['precio']); ?>" required min="0">
                </div>
                <?php if (isset($form_errors['precio'])): ?>
                    <div class="invalid-feedback d-block"><?php echo $form_errors['precio']; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" 
                <?php 
                // Si hay form_data (intento fallido), usar ese valor. Si no, usar el valor de $servicio.
                $checked = (isset($form_data['activo']) && $form_data['activo'] == 1) || (!isset($form_data['activo']) && $servicio['activo'] == 1);
                echo $checked ? 'checked' : ''; 
                ?>>
            <label class="form-check-label" for="activo">Activo</label>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
        <a href="<?php echo BASE_URL; ?>admin/servicios" class="btn btn-secondary">Cancelar</a>
    </form>
</div>