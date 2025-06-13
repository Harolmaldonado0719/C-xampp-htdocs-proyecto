
<?php
// Estas variables deben ser pasadas por el controlador:
// $is_editing (bool), $id (int, opcional para edición), $action_url (string),
// $pageTitle (string), $producto_actual (array, opcional para edición),
// $categorias (array), $form_data (array, opcional), $form_errors (array, opcional)

$is_editing = $is_editing ?? false;
$id_producto_edicion = $id ?? null; // ID del producto si se está editando

// La URL de acción es crucial y debe ser establecida por el controlador.
// Si no se pasa, se intenta construir una por defecto, pero es mejor que el controlador la defina.
$action_url = $action_url ?? ($is_editing && $id_producto_edicion ? (BASE_URL . 'empleado/productos/actualizar/' . $id_producto_edicion) : (BASE_URL . 'empleado/productos/guardar'));
$pageTitle = $pageTitle ?? ($is_editing ? 'Editar Producto' : 'Añadir Nuevo Producto');

// Clave para los datos y errores en sesión
$form_data_key = $is_editing && $id_producto_edicion ? 'editar_producto_' . $id_producto_edicion : 'crear_producto';

// Prioridad para $form_data:
// 1. Datos de sesión (si hubo un error de validación y se redirigió).
// 2. $producto_actual (si se está editando y es la primera carga del formulario).
// 3. Array vacío (si se está creando y es la primera carga).
$form_data_from_session = $_SESSION['form_data'][$form_data_key] ?? [];

if (!empty($form_data_from_session)) {
    $form_data_final = $form_data_from_session; // Usar datos de sesión si existen (error previo)
} elseif ($is_editing && isset($producto_actual)) {
    $form_data_final = $producto_actual; // Usar datos del producto actual en edición (primera carga)
} else {
    $form_data_final = []; // Formulario vacío para creación (primera carga)
}

// Errores del formulario desde la sesión
$form_errors = $_SESSION['form_errors'][$form_data_key] ?? [];

// Limpiar de la sesión después de usarlos para evitar que persistan
unset($_SESSION['form_data'][$form_data_key]);
unset($_SESSION['form_errors'][$form_data_key]);

// Preparar valores para los campos del formulario
$nombre_val = htmlspecialchars($form_data_final['nombre'] ?? '');
$descripcion_val = htmlspecialchars($form_data_final['descripcion'] ?? '');

$precio_val_raw = $form_data_final['precio'] ?? '';
$precio_val = '';
if ($precio_val_raw !== '') {
    // Si es numérico, formatear. Si no (ej. error de validación que devolvió el string original), mostrar tal cual.
    $precio_val = is_numeric($precio_val_raw) ? number_format((float)$precio_val_raw, 2, '.', '') : htmlspecialchars($precio_val_raw);
}

$stock_val = htmlspecialchars($form_data_final['stock'] ?? '');
$categoria_id_val = $form_data_final['categoria_id'] ?? '';

// Imagen actual: si estamos editando y $form_data_final tiene 'imagen_url', usar esa.
// Si no, y $producto_actual tiene 'imagen_url', usar esa.
// Si $form_data_final tiene 'imagen_url_actual' (pasado por el controlador en error de actualización), usar esa.
$imagen_url_actual = $form_data_final['imagen_url_actual'] ?? ($form_data_final['imagen_url'] ?? ($is_editing && isset($producto_actual['imagen_url']) ? $producto_actual['imagen_url'] : null));

// Valor para el checkbox 'activo'
// Prioridad:
// 1. Valor de $form_data_final['activo'] (si viene de un POST previo con error).
// 2. Valor de $producto_actual['activo'] (si se está editando y es primera carga).
// 3. Default a 1 (activo) para creación o si no se encuentra.
if (isset($form_data_final['activo'])) {
    $activo_val = $form_data_final['activo'];
} elseif ($is_editing && isset($producto_actual['activo'])) {
    $activo_val = $producto_actual['activo'];
} else {
    $activo_val = 1; // Por defecto, activo al crear o si no se especifica
}


// Mensaje de error general del formulario (si existe)
$mensaje_error_form = null;
if (isset($form_errors['general'])) {
    $mensaje_error_form = $form_errors['general'];
    unset($form_errors['general']); // No mostrarlo dos veces
} elseif (isset($_SESSION['mensaje_error_global_form'])) { // Otra posible variable de sesión
    $mensaje_error_form = $_SESSION['mensaje_error_global_form'];
    unset($_SESSION['mensaje_error_global_form']);
}

// Categorías: El controlador debe pasar la variable $categorias
$categorias_finales = $categorias ?? [];
?>
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header <?php echo $is_editing ? 'bg-warning text-dark' : 'bg-primary text-white'; ?>">
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
                        foreach ($field_specific_errors as $field_name => $errors_for_field) {
                            $messages = is_array($errors_for_field) ? $errors_for_field : [$errors_for_field];
                            foreach ($messages as $msg) {
                                // Podrías añadir el nombre del campo al mensaje si quieres: ucfirst($field_name) . ": " . htmlspecialchars($msg)
                                $all_field_errors_display[] = htmlspecialchars($msg);
                            }
                        }
                        $all_field_errors_display = array_unique($all_field_errors_display); // Evitar duplicados
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
                        <?php if ($is_editing && $id_producto_edicion): ?>
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_producto_edicion); ?>">
                        <?php endif; ?>

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
                                    <!-- Usar type="text" para permitir el formato con punto y que la validación del navegador no interfiera tanto con el pattern.
                                         La validación numérica real se hace en el servidor. -->
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
                                    <option value="" disabled>No hay categorías disponibles. Por favor, añada categorías desde la gestión correspondiente.</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($form_errors['categoria_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars(is_array($form_errors['categoria_id']) ? $form_errors['categoria_id'][0] : $form_errors['categoria_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_url" class="form-label">Imagen del Producto <?php echo $is_editing && $imagen_url_actual ? '(Opcional: subir para reemplazar)' : ''; ?></label>
                            <input type="file" class="form-control <?php echo isset($form_errors['imagen_url']) ? 'is-invalid' : ''; ?>" id="imagen_url" name="imagen_url" accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WEBP. Max: 2MB.</small>
                            <?php if ($is_editing && $imagen_url_actual): ?>
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
                            <button type="submit" class="btn <?php echo $is_editing ? 'btn-warning' : 'btn-primary'; ?> btn-lg">
                                <i class="fas fa-save"></i> <?php echo $is_editing ? 'Actualizar Producto' : 'Guardar Producto'; ?>
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