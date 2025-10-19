<?php
require_once "config/config.php";
require_once "models/producto.php";
require_once "models/categoria.php";

$appProducto = new Producto();
$appCategoria = new Categoria();

// Obtener productos destacados y nuevos
$productos_destacados = $appProducto->readDestacados(8);
$productos_nuevos = $appProducto->readNuevos(8);
$categorias = $appCategoria->read();

$pageTitle = 'Amazon Lite - Tu tienda en línea';
include_once "views/header.php";
?>

<!-- Carousel de Banners -->
<div id="carouselPrincipal" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselPrincipal" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#carouselPrincipal" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#carouselPrincipal" data-bs-slide-to="2"></button>
    </div>
    
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="banner-slide bg-gradient-primary">
                <div class="container">
                    <div class="row align-items-center" style="min-height: 400px;">
                        <div class="col-md-6">
                            <h1 class="display-4 fw-bold text-white mb-3">Ofertas increíbles</h1>
                            <p class="lead text-white mb-4">Descubre los mejores productos con descuentos de hasta 50%</p>
                            <a href="productos.php?destacados=1" class="btn btn-warning btn-lg">Ver ofertas</a>
                        </div>
                        <div class="col-md-6 text-center">
                            <i class="bi bi-tag-fill text-white" style="font-size: 200px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="carousel-item">
            <div class="banner-slide bg-gradient-success">
                <div class="container">
                    <div class="row align-items-center" style="min-height: 400px;">
                        <div class="col-md-6">
                            <h1 class="display-4 fw-bold text-white mb-3">Envío gratis</h1>
                            <p class="lead text-white mb-4">En compras mayores a <?= formatearPrecio(ENVIO_GRATIS_DESDE) ?></p>
                            <a href="productos.php" class="btn btn-warning btn-lg">Comprar ahora</a>
                        </div>
                        <div class="col-md-6 text-center">
                            <i class="bi bi-truck text-white" style="font-size: 200px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="carousel-item">
            <div class="banner-slide bg-gradient-info">
                <div class="container">
                    <div class="row align-items-center" style="min-height: 400px;">
                        <div class="col-md-6">
                            <h1 class="display-4 fw-bold text-white mb-3">Nuevos lanzamientos</h1>
                            <p class="lead text-white mb-4">Descubre los productos más recientes</p>
                            <a href="productos.php?nuevos=1" class="btn btn-warning btn-lg">Explorar</a>
                        </div>
                        <div class="col-md-6 text-center">
                            <i class="bi bi-stars text-white" style="font-size: 200px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPrincipal" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselPrincipal" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<!-- Categorías Principales -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Comprar por categoría</h2>
        <div class="row g-3">
            <?php foreach(array_slice($categorias, 0, 6) as $categoria): ?>
            <div class="col-md-2 col-6">
                <a href="productos.php?categoria=<?= $categoria['id_categoria'] ?>" class="text-decoration-none">
                    <div class="card h-100 text-center hover-lift">
                        <div class="card-body">
                            <?php if($categoria['imagen']): ?>
                                <img src="img/categorias/<?= $categoria['imagen'] ?>" 
                                     class="img-fluid mb-2" alt="<?= $categoria['nombre'] ?>"
                                     style="max-height: 80px; object-fit: contain;">
                            <?php else: ?>
                                <i class="bi bi-grid-fill fs-1 text-warning mb-2"></i>
                            <?php endif; ?>
                            <h6 class="mb-0"><?= htmlspecialchars($categoria['nombre']) ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Ofertas del Día -->
