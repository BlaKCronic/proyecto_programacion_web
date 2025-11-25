<?php
require_once "config/config.php";
require_once "models/carrito.php";

if(!estaLogueado()) {
    redirect('login.php');
}

$appCarrito = new Carrito();
$usuario_id = $_SESSION['usuario_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['actualizar'])) {
        foreach($_POST['cantidad'] as $id_carrito => $cantidad) {
            $cantidad = (int)$cantidad;
            if($cantidad > 0) {
                $appCarrito->actualizarCantidad($id_carrito, $cantidad);
            } else {
                $appCarrito->eliminar($id_carrito);
            }
        }
        $_SESSION['cart_count'] = $appCarrito->contarItems($usuario_id);
        redirect('carrito.php');
    } elseif(isset($_POST['eliminar'])) {
        $id_carrito = (int)$_POST['id_carrito'];
        $appCarrito->eliminar($id_carrito);
        $_SESSION['cart_count'] = $appCarrito->contarItems($usuario_id);
        redirect('carrito.php');
    } elseif(isset($_POST['vaciar'])) {
        $appCarrito->vaciarCarrito($usuario_id);
        $_SESSION['cart_count'] = 0;
        redirect('carrito.php');
    }
}

$items_carrito = $appCarrito->obtenerCarrito($usuario_id);
$subtotal = 0;

foreach($items_carrito as $item) {
    $precio = $item['precio_descuento'] ?? $item['precio'];
    $subtotal += $precio * $item['cantidad'];
}

$envio = $subtotal >= ENVIO_GRATIS_DESDE ? 0 : COSTO_ENVIO_BASE;
$impuestos = $subtotal * IVA;
$total = $subtotal + $envio + $impuestos;

