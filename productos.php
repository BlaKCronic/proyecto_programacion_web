<?php
require_once "config/config.php";
require_once "models/producto.php";
require_once "models/categoria.php";

$appProducto = new Producto();
$appCategoria = new Categoria();

$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$destacados = isset($_GET['destacados']) ? true : false;
$nuevos = isset($_GET['nuevos']) ? true : false;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

if($destacados) {
    $productos = $appProducto->readDestacados(50);
    $titulo = 'Ofertas del día';
} elseif($nuevos) {
    $productos = $appProducto->readNuevos(50);
    $titulo = 'Nuevos lanzamientos';
} elseif($categoria_id) {
    $productos = $appProducto->readByCategoria($categoria_id);
    $categoria_info = $appCategoria->readOne($categoria_id);
    $titulo = $categoria_info ? $categoria_info['nombre'] : 'Productos';
} else {
    $productos = $appProducto->read();
    $titulo = 'Todos los productos';
}

if($orden == 'precio_asc') {
    usort($productos, function($a, $b) {
        $precioA = $a['precio_descuento'] ?? $a['precio'];
        $precioB = $b['precio_descuento'] ?? $b['precio'];
        return $precioA - $precioB;
    });
} elseif($orden == 'precio_desc') {
    usort($productos, function($a, $b) {
        $precioA = $a['precio_descuento'] ?? $a['precio'];
        $precioB = $b['precio_descuento'] ?? $b['precio'];
        return $precioB - $precioA;
    });
} elseif($orden == 'nombre') {
    usort($productos, function($a, $b) {
        return strcmp($a['nombre'], $b['nombre']);
    });
}

$categorias = $appCategoria->read();
$total_productos = count($productos);

$pageTitle = $titulo . ' - Amazon Lite';
include_once "views/header.php";
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Categorías</h6>
                    <div class="list-group list-group-flush mb-4">
                        <a href="productos.php" 
                           class="list-group-item list-group-item-action <?= !$categoria_id && !$destacados && !$nuevos ? 'active' : '' ?>">
                            Todas las categorías
                        </a>
                        <?php foreach($categorias as $cat): ?>
                            <a href="productos.php?categoria=<?= $cat['id_categoria'] ?>" 
                               class="list-group-item list-group-item-action <?= $categoria_id == $cat['id_categoria'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <h6 class="fw-bold mb-3">Ofertas especiales</h6>
                    <div class="list-group list-group-flush">
                        <a href="productos.php?destacados=1" 
                           class="list-group-item list-group-item-action <?= $destacados ? 'active' : '' ?>">
                            <i class="bi bi-tag-fill text-danger me-2"></i>Ofertas del día
                        </a>
                        <a href="productos.php?nuevos=1" 
                           class="list-group-item list-group-item-action <?= $nuevos ? 'active' : '' ?>">
                            <i class="bi bi-stars text-success me-2"></i>Nuevos lanzamientos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><?= htmlspecialchars($titulo) ?></h2>
                    <p class="text-muted mb-0"><?= $total_productos ?> resultados</p>
                </div>
                
                <div class="d-flex align-items-center">
                    <label class="me-2">Ordenar por:</label>
                    <select class="form-select" id="ordenSelect" style="width: 200px;">
                        <option value="recientes" <?= $orden == 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                        <option value="precio_asc" <?= $orden == 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                        <option value="precio_desc" <?= $orden == 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                        <option value="nombre" <?= $orden == 'nombre' ? 'selected' : '' ?>>Nombre: A-Z</option>
                    </select>
                </div>
            </div>

            <?php if(!empty($productos)): ?>
                <div class="row g-4">
                    <?php foreach($productos as $producto): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <a href="producto_detalle.php?id=<?= $producto['id_producto'] ?>" 
                                   class="text-decoration-none position-relative">
                                    <?php if($producto['imagen_principal']): ?>
                                        <img src="img/productos/<?= $producto['imagen_principal'] ?>" 
                                             class="card-img-top p-3" alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                             style="height: 200px; object-fit: contain;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-image fs-1 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($producto['precio_descuento']): ?>
                                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                            -<?= calcularDescuento($producto['precio'], $producto['precio_descuento']) ?>%
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($producto['stock'] <= 0): ?>
                                        <span class="badge bg-secondary position-absolute top-0 end-0 m-2">
                                            Agotado
                                        </span>
                                    <?php elseif($producto['stock'] < 10): ?>
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-2">
                                            Solo <?= $producto['stock'] ?> disponibles
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
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-shop"></i> 
                                            <?= htmlspecialchars(substr($producto['nombre_tienda'], 0, 15)) ?>
                                            <?= strlen($producto['nombre_tienda']) > 15 ? '...' : '' ?>
                                        </small>
                                    </div>
                                    
                                    <?php if($producto['stock'] > 0): ?>
                                        <button class="btn btn-warning btn-sm w-100 btn-add-cart" 
                                                data-producto-id="<?= $producto['id_producto'] ?>"
                                                <?= !estaLogueado() ? 'onclick="location.href=\'login.php\'"' : '' ?>>
                                            <i class="bi bi-cart-plus"></i> Agregar al carrito
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm w-100" disabled>
                                            <i class="bi bi-x-circle"></i> No disponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h4>No se encontraron productos</h4>
                    <p class="mb-3">Intenta con otros filtros o categorías</p>
                    <a href="productos.php" class="btn btn-warning">Ver todos los productos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}

.card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.list-group-item.active {
    background-color: #febd69;
    border-color: #febd69;
    color: #111;
}
</style>

<script>
document.getElementById('ordenSelect').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('orden', this.value);
    window.location.href = url.toString();
});

<?php if(estaLogueado()): ?>
document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', function() {
        const productoId = this.dataset.productoId;
        agregarAlCarrito(productoId);
    });
});

function agregarAlCarrito(productoId) {
    fetch('api/carrito_add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ producto_id: productoId, cantidad: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Producto agregado al carrito');
            location.reload();
        } else {
            alert('Error al agregar al carrito');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar al carrito');
    });
}
<?php endif; ?>
</script>

<?php include_once "views/footer.php"; ?>