<?php
require_once "config/config.php";
require_once "models/usuario.php";
require_once "models/pedido.php";
require_once "models/resena.php";

if(!estaLogueado()) {
    redirect('login.php');
}

$appUsuario = new Usuario();
$appPedido = new Pedido();
$appResena = new Resena();

$usuario_id = $_SESSION['usuario_id'];
$usuario = $appUsuario->readOne($usuario_id);

$pedidos = $appPedido->readByUsuario($usuario_id);

$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
if($filtro_estado != 'todos') {
    $pedidos = array_filter($pedidos, function($pedido) use ($filtro_estado) {
        return $pedido['estado'] == $filtro_estado;
    });
}

$pedido_detalle = null;
$detalles = [];
if(isset($_GET['detalle'])) {
    $id_pedido = (int)$_GET['detalle'];
    $pedido_detalle = $appPedido->readOne($id_pedido);
    
    if($pedido_detalle && $pedido_detalle['id_usuario'] == $usuario_id) {
        $detalles = $appPedido->readDetalle($id_pedido);
    } else {
        $pedido_detalle = null;
    }
}

$total_pedidos = count($appPedido->readByUsuario($usuario_id));
$total_gastado = 0;
foreach($appPedido->readByUsuario($usuario_id) as $p) {
    if($p['estado'] != 'cancelado') {
        $total_gastado += $p['total'];
    }
}