<?php if(!empty($productos_destacados)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ofertas del día</h2>
            <a href="productos.php?destacados=1" class="text-decoration-none">
                Ver todas <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach($productos_destacados as $producto): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <a href="producto_detalle.php?id=<?= $producto['id_producto'] ?>" class="text-decoration-none">
                        <?php if($producto['imagen_principal']): ?>
                            <img src="img/productos/<?= $producto['imagen_principal'] ?>" 
                                 class="card-img-top p-3" alt="<?= $producto['nombre'] ?>"
                                 style="height: 200px; object-fit: contain;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($producto['precio_descuento']): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                -<?= calcularDescuento($producto['precio'], $producto['precio_descuento']) ?>%
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="card-body">
                        <a href="producto_detalle.php?id=<?= $producto['id_producto'] ?>" 
                           class="text-decoration-none text-dark">
                            <h6 class="card-title mb-2" style="height: 40px; overflow: hidden;">
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h6>
                        </a>
                        
                        <div class="mb-2">
                            <?php if($producto['precio_descuento']): ?>
                                <span class="text-danger fw-bold fs-5">
                                    <?= formatearPrecio($producto['precio_descuento']) ?>
                                </span>
                                <small class="text-muted text-decoration-line-through ms-2">
                                    <?= formatearPrecio($producto['precio']) ?>
                                </small>
                            <?php else: ?>
                                <span class="fw-bold fs-5">
                                    <?= formatearPrecio($producto['precio']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-shop"></i> <?= htmlspecialchars($producto['nombre_tienda']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Banner Intermedio -->
<section class="py-5 bg-warning">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-3">¿Quieres vender tus productos?</h2>
                <p class="lead mb-0">Únete a miles de vendedores que confían en Amazon Lite</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="vendedor/registro.php" class="btn btn-dark btn-lg">
                    <i class="bi bi-shop"></i> Vende con nosotros
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Nuevos Lanzamientos -->
<?php if(!empty($productos_nuevos)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Nuevos lanzamientos</h2>
            <a href="productos.php?nuevos=1" class="text-decoration-none">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach($productos_nuevos as $producto): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <a href="producto_detalle.php?id=<?= $producto['id_producto'] ?>" class="text-decoration-none">
                        <?php if($producto['imagen_principal']): ?>
                            <img src="img/productos/<?= $producto['imagen_principal'] ?>" 
                                 class="card-img-top p-3" alt="<?= $producto['nombre'] ?>"
                                 style="height: 200px; object-fit: contain;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <span class="badge bg-success position-absolute top-0 start-0 m-2">
                            Nuevo
                        </span>
                    </a>
                    
                    <div class="card-body">
                        <a href="producto_detalle.php?id=<?= $producto['id_producto'] ?>" 
                           class="text-decoration-none text-dark">
                            <h6 class="card-title mb-2" style="height: 40px; overflow: hidden;">
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h6>
                        </a>
                        
                        <div class="mb-2">
                            <?php if($producto['precio_descuento']): ?>
                                <span class="text-danger fw-bold fs-5">
                                    <?= formatearPrecio($producto['precio_descuento']) ?>
                                </span>
                                <small class="text-muted text-decoration-line-through ms-2">
                                    <?= formatearPrecio($producto['precio']) ?>
                                </small>
                            <?php else: ?>
                                <span class="fw-bold fs-5">
                                    <?= formatearPrecio($producto['precio']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-shop"></i> <?= htmlspecialchars($producto['nombre_tienda']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Beneficios -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <i class="bi bi-truck fs-1 text-warning mb-3"></i>
                <h5>Envío gratis</h5>
                <p class="text-muted">En compras mayores a <?= formatearPrecio(ENVIO_GRATIS_DESDE) ?></p>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-shield-check fs-1 text-warning mb-3"></i>
                <h5>Compra segura</h5>
                <p class="text-muted">Protegemos tus datos y tu dinero</p>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-arrow-repeat fs-1 text-warning mb-3"></i>
                <h5>Devoluciones fáciles</h5>
                <p class="text-muted">30 días para devolver tu producto</p>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-headset fs-1 text-warning mb-3"></i>
                <h5>Soporte 24/7</h5>
                <p class="text-muted">Estamos aquí para ayudarte</p>
            </div>
        </div>
    </div>
</section>

<style>
.banner-slide {
    min-height: 400px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include_once "views/footer.php"; ?>