$pageTitle = 'Carrito de compras - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-4">
    <h1 class="mb-4">Carrito de compras</h1>
    
    <?php if(!empty($items_carrito)): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="POST" action="carrito.php">
                            <?php foreach($items_carrito as $item): ?>
                                <?php 
                                $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
                                $subtotal_item = $precio_unitario * $item['cantidad'];
                                ?>
                                <div class="row border-bottom py-3 align-items-center">
                                    <div class="col-md-2 col-3">
                                        <a href="producto_detalle.php?id=<?= $item['id_producto'] ?>">
                                                    <?php if($item['imagen_principal']): ?>
                                                        <?php
                                                            $rp = null;
                                                            $val = $item['imagen_principal'];
                                                            if(strpos($val, 'data:') === 0) {
                                                                $rp = $val;
                                                            } else {
                                                                $rp = 'img/productos/' . $val;
                                                            }
                                                        ?>
                                                        <?php if(!empty($rp)): ?>
                                                            <img src="<?= $rp ?>" class="img-fluid" alt="<?= htmlspecialchars($item['nombre']) ?>">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                                 style="height: 100px;">
                                                                <i class="bi bi-image fs-3 text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="height: 100px;">
                                                    <i class="bi bi-image fs-3 text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    
                                    <div class="col-md-4 col-9">
                                        <a href="producto_detalle.php?id=<?= $item['id_producto'] ?>" 
                                           class="text-decoration-none text-dark">
                                            <h6 class="mb-2"><?= htmlspecialchars($item['nombre']) ?></h6>
                                        </a>
                                        <p class="text-muted small mb-1">
                                            <i class="bi bi-shop"></i> <?= htmlspecialchars($item['nombre_tienda']) ?>
                                        </p>
                                        <p class="text-success small mb-0">
                                            <i class="bi bi-check-circle-fill"></i> En stock
                                        </p>
                                        
                                        <button type="submit" name="eliminar" value="1" 
                                                class="btn btn-link btn-sm text-danger p-0 d-md-none"
                                                onclick="return confirm('¿Eliminar este producto?');">
                                            <input type="hidden" name="id_carrito" value="<?= $item['id_carrito'] ?>">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-2 col-6 mt-3 mt-md-0">
                                        <label class="form-label small">Cantidad:</label>
                                        <select name="cantidad[<?= $item['id_carrito'] ?>]" 
                                                class="form-select form-select-sm" 
                                                onchange="this.form.submit()">
                                            <?php for($i = 0; $i <= min(20, $item['stock']); $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == $item['cantidad'] ? 'selected' : '' ?>>
                                                    <?= $i == 0 ? 'Eliminar' : $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2 col-6 text-end mt-3 mt-md-0">
                                        <?php if($item['precio_descuento']): ?>
                                            <div class="fw-bold text-danger">
                                                <?= formatearPrecio($precio_unitario) ?>
                                            </div>
                                            <small class="text-muted text-decoration-line-through">
                                                <?= formatearPrecio($item['precio']) ?>
                                            </small>
                                        <?php else: ?>
                                            <div class="fw-bold">
                                                <?= formatearPrecio($precio_unitario) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-2 col-12 text-end mt-3 mt-md-0">
                                        <div class="fw-bold fs-5">
                                            <?= formatearPrecio($subtotal_item) ?>
                                        </div>
                                        
                                        <!-- Botón eliminar desktop -->
                                        <button type="submit" name="eliminar" value="1" 
                                                class="btn btn-link btn-sm text-danger d-none d-md-inline"
                                                onclick="return confirm('¿Eliminar este producto?');">
                                            <input type="hidden" name="id_carrito" value="<?= $item['id_carrito'] ?>">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" name="vaciar" class="btn btn-outline-danger"
                                        onclick="return confirm('¿Vaciar todo el carrito?');">
                                    <i class="bi bi-trash"></i> Vaciar carrito
                                </button>
                                <button type="submit" name="actualizar" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar carrito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if($subtotal < ENVIO_GRATIS_DESDE): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-truck"></i>
                        Agrega <strong><?= formatearPrecio(ENVIO_GRATIS_DESDE - $subtotal) ?></strong> 
                        más para obtener <strong>envío gratis</strong>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar bg-info" 
                                 style="width: <?= ($subtotal / ENVIO_GRATIS_DESDE) * 100 ?>%"></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>¡Felicidades!</strong> Tu pedido califica para envío gratis
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Resumen del pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?= count($items_carrito) ?> productos):</span>
                            <span><?= formatearPrecio($subtotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío:</span>
                            <span class="<?= $envio == 0 ? 'text-success fw-bold' : '' ?>">
                                <?= $envio == 0 ? 'GRATIS' : formatearPrecio($envio) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>IVA (<?= (IVA * 100) ?>%):</span>
                            <span><?= formatearPrecio($impuestos) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="fs-5">Total:</strong>
                            <strong class="fs-5 text-danger"><?= formatearPrecio($total) ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-warning btn-lg w-100 mb-2">
                            <i class="bi bi-lock-fill"></i> Proceder al pago
                        </a>
                        <a href="productos.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Seguir comprando
                        </a>
                    </div>
                </div>
                
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Aceptamos:</h6>
                        <div class="d-flex justify-content-around">
                            <i class="bi bi-credit-card fs-3 text-muted"></i>
                            <i class="bi bi-wallet2 fs-3 text-muted"></i>
                            <i class="bi bi-cash fs-3 text-muted"></i>
                        </div>
                        <p class="text-center small text-muted mt-2 mb-0">
                            Compra 100% segura
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
                <h3>Tu carrito está vacío</h3>
                <p class="text-muted mb-4">Agrega productos para comenzar tu compra</p>
                <a href="productos.php" class="btn btn-warning btn-lg">
                    <i class="bi bi-shop"></i> Explorar productos
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.sticky-top {
    position: sticky;
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative;
    }
}
</style>

<script>
document.querySelectorAll('select[name^="cantidad"]').forEach(select => {
    select.addEventListener('change', function() {
        if(this.value == '0') {
            if(confirm('¿Eliminar este producto del carrito?')) {
                this.form.submit();
            }
        }
    });
});
</script>

<?php include_once "views/footer.php"; ?>