$pageTitle = 'Mis Pedidos - Amazon Lite';
include_once "views/header.php";
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-warning text-white mb-3">
                            <span class="fs-2 fw-bold">
                                <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                            </span>
                        </div>
                        <h5 class="mb-1"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h5>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="perfil.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-fill me-2"></i> Mi perfil
                        </a>
                        <a href="pedidos.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-box-seam me-2"></i> Mis pedidos
                            <?php if($total_pedidos > 0): ?>
                                <span class="badge bg-warning text-dark float-end"><?= $total_pedidos ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="carrito.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-cart3 me-2"></i> Mi carrito
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart me-2"></i> Lista de deseos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-geo-alt me-2"></i> Direcciones
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">Estadísticas</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Pedidos realizados</small>
                            <strong><?= $total_pedidos ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Total gastado</small>
                            <strong class="text-success"><?= formatearPrecio($total_gastado) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Miembro desde</small>
                            <strong><?= date('Y', strtotime($usuario['fecha_registro'])) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if($pedido_detalle): ?>
                <div class="mb-3">
                    <a href="pedidos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a mis pedidos
                    </a>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-0">
                                    Pedido #<?= htmlspecialchars($pedido_detalle['numero_pedido']) ?>
                                </h4>
                                <p class="text-muted small mb-0">
                                    Realizado el <?= date('d/m/Y H:i', strtotime($pedido_detalle['fecha_pedido'])) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <?= obtenerBadgeEstado($pedido_detalle['estado']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Dirección de envío</h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($pedido_detalle['direccion_envio'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Método de pago</h6>
                                <p class="mb-2"><?= htmlspecialchars($pedido_detalle['metodo_pago']) ?></p>
                                
                                <h6 class="fw-bold mb-2 mt-3">Estado del pedido</h6>
                                <div class="progress" style="height: 25px;">
                                    <?php
                                    $estados_progreso = [
                                        'pendiente' => 20,
                                        'procesando' => 40,
                                        'enviado' => 70,
                                        'entregado' => 100,
                                        'cancelado' => 100
                                    ];
                                    $progreso = $estados_progreso[$pedido_detalle['estado']] ?? 0;
                                    $color = $pedido_detalle['estado'] == 'cancelado' ? 'danger' : 'success';
                                    ?>
                                    <div class="progress-bar bg-<?= $color ?>" role="progressbar" 
                                         style="width: <?= $progreso ?>%" aria-valuenow="<?= $progreso ?>">
                                        <?= $progreso ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="mb-3">Productos</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio unitario</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($detalles as $detalle): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php
                                                        $rp = null;
                                                        if(!empty($detalle['imagen_principal'])) {
                                                            $val = $detalle['imagen_principal'];
                                                            if(strpos($val, 'data:') === 0) {
                                                                $rp = $val;
                                                            } else {
                                                                $rp = 'img/productos/' . $val;
                                                            }
                                                        }
                                                    ?>
                                                    <?php if(!empty($rp)): ?>
                                                        <img src="<?= $rp ?>" class="me-3" style="width: 60px; height: 60px; object-fit: contain;"
                                                             alt="<?= htmlspecialchars($detalle['producto']) ?>">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                                             style="width:60px; height:60px;">
                                                            <i class="bi bi-image fs-4 text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <a href="producto_detalle.php?id=<?= $detalle['id_producto'] ?>" 
                                                           class="text-decoration-none text-dark fw-bold">
                                                            <?= htmlspecialchars($detalle['producto']) ?>
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="bi bi-shop"></i> <?= htmlspecialchars($detalle['nombre_tienda']) ?>
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <?= obtenerBadgeEstado($detalle['estado_vendedor']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle"><?= $detalle['cantidad'] ?></td>
                                            <td class="text-end align-middle"><?= formatearPrecio($detalle['precio_unitario']) ?></td>
                                            <td class="text-end align-middle fw-bold"><?= formatearPrecio($detalle['subtotal']) ?></td>
                                            <td class="text-center align-middle">
                                                <?php if($pedido_detalle['estado'] == 'entregado'): ?>
                                                    <?php
                                                    // Verificar si puede reseñar
                                                    $puede_resenar = $appResena->usuarioPuedeResenar($usuario_id, $detalle['id_producto']);
                                                    ?>
                                                    <?php if($puede_resenar): ?>
                                                        <a href="producto_detalle.php?id=<?= $detalle['id_producto'] ?>#resenas" 
                                                           class="btn btn-sm btn-outline-warning">
                                                            <i class="bi bi-star"></i> Reseñar
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> Reseñado
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a href="producto_detalle.php?id=<?= $detalle['id_producto'] ?>" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        Ver producto
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><?= formatearPrecio($pedido_detalle['subtotal']) ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Envío:</strong></td>
                                        <td class="text-end">
                                            <?= $pedido_detalle['envio'] == 0 ? 'GRATIS' : formatearPrecio($pedido_detalle['envio']) ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>IVA:</strong></td>
                                        <td class="text-end"><?= formatearPrecio($pedido_detalle['impuestos']) ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong class="fs-5">Total:</strong></td>
                                        <td class="text-end"><strong class="fs-5 text-success"><?= formatearPrecio($pedido_detalle['total']) ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="mb-0"><i class="bi bi-box-seam text-warning"></i> Mis pedidos</h4>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="filtroEstado" onchange="filtrarPedidos(this.value)">
                                    <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos los pedidos</option>
                                    <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                    <option value="procesando" <?= $filtro_estado == 'procesando' ? 'selected' : '' ?>>En proceso</option>
                                    <option value="enviado" <?= $filtro_estado == 'enviado' ? 'selected' : '' ?>>Enviados</option>
                                    <option value="entregado" <?= $filtro_estado == 'entregado' ? 'selected' : '' ?>>Entregados</option>
                                    <option value="cancelado" <?= $filtro_estado == 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($pedidos)): ?>
                            <?php foreach($pedidos as $pedido): ?>
                                <div class="card mb-3 border">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="mb-2">
                                                    Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?>
                                                    <?= obtenerBadgeEstado($pedido['estado']) ?>
                                                </h5>
                                                <p class="text-muted small mb-2">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?>
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Total:</strong> 
                                                    <span class="text-success fs-5"><?= formatearPrecio($pedido['total']) ?></span>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <a href="pedidos.php?detalle=<?= $pedido['id_pedido'] ?>" 
                                                   class="btn btn-outline-warning mb-2 w-100">
                                                    <i class="bi bi-eye"></i> Ver detalle
                                                </a>
                                                <?php if($pedido['estado'] == 'pendiente'): ?>
                                                    <button class="btn btn-outline-danger w-100" 
                                                            onclick="cancelarPedido(<?= $pedido['id_pedido'] ?>)">
                                                        <i class="bi bi-x-circle"></i> Cancelar
                                                    </button>
                                                <?php elseif($pedido['estado'] == 'entregado'): ?>
                                                    <a href="pedidos.php?detalle=<?= $pedido['id_pedido'] ?>" 
                                                       class="btn btn-outline-success w-100">
                                                        <i class="bi bi-star"></i> Dejar reseñas
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                <h4>No tienes pedidos<?= $filtro_estado != 'todos' ? ' con este estado' : '' ?></h4>
                                <p class="text-muted mb-4">Comienza a comprar para ver tus pedidos aquí</p>
                                <?php if($filtro_estado != 'todos'): ?>
                                    <a href="pedidos.php" class="btn btn-outline-secondary me-2">
                                        Ver todos los pedidos
                                    </a>
                                <?php endif; ?>
                                <a href="productos.php" class="btn btn-warning">
                                    Explorar productos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.list-group-item.active {
    background-color: #febd69;
    border-color: #febd69;
    color: #111;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<script src="js/usuario/pedidos.js"></script>

<?php include_once "views/footer.php"; ?>