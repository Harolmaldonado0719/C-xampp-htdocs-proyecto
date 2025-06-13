
<?php
/

$is_editing = true; // Siempre es verdadero para este archivo
$id_producto_edicion = $id ?? ($producto_actual['id'] ?? null);

if (!$id_producto_edicion) {
    // Esto no debería ocurrir si el controlador está configurado correctamente para la edición.
    echo "<div class='container mt-4'><div class='alert alert-danger'>Error: ID del producto no especificado para la edición. Por favor, regrese y vuelva a intentarlo.</div></div>";
    // Podrías incluir aquí el footer o detener la ejecución si es un error crítico.
    return; 
}

// Título de la página y URL de acción (el controlador debería pasarlos, pero podemos tener defaults)
$pageTitle = $pageTitle ?? 'Editar Producto: ' . htmlspecialchars($producto_actual['nombre'] ?? 'Desconocido');
$action_url = $action_url ?? (BASE_URL . 'empleado/productos/actualizar/' . $id_producto_edicion);

// Clave para los datos y errores en sesión
$form_data_key = 'editar_producto_' . $id_producto_edicion;

// Prioridad para $form_data_final:
// 1. Datos de sesión (si hubo un error de validación y se redirigió).
// 2. $producto_actual (datos actuales del producto, primera carga del form de edición).
$form_data_from_session = $_SESSION['form_data'][$form_data_key] ?? [];

if (!empty($form_data_from_session)) {
    $form_data_final = $form_data_from_session; // Usar datos de sesión si existen (error previo)
} elseif (isset($producto_actual)) {
    $form_data_final = $producto_actual; // Usar datos del producto actual en edición (primera carga)
} else {
    // Si no hay $producto_actual, es un error.
    echo "<div class='container mt-4'><div class='alert alert-danger'>Error: No se pudieron cargar los datos del producto para editar. Producto ID: " . htmlspecialchars($id_producto_edicion) . "</div></div>";
    return;
}

// Errores del formulario desde la sesión
$form_errors = $_SESSION['form_errors'][$form_data_key] ?? [];

// Limpiar de la sesión después de usarlos para evitar que persistan en otras páginas
unset($_SESSION['form_data'][$form_data_key]);
unset($_SESSION['form_errors'][$form_data_key]);

// Preparar valores para los campos del formulario
$nombre_val = htmlspecialchars($form_data_final['nombre'] ?? '');
$descripcion_val = htmlspecialchars($form_data_final['descripcion'] ?? '');

$precio_val_raw = $form_data_final['precio'] ?? '';
$precio_val = '';
if ($precio_val_raw !== '') {
    $precio_val = is_numeric($precio_val_raw) ? number_format((float)$precio_val_raw, 2, '.', '') : htmlspecialchars($precio_val_raw);
}

$stock_val = htmlspecialchars($form_data_final['stock'] ?? '');
$categoria_id_val = $form_data_final['categoria_id'] ?? '';

// Imagen actual:
// Si $form_data_final tiene 'imagen_url_actual' (pasado por el controlador en error de actualización), usar esa.
// Sino, si $form_data_final tiene 'imagen_url' (podría ser de un intento fallido de subida), usar esa.
// Sino, usar la imagen de $producto_actual.
$imagen_url_actual = $form_data_final['imagen_url_actual'] ?? ($form_data_final['imagen_url'] ?? ($producto_actual['imagen_url'] ?? null));


// Valor para el checkbox 'activo'
if (isset($form_data_final['activo'])) {
    $activo_val = $form_data_final['activo'];
} elseif (isset($producto_actual['activo'])) {
    $activo_val = $producto_actual['activo'];
} else {
    $activo_val = 0; // Default a inactivo si no está presente, aunque debería estarlo.
}

// Mensaje de error general del formulario (si existe)
$mensaje_error_form = null;
if (isset($form_errors['general'])) {
    $mensaje_error_form = $form_errors['general'];
    unset($form_errors['general']); // No mostrarlo dos veces si se lista abajo
}

