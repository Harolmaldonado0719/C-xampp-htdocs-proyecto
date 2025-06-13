
<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 text-center text-primary"><?php echo htmlspecialchars($pageTitle ?? "Nuestro Catálogo"); ?></h1>
            <p class="lead text-center text-muted">Explora nuestra selección de productos de alta calidad.</p>
        </div>
    </div>

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

    <?php if (!empty($productos) && is_array($productos)): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($productos as $producto): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm product-card-hover">
                        <a href="<?php echo BASE_URL . 'producto/' . htmlspecialchars($producto['id']); ?>" class="text-decoration-none text-dark">
                            <img src="<?php echo htmlspecialchars($producto['imagen_completa_url'] ?? (BASE_URL . 'img/placeholder_producto.png')); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($producto['nombre'] ?? 'Producto'); ?>" 
                                 style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="<?php echo BASE_URL . 'producto/' . htmlspecialchars($producto['id']); ?>" class="text-decoration-none text-primary">
                                    <?php echo htmlspecialchars($producto['nombre'] ?? 'Nombre no disponible'); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small">
                                <?php 
                                    $descripcion_corta = $producto['descripcion'] ?? 'Descripción no disponible.';
                                    echo htmlspecialchars(mb_strimwidth($descripcion_corta, 0, 70, "...")); 
                                ?>
                            </p>
                            <div class="mt-auto">
                                <p class="card-text fs-5 fw-bold text-success mb-2">
                                    $<?php echo number_format($producto['precio'] ?? 0, 2, '.', ','); ?>
                                </p>
                                <a href="<?php echo BASE_URL . 'producto/' . htmlspecialchars($producto['id']); ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                                <!-- Futuro botón de añadir al carrito -->
                                <!-- <button class="btn btn-success btn-sm w-100 mt-2"><i class="fas fa-cart-plus"></i> Añadir al Carrito</button> -->
                            </div>
                        </div>
                        <?php if (isset($producto['categoria_nombre'])): ?>
                        <div class="card-footer bg-transparent border-top-0">
                            <small class="text-muted">Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle"></i> No hay productos disponibles en este momento. ¡Vuelve pronto!
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .product-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-img-top {
        border-bottom: 1px solid #eee;
    }
</style>