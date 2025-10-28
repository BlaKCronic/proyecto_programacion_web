<?php
require_once "config/config.php";
require_once "models/carrito.php";
require_once "models/pedido.php";
require_once "models/usuario.php";
require_once "models/producto.php";

if(!estaLogueado()) {
    redirect('login.php');
}

$appCarrito = new Carrito();
$appPedido = new Pedido();
$appUsuario = new Usuario();
$appProducto = new Producto();

$usuario_id = $_SESSION['usuario_id'];
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

$items_carrito = $appCarrito->obtenerCarrito($usuario_id);

if(empty($items_carrito)) {
    redirect('carrito.php');
}

$subtotal = 0;
foreach($items_carrito as $item) {
    $precio = $item['precio_descuento'] ?? $item['precio'];
    $subtotal += $precio * $item['cantidad'];
}

$envio = $subtotal >= ENVIO_GRATIS_DESDE ? 0 : COSTO_ENVIO_BASE;
$impuestos = $subtotal * IVA;
$total = $subtotal + $envio + $impuestos;

$usuario = $appUsuario->readOne($usuario_id);

$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['confirmar_direccion'])) {
        $_SESSION['checkout_direccion'] = [
            'direccion' => $_POST['direccion'],
            'ciudad' => $_POST['ciudad'],
            'estado' => $_POST['estado'],
            'codigo_postal' => $_POST['codigo_postal'],
            'telefono' => $_POST['telefono']
        ];
        redirect('checkout.php?step=2');
    } elseif(isset($_POST['confirmar_pago'])) {
        $_SESSION['checkout_pago'] = [
            'metodo' => $_POST['metodo_pago']
        ];
        redirect('checkout.php?step=3');
    } elseif(isset($_POST['finalizar_pedido'])) {
        $direccion_completa = $_SESSION['checkout_direccion']['direccion'] . ', ' .
                            $_SESSION['checkout_direccion']['ciudad'] . ', ' .
                            $_SESSION['checkout_direccion']['estado'] . ' ' .
                            $_SESSION['checkout_direccion']['codigo_postal'];
        
        $data_pedido = [
            'id_usuario' => $usuario_id,
            'total' => $total,
            'subtotal' => $subtotal,
            'envio' => $envio,
            'impuestos' => $impuestos,
            'direccion_envio' => $direccion_completa,
            'metodo_pago' => $_SESSION['checkout_pago']['metodo']
        ];
        
        $id_pedido = $appPedido->create($data_pedido);
        
        if($id_pedido) {
            foreach($items_carrito as $item) {
                $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
                $subtotal_item = $precio_unitario * $item['cantidad'];
                
                $data_detalle = [
                    'id_pedido' => $id_pedido,
                    'id_producto' => $item['id_producto'],
                    'id_vendedor' => $item['id_vendedor'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precio_unitario,
                    'subtotal' => $subtotal_item
                ];
                
                $appPedido->agregarDetalle($data_detalle);
                
                $appProducto->updateStock($item['id_producto'], $item['cantidad']);
            }
            
            $appCarrito->vaciarCarrito($usuario_id);
            $_SESSION['cart_count'] = 0;
            
            unset($_SESSION['checkout_direccion']);
            unset($_SESSION['checkout_pago']);
            
            $_SESSION['ultimo_pedido'] = $id_pedido;
            redirect('checkout.php?step=4');
        } else {
            $mensaje = 'Error al procesar el pedido. Intenta nuevamente.';
            $tipo_mensaje = 'danger';
        }
    }
}

$pageTitle = 'Checkout - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-fill text-center">
                    <div class="checkout-step <?= $step >= 1 ? 'active' : '' ?>">
                        <div class="step-circle">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="step-label">Dirección</div>
                    </div>
                </div>
                <div class="step-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                <div class="flex-fill text-center">
                    <div class="checkout-step <?= $step >= 2 ? 'active' : '' ?>">
                        <div class="step-circle">
                            <i class="bi bi-credit-card-fill"></i>
                        </div>
                        <div class="step-label">Pago</div>
                    </div>
                </div>
                <div class="step-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                <div class="flex-fill text-center">
                    <div class="checkout-step <?= $step >= 3 ? 'active' : '' ?>">
                        <div class="step-circle">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="step-label">Confirmar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($mensaje): ?>
        <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <?php
            switch($step) {
                case 1:
                    include_once "views/checkout/direccion.php";
                    break;
                case 2:
                    include_once "views/checkout/pago.php";
                    break;
                case 3:
                    include_once "views/checkout/confirmacion.php";
                    break;
                case 4:
                    include_once "views/checkout/exito.php";
                    break;
                default:
                    redirect('checkout.php?step=1');
            }
            ?>
        </div>
        
        <?php if($step < 4): ?>
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Resumen del pedido</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach($items_carrito as $item): ?>
                            <?php 
                            $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
                            $subtotal_item = $precio_unitario * $item['cantidad'];
                            ?>
                            <div class="d-flex mb-3">
                                <img src="img/productos/<?= $item['imagen_principal'] ?>" 
                                     class="me-2" style="width: 60px; height: 60px; object-fit: contain;"
                                     alt="<?= htmlspecialchars($item['nombre']) ?>">
                                <div class="flex-fill">
                                    <div class="small mb-1">
                                        <?= htmlspecialchars(substr($item['nombre'], 0, 40)) ?>
                                        <?= strlen($item['nombre']) > 40 ? '...' : '' ?>
                                    </div>
                                    <div class="small text-muted">
                                        Cant: <?= $item['cantidad'] ?> × <?= formatearPrecio($precio_unitario) ?>
                                    </div>
                                    <div class="small fw-bold">
                                        <?= formatearPrecio($subtotal_item) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?= formatearPrecio($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span class="<?= $envio == 0 ? 'text-success fw-bold' : '' ?>">
                            <?= $envio == 0 ? 'GRATIS' : formatearPrecio($envio) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>IVA:</span>
                        <span><?= formatearPrecio($impuestos) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong class="fs-5">Total:</strong>
                        <strong class="fs-5 text-danger"><?= formatearPrecio($total) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.checkout-step {
    position: relative;
}

.step-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto;
    transition: all 0.3s;
}

.checkout-step.active .step-circle {
    background-color: #febd69;
    color: #111;
}

.step-label {
    margin-top: 10px;
    font-size: 14px;
    color: #6c757d;
}

.checkout-step.active .step-label {
    color: #111;
    font-weight: bold;
}

.step-line {
    height: 2px;
    background-color: #e9ecef;
    flex: 1;
    margin: 0 10px;
    position: relative;
    top: -20px;
}

.step-line.active {
    background-color: #febd69;
}

.sticky-top {
    position: sticky;
}

@media (max-width: 991px) {
    .sticky-top {
        position: relative;
    }
}
</style>

<?php include_once "views/footer.php"; ?>