
<?php
// Asegurarse de que las variables de sesión para mensajes se muestren y limpien
$mensaje_exito = $_SESSION['mensaje_exito_global'] ?? null;
$mensaje_error = $_SESSION['mensaje_error_global'] ?? null;
$mensaje_info = $_SESSION['mensaje_info_global'] ?? null;

unset($_SESSION['mensaje_exito_global']);
unset($_SESSION['mensaje_error_global']);
unset($_SESSION['mensaje_info_global']);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo htmlspecialchars($pageTitle ?? 'Gestionar Productos'); ?></h1>
        <a href="<?php echo BASE_URL . 'empleado/productos/crear'; ?>" class="btn btn-success btn-lg">
            <i class="fas fa-plus-circle"></i> Añadir Nuevo Producto
        </a>
    </div>

    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje_exito); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($mensaje_info): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje_info); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <div class="alert alert-info">
            No hay productos registrados actualmente. ¡Añade el primero!
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($producto['id']); ?></th>
                            <td>
                                <img src="<?php echo htmlspecialchars($producto['imagen_completa_url'] ?? (BASE_URL . 'img/placeholder.png')); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'N/A'); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format((float)($producto['precio'] ?? 0), 2)); ?></td>
                            <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL . 'empleado/productos/editar/' . htmlspecialchars($producto['id']); ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Editar</span>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar" 
                                        onclick="confirmarEliminacionProducto('<?php echo htmlspecialchars($producto['id']); ?>', '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>')">
                                    <i class="fas fa-trash-alt"></i> <span class="d-none d-md-inline">Eliminar</span>
                                </button>
                                <!-- Formulario oculto para la eliminación -->
                                <form id="form-eliminar-producto-<?php echo htmlspecialchars($producto['id']); ?>" 
                                      action="<?php echo BASE_URL . 'empleado/productos/eliminar/' . htmlspecialchars($producto['id']); ?>" 
                                      method="POST" style="display: none;">
                                    <?php /* Para simular DELETE si tu enrutador lo maneja así, sino simplemente POST */ ?>
                                    <!-- <input type="hidden" name="_method" value="DELETE"> -->
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Confirmación de Eliminación -->
<!-- ESTE ES EL MODAL: Asegúrate de que no tenga la clase 'show' aquí -->
<div class="modal fade" id="confirmarEliminacionModalProducto" tabindex="-1" aria-labelledby="confirmarEliminacionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmarEliminacionModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar el producto "<strong id="nombreProductoEliminar"></strong>"? Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="botonConfirmarEliminacionProducto">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
function confirmarEliminacionProducto(idProducto, nombreProducto) {
    document.getElementById('nombreProductoEliminar').textContent = nombreProducto;
    
    const modalElement = document.getElementById('confirmarEliminacionModalProducto');
    // Obtener instancia existente o crear una nueva si no existe
    const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    
    document.getElementById('botonConfirmarEliminacionProducto').onclick = function() {
        document.getElementById('form-eliminar-producto-' + idProducto).submit();
    };
    modal.show(); // Aquí es donde se muestra el modal
}
</script>