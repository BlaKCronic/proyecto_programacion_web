<?php
require_once "config/config.php";
require_once "models/producto.php";
require_once "models/categoria.php";

$appProducto = new Producto();
$appCategoria = new Categoria();

$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'relevancia';

$productos = [];
if(!empty($termino)) {
    $productos = $appProducto->buscar($termino, $categoria_id);
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
$total_resultados = count($productos);

$pageTitle = !empty($termino) ? "Resultados para: $termino - Amazon Lite" : 'Búsqueda - Amazon Lite';
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
                    <h6 class="fw-bold mb-3">Categoría</h6>
                    <div class="list-group list-group-flush mb-4">
                        <a href="busqueda.php?q=<?= urlencode($termino) ?>" 
                           class="list-group-item list-group-item-action <?= !$categoria_id ? 'active' : '' ?>">
                            Todas las categorías
                        </a>
                        <?php foreach($categorias as $cat): ?>
                            <a href="busqueda.php?q=<?= urlencode($termino) ?>&categoria=<?= $cat['id_categoria'] ?>" 
                               class="list-group-item list-group-item-action <?= $categoria_id == $cat['id_categoria'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if($categoria_id): ?>
                        <a href="busqueda.php?q=<?= urlencode($termino) ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-x-circle"></i> Limpiar filtros
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="mb-4">
                <?php if(!empty($termino)): ?>
                    <h2>Resultados para: "<?= htmlspecialchars($termino) ?>"</h2>
                    <?php if($categoria_id): ?>
                        <?php 
                        $cat_nombre = '';
                        foreach($categorias as $cat) {
                            if($cat['id_categoria'] == $categoria_id) {
                                $cat_nombre = $cat['nombre'];
                                break;
                            }
                        }
                        ?>
                        <p class="text-muted">en <?= htmlspecialchars($cat_nombre) ?></p>
                    <?php endif; ?>
                    <p class="text-muted"><?= $total_resultados ?> resultados encontrados</p>
                <?php else: ?>
                    <h2>Búsqueda</h2>
                    <p class="text-muted">Ingresa un término para buscar productos</p>
                <?php endif; ?>
            </div>

            <div class="card mb-4 bg-light border-0">
                <div class="card-body">
                    <form action="busqueda.php" method="GET" class="row g-3">
                        <div class="col-md-10">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" name="q" 
                                       placeholder="¿Qué estás buscando?" 
                                       value="<?= htmlspecialchars($termino) ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-warning btn-lg w-100">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if(!empty($termino)): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div></div>
                    <div class="d-flex align-items-center">
                        <label class="me-2">Ordenar por:</label>
                        <select class="form-select" id="ordenSelect" style="width: 200px;">
                            <option value="relevancia" <?= $orden == 'relevancia' ? 'selected' : '' ?>>Relevancia</option>
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
                                            <?php
                                                $rp = null;
                                                $val = $producto['imagen_principal'];
                                                if(strpos($val, 'data:') === 0) {
                                                    $rp = $val;
                                                } else {
                                                    $rp = 'img/productos/' . $val;
                                                }
                                            ?>
                                            <?php if(!empty($rp)): ?>
                                                <img src="<?= $rp ?>" 
                                                     class="card-img-top p-3" alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                     style="height: 200px; object-fit: contain;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="height: 200px;">
                                                    <i class="bi bi-image fs-1 text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="bi bi-image fs-1 text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Badges -->
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
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-shop"></i> 
                                                <?= htmlspecialchars(substr($producto['nombre_tienda'], 0, 15)) ?>
                                                <?= strlen($producto['nombre_tienda']) > 15 ? '...' : '' ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <a href="productos.php?categoria=<?= $producto['id_categoria'] ?>" 
                                               class="badge bg-light text-dark text-decoration-none">
                                                <?= htmlspecialchars($producto['categoria']) ?>
                                            </a>
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
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                            <h4>No se encontraron resultados</h4>
                            <p class="text-muted mb-4">
                                No encontramos productos que coincidan con "<?= htmlspecialchars($termino) ?>"
                            </p>
                            
                            <div class="mb-4">
                                <strong>Sugerencias:</strong>
                                <ul class="list-unstyled mt-2">
                                    <li><i class="bi bi-check2 text-success"></i> Verifica la ortografía</li>
                                    <li><i class="bi bi-check2 text-success"></i> Intenta con palabras más generales</li>
                                    <li><i class="bi bi-check2 text-success"></i> Usa menos palabras clave</li>
                                    <li><i class="bi bi-check2 text-success"></i> Prueba con sinónimos</li>
                                </ul>
                            </div>
                            
                            <a href="productos.php" class="btn btn-warning">
                                Ver todos los productos
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search fs-1 text-warning mb-3 d-block"></i>
                        <h4>Encuentra lo que buscas</h4>
                        <p class="text-muted mb-4">
                            Utiliza la barra de búsqueda para encontrar productos
                        </p>
                        
                        <div class="mt-4">
                            <h5 class="mb-3">Búsquedas populares:</h5>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="busqueda.php?q=laptop" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-laptop"></i> Laptop
                                </a>
                                <a href="busqueda.php?q=celular" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-phone"></i> Celular
                                </a>
                                <a href="busqueda.php?q=audífonos" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-headphones"></i> Audífonos
                                </a>
                                <a href="busqueda.php?q=teclado" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-keyboard"></i> Teclado
                                </a>
                                <a href="busqueda.php?q=mouse" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-mouse"></i> Mouse
                                </a>
                                <a href="busqueda.php?q=monitor" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-display"></i> Monitor
                                </a>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <a href="productos.php" class="btn btn-warning">
                            Explorar todos los productos
                        </a>
                    </div>
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
    line-clamp: 2;
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
<?php if(!empty($termino)): ?>
document.getElementById('ordenSelect').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('orden', this.value);
    window.location.href = url.toString();
});
<?php endif; ?>

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
            alert(data.message || 'Error al agregar al carrito');
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