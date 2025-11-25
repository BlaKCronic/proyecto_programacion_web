<?php
require_once "config/config.php";
require_once "models/producto.php";
require_once "models/resena.php";

$appProducto = new Producto();
$appResena = new Resena();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id <= 0) {
    header("Location: productos.php");
    exit();
}

$producto = $appProducto->readOne($id);

if(!$producto) {
    header("Location: productos.php");
    exit();
}

$resenas = $appResena->readByProducto($id);
$estadisticas_resenas = $appResena->obtenerPromedioCalificacion($id);
$distribucion = $appResena->obtenerDistribucionCalificaciones($id);

$productos_relacionados = $appProducto->readByCategoria($producto['id_categoria']);
$productos_relacionados = array_filter($productos_relacionados, function($p) use ($id) {
    return $p['id_producto'] != $id;
});
$productos_relacionados = array_slice($productos_relacionados, 0, 4);
$principalSrc = null;
if(!empty($producto['imagen_principal'])) {
    $val = $producto['imagen_principal'];
    if(strpos($val, 'data:') === 0) {
        $principalSrc = $val;
    } else {
        $principalSrc = 'img/productos/' . $val;
    }
}

$imagenes_adicionales_arr = [];
if(!empty($producto['imagenes_adicionales'])) {
    $raw = trim($producto['imagenes_adicionales']);
    $arr = null;
    if(strlen($raw) > 0 && $raw[0] === '[') {
        $arr = json_decode($raw, true);
    }

    if(is_array($arr)) {
        foreach($arr as $item) {
            if(strpos($item, 'data:') === 0) {
                $imagenes_adicionales_arr[] = $item;
            } else {
                $imagenes_adicionales_arr[] = 'img/productos/' . $item;
            }
        }
    } else {
        $files = array_filter(array_map('trim', explode(',', $raw)));
        foreach($files as $f) {
            if($f !== '') $imagenes_adicionales_arr[] = 'img/productos/' . $f;
        }
    }
}

