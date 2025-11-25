<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";
require_once "../models/pedido.php";

if(!esVendedor()) {
    redirect('login.php');
}

$appVendedor = new Vendedor();
$appPedido = new Pedido();

$vendedor_id = $_SESSION['vendedor_id'];
$vendedor = $appVendedor->readOne($vendedor_id);

$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_estado'])) {
    $id_detalle = (int)$_POST['id_detalle'];
    $estado = $_POST['estado'];
    $numero_seguimiento = $_POST['numero_seguimiento'] ?? null;
    
    $filas = $appPedido->updateEstadoVendedor($id_detalle, $estado, $numero_seguimiento);
    if($filas > 0) {
        $mensaje = 'Estado actualizado exitosamente';
        $tipo_mensaje = 'success';
    }
}

$pedidos = $appPedido->readByVendedor($vendedor_id);

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
    
    if($pedido_detalle) {
        $detalles = $appPedido->readDetalleByVendedor($id_pedido, $vendedor_id);
    }
}

$pageTitle = 'Gestión de Pedidos - ' . $vendedor['nombre_tienda'];
include_once "views/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "views/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if($pedido_detalle && !empty($detalles)): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-receipt text-warning"></i> 
                        Pedido #<?= htmlspecialchars($pedido_detalle['numero_pedido']) ?>
                    </h1>
                    <a href="pedidos.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if($mensaje): ?>
                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Productos de tu tienda en este pedido</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach($detalles as $detalle): ?>
                                    <div class="card mb-3 border">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <?php
                                                        $rp = null;
                                                        if(!empty($detalle['imagen_principal'])) {
                                                            $val = $detalle['imagen_principal'];
                                                            if(strpos($val, 'data:') === 0) {
                                                                $rp = $val;
                                                            } else {
                                                                $rp = '../img/productos/' . $val;
                                                            }
                                                        }
                                                    ?>
                                                    <?php if(!empty($rp)): ?>
                                                        <img src="<?= $rp ?>" class="img-fluid" alt="<?= htmlspecialchars($detalle['producto']) ?>">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:100%;">
                                                            <i class="bi bi-image fs-3 text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold"><?= htmlspecialchars($detalle['producto']) ?></h6>
                                                    <p class="text-muted mb-2">
                                                        Cantidad: <strong><?= $detalle['cantidad'] ?></strong><br>
                                                        Precio unitario: <strong><?= formatearPrecio($detalle['precio_unitario']) ?></strong><br>
                                                        Subtotal: <strong class="text-success"><?= formatearPrecio($detalle['subtotal']) ?></strong>
                                                    </p>
                                                    <p class="mb-0">
                                                        <small class="text-muted">
                                                            Comisión plataforma: <?= formatearPrecio($detalle['comision_plataforma']) ?><br>
                                                            Tu ganancia: <strong class="text-success">
                                                                <?= formatearPrecio($detalle['subtotal'] - $detalle['comision_plataforma']) ?>
                                                            </strong>
                                                        </small>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <form method="POST" action="pedidos.php?detalle=<?= $id_pedido ?>">
                                                        <input type="hidden" name="id_detalle" value="<?= $detalle['id_detalle'] ?>">
                                                        
                                                        <div class="mb-2">
                                                            <label class="form-label small">Estado:</label>
                                                            <select class="form-select form-select-sm" name="estado">
                                                                <option value="pendiente" <?= $detalle['estado_vendedor'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                                <option value="confirmado" <?= $detalle['estado_vendedor'] == 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                                                <option value="preparando" <?= $detalle['estado_vendedor'] == 'preparando' ? 'selected' : '' ?>>Preparando</option>
                                                                <option value="enviado" <?= $detalle['estado_vendedor'] == 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                                                <option value="entregado" <?= $detalle['estado_vendedor'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-2">
                                                            <label class="form-label small">No. Seguimiento:</label>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                   name="numero_seguimiento" 
                                                                   value="<?= htmlspecialchars($detalle['numero_seguimiento'] ?? '') ?>"
                                                                   placeholder="Opcional">
                                                        </div>
                                                        
                                                        <button type="submit" name="actualizar_estado" 
                                                                class="btn btn-warning btn-sm w-100">
                                                            <i class="bi bi-check-circle"></i> Actualizar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Información del pedido</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <strong>Estado general:</strong><br>
                                    <?= obtenerBadgeEstado($pedido_detalle['estado']) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Fecha del pedido:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($pedido_detalle['fecha_pedido'])) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Método de pago:</strong><br>
                                    <?= htmlspecialchars($pedido_detalle['metodo_pago']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="card shadow">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Información del cliente</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <strong>Nombre:</strong><br>
                                    <?= htmlspecialchars($pedido_detalle['nombre'] . ' ' . $pedido_detalle['apellido']) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Email:</strong><br>
                                    <a href="mailto:<?= htmlspecialchars($pedido_detalle['email']) ?>">
                                        <?= htmlspecialchars($pedido_detalle['email']) ?>
                                    </a>
                                </p>
                                <p class="mb-2">
                                    <strong>Teléfono:</strong><br>
                                    <?= htmlspecialchars($pedido_detalle['telefono']) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Dirección de envío:</strong><br>
                                    <?= nl2br(htmlspecialchars($pedido_detalle['direccion_envio'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-cart-check text-warning"></i> Gestión de Pedidos
                    </h1>
                </div>

                <?php if($mensaje): ?>
                    <?= mostrarAlerta($mensaje, $tipo_mensaje) ?>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Filtrar por estado:</label>
                                <select class="form-select" onchange="window.location.href='?estado='+this.value">
                                    <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos los pedidos</option>
                                    <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                    <option value="procesando" <?= $filtro_estado == 'procesando' ? 'selected' : '' ?>>En proceso</option>
                                    <option value="enviado" <?= $filtro_estado == 'enviado' ? 'selected' : '' ?>>Enviados</option>
                                    <option value="entregado" <?= $filtro_estado == 'entregado' ? 'selected' : '' ?>>Entregados</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($pedidos)): ?>
                                        <?php foreach($pedidos as $pedido): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($pedido['numero_pedido']) ?></td>
                                                <td><?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                                <td><?= formatearPrecio($pedido['total']) ?></td>
                                                <td><?= obtenerBadgeEstado($pedido['estado']) ?></td>
                                                <td>
                                                    <a href="?detalle=<?= $pedido['id_pedido'] ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Ver detalle
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                                <p class="text-muted">No hay pedidos<?= $filtro_estado != 'todos' ? ' con este estado' : '' ?></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once "views/footer.php"; ?>