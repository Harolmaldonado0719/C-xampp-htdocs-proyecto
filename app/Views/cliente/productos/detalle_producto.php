
<div class="container mt-5 mb-5">
    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($producto && is_array($producto)): ?>
        <div class="card shadow-lg product-detail-card">
            <div class="row g-0">
                <div class="col-md-5 text-center p-3">
                    <img src="<?php echo htmlspecialchars($producto['imagen_completa_url'] ?? (BASE_URL . 'img/placeholder_producto.png')); ?>" 
                         class="img-fluid rounded product-detail-image" 
                         alt="<?php echo htmlspecialchars($producto['nombre'] ?? 'Imagen del Producto'); ?>"
                         style="max-height: 450px; object-fit: contain;">
                </div>
                <div class="col-md-7">
                    <div class="card-body p-4">
                        <h1 class="card-title display-6 text-primary mb-3"><?php echo htmlspecialchars($producto['nombre'] ?? 'Nombre no disponible'); ?></h1>
                        
                        <?php if (isset($producto['categoria_nombre'])): ?>
                            <p class="card-text mb-2">
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-tag"></i> Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                </span>
                            </p>
                        <?php endif; ?>

                        <p class="card-text lead text-muted mb-4">
                            <?php echo nl2br(htmlspecialchars($producto['descripcion'] ?? 'Descripción no disponible.')); ?>
                        </p>
                        
                        <div class="mb-4">
                            <span class="fs-2 fw-bold text-success">
                                $<?php echo number_format($producto['precio'] ?? 0, 2, '.', ','); ?>
                            </span>
                        </div>

                        <p class="card-text mb-1">
                            <small class="text-muted">
                                Stock disponible: 
                                <?php if (isset($producto['stock']) && $producto['stock'] > 0): ?>
                                    <span class="badge bg-success">En Stock (<?php echo htmlspecialchars($producto['stock']); ?> unidades)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Agotado</span>
                                <?php endif; ?>
                            </small>
                        </p>
                        
                        <!-- Futuros botones de acción -->
                        <div class="mt-4 pt-3 border-top">
                            <!-- <button class="btn btn-lg btn-success me-2" <?php echo (!isset($producto['stock']) || $producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus"></i> Añadir al Carrito
                            </button>
                            <button class="btn btn-lg btn-outline-secondary">
                                <i class="fas fa-heart"></i> Añadir a Favoritos
                            </button> -->
                            <p class="text-muted"><small>Funcionalidad de carrito y favoritos pendiente.</small></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL . 'catalogo'; ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>

    <?php else: ?>
        <?php // El controlador ya debería haber manejado el caso de producto no encontrado y mostrado un error 404.
              // Esto es un fallback adicional si por alguna razón $producto es null aquí sin un error previo.
        ?>
        <div class="alert alert-warning text-center" role="alert">
            <i class="fas fa-exclamation-triangle"></i> El producto que buscas no está disponible o no se pudo cargar la información.
        </div>
        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL . 'catalogo'; ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>
    <?php endif; ?>
</div>
<style>
    .product-detail-card {
        border: none;
    }
    .product-detail-image {
        transition: transform 0.3s ease-in-out;
    }
    .product-detail-image:hover {
        transform: scale(1.05);
    }
</style>