$pageTitle = htmlspecialchars($producto['nombre']) . ' - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="productos.php">Productos</a></li>
            <li class="breadcrumb-item"><a href="productos.php?categoria=<?= $producto['id_categoria'] ?>">
                <?= htmlspecialchars($producto['categoria']) ?>
            </a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($producto['nombre']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-3" id="imagenPrincipal">
                        <?php if(!empty($principalSrc)): ?>
                            <img src="<?= $principalSrc ?>" 
                                 class="img-fluid" alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                 style="max-height: 400px; object-fit: contain;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 400px;">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($principalSrc) || !empty($imagenes_adicionales_arr)): ?>
                        <div class="d-flex justify-content-center gap-2">
                            <?php if(!empty($principalSrc)): ?>
                                <div class="miniatura-img active" onclick="cambiarImagen('<?= $principalSrc ?>')">
                                    <img src="<?= $principalSrc ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;">
                                </div>
                            <?php endif; ?>

                            <?php foreach($imagenes_adicionales_arr as $src): ?>
                                <div class="miniatura-img" onclick="cambiarImagen('<?= $src ?>')">
                                    <img src="<?= $src ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="h3 mb-3"><?= htmlspecialchars($producto['nombre']) ?></h1>
            
            <?php if($estadisticas_resenas['total'] > 0): ?>
                <div class="mb-3">
                    <?= generarEstrellas($estadisticas_resenas['promedio']) ?>
                    <span class="text-warning fw-bold ms-2"><?= number_format($estadisticas_resenas['promedio'], 1) ?></span>
                    <span class="text-muted ms-2">(<?= $estadisticas_resenas['total'] ?> valoraciones)</span>
                </div>
            <?php endif; ?>
            
            <hr>
            
            <div class="mb-4">
                <?php if($producto['precio_descuento']): ?>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <span class="badge bg-danger">
                            -<?= calcularDescuento($producto['precio'], $producto['precio_descuento']) ?>%
                        </span>
                        <span class="text-danger display-5 fw-bold">
                            <?= formatearPrecio($producto['precio_descuento']) ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-muted">Precio anterior: </span>
                        <span class="text-muted text-decoration-line-through">
                            <?= formatearPrecio($producto['precio']) ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="display-5 fw-bold"><?= formatearPrecio($producto['precio']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <table class="table table-sm">
                    <?php if($producto['marca']): ?>
                    <tr>
                        <td class="text-muted" style="width: 150px;">Marca:</td>
                        <td class="fw-bold"><?= htmlspecialchars($producto['marca']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Categoría:</td>
                        <td>
                            <a href="productos.php?categoria=<?= $producto['id_categoria'] ?>">
                                <?= htmlspecialchars($producto['categoria']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Vendedor:</td>
                        <td>
                            <i class="bi bi-shop text-warning"></i> 
                            <?= htmlspecialchars($producto['nombre_tienda']) ?>
                            <?php if($producto['calificacion_promedio'] > 0): ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="bi bi-star-fill"></i> 
                                    <?= number_format($producto['calificacion_promedio'], 1) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Disponibilidad:</td>
                        <td>
                            <?php if($producto['stock'] > 0): ?>
                                <span class="text-success">
                                    <i class="bi bi-check-circle-fill"></i> En stock (<?= $producto['stock'] ?> disponibles)
                                </span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-x-circle-fill"></i> Agotado
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if($producto['sku']): ?>
                    <tr>
                        <td class="text-muted">SKU:</td>
                        <td><?= htmlspecialchars($producto['sku']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if($producto['stock'] > 0): ?>
                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label">Cantidad:</label>
                            <select class="form-select" id="cantidad">
                                <?php for($i = 1; $i <= min(10, $producto['stock']); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-9 mt-3 mt-md-0">
                            <?php if(estaLogueado()): ?>
                                <button class="btn btn-warning btn-lg w-100" id="btnAgregarCarrito">
                                    <i class="bi bi-cart-plus"></i> Agregar al carrito
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-warning btn-lg w-100">
                                    <i class="bi bi-box-arrow-in-right"></i> Inicia sesión para comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-3">
                    <i class="bi bi-truck"></i> 
                    <strong>Envío gratis</strong> en pedidos superiores a <?= formatearPrecio(ENVIO_GRATIS_DESDE) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Este producto está agotado actualmente
                </div>
            <?php endif; ?>

            <div class="row text-center mt-4">
                <div class="col-4">
                    <i class="bi bi-shield-check fs-3 text-success"></i>
                    <p class="small mb-0 mt-2">Compra protegida</p>
                </div>
                <div class="col-4">
                    <i class="bi bi-arrow-repeat fs-3 text-primary"></i>
                    <p class="small mb-0 mt-2">Devolución gratis</p>
                </div>
                <div class="col-4">
                    <i class="bi bi-award fs-3 text-warning"></i>
                    <p class="small mb-0 mt-2">Garantía del vendedor</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#descripcion">
                        Descripción
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#especificaciones">
                        Especificaciones
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#resenas">
                        Reseñas (<?= count($resenas) ?>)
                    </button>
                </li>
            </ul>
            
            <div class="tab-content p-4 border border-top-0">
                <div class="tab-pane fade show active" id="descripcion">
                    <?php if($producto['descripcion']): ?>
                        <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted">No hay descripción disponible para este producto.</p>
                    <?php endif; ?>
                </div>
                
                <div class="tab-pane fade" id="especificaciones">
                    <table class="table">
                        <?php if($producto['marca']): ?>
                        <tr>
                            <td class="text-muted" style="width: 200px;">Marca</td>
                            <td><?= htmlspecialchars($producto['marca']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($producto['peso']): ?>
                        <tr>
                            <td class="text-muted">Peso</td>
                            <td><?= htmlspecialchars($producto['peso']) ?> kg</td>
                        </tr>
                        <?php endif; ?>
                        <?php if($producto['dimensiones']): ?>
                        <tr>
                            <td class="text-muted">Dimensiones</td>
                            <td><?= htmlspecialchars($producto['dimensiones']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($producto['sku']): ?>
                        <tr>
                            <td class="text-muted">SKU</td>
                            <td><?= htmlspecialchars($producto['sku']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="tab-pane fade" id="resenas">
                    <?php if(!empty($resenas)): ?>
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="display-4 fw-bold text-warning">
                                    <?= number_format($estadisticas_resenas['promedio'], 1) ?>
                                </div>
                                <div class="mb-2">
                                    <?= generarEstrellas($estadisticas_resenas['promedio']) ?>
                                </div>
                                <p class="text-muted"><?= $estadisticas_resenas['total'] ?> valoraciones</p>
                            </div>
                            <div class="col-md-8">
                                <?php foreach([5,4,3,2,1] as $estrellas): ?>
                                    <?php 
                                    $cant = 0;
                                    $porc = 0;
                                    foreach($distribucion as $dist) {
                                        if($dist['calificacion'] == $estrellas) {
                                            $cant = $dist['cantidad'];
                                            $porc = $dist['porcentaje'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <span style="width: 80px;"><?= $estrellas ?> estrellas</span>
                                        <div class="progress flex-grow-1 mx-3" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: <?= $porc ?>%"></div>
                                        </div>
                                        <span class="text-muted" style="width: 60px;"><?= round($porc) ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <?php foreach($resenas as $resena): ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($resena['nombre'] . ' ' . $resena['apellido']) ?></strong>
                                        <div class="small text-muted"><?= tiempoTranscurrido($resena['fecha_resena']) ?></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <?= generarEstrellas($resena['calificacion']) ?>
                                    <?php if($resena['titulo']): ?>
                                        <strong class="ms-2"><?= htmlspecialchars($resena['titulo']) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <?php if($resena['comentario']): ?>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($resena['comentario'])) ?></p>
                                <?php endif; ?>
                                <?php if($resena['verificado']): ?>
                                    <span class="badge bg-success mt-2">
                                        <i class="bi bi-check-circle"></i> Compra verificada
                                    </span>
                                <?php endif; ?>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-left-text fs-1 text-muted mb-3 d-block"></i>
                            <h5>Aún no hay reseñas</h5>
                            <p class="text-muted">Sé el primero en valorar este producto</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($productos_relacionados)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Productos relacionados</h3>
            <div class="row g-4">
                <?php foreach($productos_relacionados as $prod): ?>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift">
                            <a href="producto_detalle.php?id=<?= $prod['id_producto'] ?>" class="text-decoration-none">
                                <?php
                                    $rp = null;
                                    if(!empty($prod['imagen_principal'])) {
                                        if(strpos($prod['imagen_principal'], 'data:') === 0) {
                                            $rp = $prod['imagen_principal'];
                                        } else {
                                            $rp = 'img/productos/' . $prod['imagen_principal'];
                                        }
                                    }
                                ?>
                                <?php if(!empty($rp)): ?>
                                    <img src="<?= $rp ?>" 
                                         class="card-img-top p-3" alt="<?= htmlspecialchars($prod['nombre']) ?>"
                                         style="height: 200px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-image fs-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="card-body">
                                <a href="producto_detalle.php?id=<?= $prod['id_producto'] ?>" 
                                   class="text-decoration-none text-dark">
                                    <h6 class="card-title" style="height: 40px; overflow: hidden;">
                                        <?= htmlspecialchars($prod['nombre']) ?>
                                    </h6>
                                </a>
                                <div class="fw-bold">
                                    <?= formatearPrecio($prod['precio_descuento'] ?? $prod['precio']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}

.miniatura-img {
    border: 2px solid transparent;
    transition: border-color 0.3s;
}

.miniatura-img.active {
    border-color: #febd69;
}

.miniatura-img:hover {
    border-color: #f3a847;
}
</style>

<script>
function cambiarImagen(src) {
    document.querySelector('#imagenPrincipal img').src = src;
    
    document.querySelectorAll('.miniatura-img').forEach(min => min.classList.remove('active'));
    event.target.closest('.miniatura-img').classList.add('active');
}

<?php if(estaLogueado() && $producto['stock'] > 0): ?>
document.getElementById('btnAgregarCarrito').addEventListener('click', function() {
    const cantidad = document.getElementById('cantidad').value;
    const productoId = <?= $producto['id_producto'] ?>;
    
    fetch('api/carrito_add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            producto_id: productoId, 
            cantidad: parseInt(cantidad) 
        })
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
});
<?php endif; ?>
</script>

<?php include_once "views/footer.php"; ?>