// Categorías: El controlador debe pasar la variable $categorias
$categorias_finales = $categorias ?? [];
?>
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($mensaje_error_form): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje_error_form); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Mostrar errores de campos específicos si los hay
                    $field_specific_errors = array_filter($form_errors, fn($key) => $key !== 'general', ARRAY_FILTER_USE_KEY);
                    if (!empty($field_specific_errors)):
                        $all_field_errors_display = [];
                        foreach ($field_specific_errors as $errors_for_field) {
                            $messages = is_array($errors_for_field) ? $errors_for_field : [$errors_for_field];
                            foreach ($messages as $msg) {
                                $all_field_errors_display[] = htmlspecialchars($msg);
                            }
                        }
                        $all_field_errors_display = array_unique($all_field_errors_display);
                        if (!empty($all_field_errors_display)):
                    ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Por favor corrige los siguientes errores:</strong>
                            <ul>
                                <?php foreach ($all_field_errors_display as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php
                        endif;
                    endif;
                    ?>

                    <form action="<?php echo $action_url; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_producto_edicion); ?>">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($form_errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" value="<?php echo $nombre_val; ?>" required maxlength="100">
                            <?php if (isset($form_errors['nombre'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars(is_array($form_errors['nombre']) ? $form_errors['nombre'][0] : $form_errors['nombre']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control <?php echo isset($form_errors['descripcion']) ? 'is-invalid' : ''; ?>" id="descripcion" name="descripcion" rows="3" maxlength="1000"><?php echo $descripcion_val; ?></textarea>
                            <?php if (isset($form_errors['descripcion'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars(is_array($form_errors['descripcion']) ? $form_errors['descripcion'][0] : $form_errors['descripcion']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio (COP) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">COP</span>
                                    <input type="text" class="form-control <?php echo isset($form_errors['precio']) ? 'is-invalid' : ''; ?>" id="precio" name="precio" value="<?php echo $precio_val; ?>" required pattern="^\d{1,8}(\.\d{1,2})?$" placeholder="Ej: 50000.00" title="Ingrese un número válido, ej: 12345.67 o 12345. Máximo 8 dígitos antes del punto y 2 decimales.">
                                </div>
                                <?php if (isset($form_errors['precio'])): ?>
                                    <div class="invalid-feedback d-block">
                                        <?php echo htmlspecialchars(is_array($form_errors['precio']) ? $form_errors['precio'][0] : $form_errors['precio']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php echo isset($form_errors['stock']) ? 'is-invalid' : ''; ?>" id="stock" name="stock" value="<?php echo $stock_val; ?>" required step="1" min="0" max="99999" placeholder="Ej: 10">
                                <?php if (isset($form_errors['stock'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars(is_array($form_errors['stock']) ? $form_errors['stock'][0] : $form_errors['stock']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo isset($form_errors['categoria_id']) ? 'is-invalid' : ''; ?>" id="categoria_id" name="categoria_id" required>
                                <option value="">Selecciona una categoría...</option>
                                <?php if (!empty($categorias_finales)): ?>
                                    <?php foreach ($categorias_finales as $categoria): ?>
                                        <?php
                                            $cat_id = htmlspecialchars($categoria['id'] ?? '');
                                            $cat_nombre = htmlspecialchars($categoria['nombre'] ?? 'Categoría sin nombre');
                                            $selected_attr = ($categoria_id_val == $cat_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $cat_id; ?>" <?php echo $selected_attr; ?>>
                                            <?php echo $cat_nombre; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay categorías disponibles.</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($form_errors['categoria_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars(is_array($form_errors['categoria_id']) ? $form_errors['categoria_id'][0] : $form_errors['categoria_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_url" class="form-label">Imagen del Producto (Opcional: subir para reemplazar)</label>
                            <input type="file" class="form-control <?php echo isset($form_errors['imagen_url']) ? 'is-invalid' : ''; ?>" id="imagen_url" name="imagen_url" accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WEBP. Max: 2MB.</small>
                            <?php if ($imagen_url_actual): ?>
                                <div class="mt-2">
                                    <p class="mb-1"><small>Imagen actual:</small></p>
                                    <img src="<?php echo BASE_URL . (defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? rtrim(APP_UPLOAD_DIR_PUBLIC_PATH, '/') . '/' : 'uploads/') . htmlspecialchars($imagen_url_actual); ?>"
                                         alt="Imagen actual del producto"
                                         style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover; border:1px solid #ddd;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="eliminar_imagen_actual" id="eliminar_imagen_actual" value="1" <?php echo (isset($form_data_final['eliminar_imagen_actual']) && $form_data_final['eliminar_imagen_actual'] == '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="eliminar_imagen_actual"><small>Eliminar imagen actual al guardar</small></label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($form_errors['imagen_url'])): ?>
                                <div class="invalid-feedback d-block"><?php echo htmlspecialchars(is_array($form_errors['imagen_url']) ? $form_errors['imagen_url'][0] : $form_errors['imagen_url']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" <?php echo ($activo_val == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">Producto Activo</label>
                            <small class="form-text text-muted d-block">Desmarca esta casilla para que el producto no sea visible para los clientes en el catálogo.</small>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo BASE_URL . 'empleado/productos'; ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save"></i> Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script para validación de Bootstrap (cliente-side)
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>