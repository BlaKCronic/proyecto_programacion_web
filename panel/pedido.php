<?php
require_once "../config/config.php";
require_once "../models/pedido.php";
require_once "../models/usuario.php";

if(!esAdmin()) redirect('login.php');

$appPedido = new Pedido();
$appUsuario = new Usuario();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$mensaje = '';
$tipo_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['actualizar_estado'])) {
        $id_pedido = (int)$_POST['id_pedido'];
        $estado = $_POST['estado'];
        
        $appPedido->updateEstado($id_pedido, $estado);
        
        if($estado == 'entregado') {
            $appPedido->updateFechaEntrega($id_pedido, date('Y-m-d H:i:s'));
        }
        
        $mensaje = 'Estado del pedido actualizado';
        $tipo_mensaje = 'success';
    }
}

$pedidos = $appPedido->read();

$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
if($filtro_estado != 'todos') {
    $pedidos = array_filter($pedidos, fn($p) => $p['estado'] == $filtro_estado);
}

$pedido_detalle = null;
$detalles = [];
if(isset($_GET['detalle'])) {
    $id_pedido = (int)$_GET['detalle'];
    $pedido_detalle = $appPedido->readOne($id_pedido);
    if($pedido_detalle) {
        $detalles = $appPedido->readDetalle($id_pedido);
    }
}

$pageTitle = 'Gestión de Pedidos';
include_once "views/header.php";
?>

<?php if($pedido_detalle): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Pedido #<?= htmlspecialchars($pedido_detalle['numero_pedido']) ?>
        </h1>
        <a href="pedido.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Estado:</strong><br>
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
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Cliente:</strong><br>
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
                        </div>
                    </div>
                    
                    <hr>
                    
                    <p class="mb-2"><strong>Dirección de envío:</strong></p>
                    <p><?= nl2br(htmlspecialchars($pedido_detalle['direccion_envio'])) ?></p>
                    
                    <?php if($pedido_detalle['fecha_entrega']): ?>
                        <p class="mb-0">
                            <strong>Fecha de entrega:</strong>
                            <?= date('d/m/Y H:i', strtotime($pedido_detalle['fecha_entrega'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Vendedor</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
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
                                                            $rp = '../img/productos/' . $val;
                                                        }
                                                    }
                                                ?>
                                                <?php if(!empty($rp)): ?>
                                                    <img src="<?= $rp ?>" class="me-2" style="width: 50px; height: 50px; object-fit: contain;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                                         style="width:50px; height:50px;">
                                                        <i class="bi bi-image fs-4 text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <?= htmlspecialchars($detalle['producto']) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($detalle['nombre_tienda']) ?></td>
                                        <td class="text-center"><?= $detalle['cantidad'] ?></td>
                                        <td class="text-end"><?= formatearPrecio($detalle['precio_unitario']) ?></td>
                                        <td class="text-end fw-bold"><?= formatearPrecio($detalle['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><?= formatearPrecio($pedido_detalle['subtotal']) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Envío:</strong></td>
                                    <td class="text-end">
                                        <?= $pedido_detalle['envio'] == 0 ? 'GRATIS' : formatearPrecio($pedido_detalle['envio']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>IVA:</strong></td>
                                    <td class="text-end"><?= formatearPrecio($pedido_detalle['impuestos']) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong class="fs-5">Total:</strong></td>
                                    <td class="text-end">
                                        <strong class="fs-5 text-success">
                                            <?= formatearPrecio($pedido_detalle['total']) ?>
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Actualizar Estado</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="pedido.php?detalle=<?= $pedido_detalle['id_pedido'] ?>">
                        <input type="hidden" name="id_pedido" value="<?= $pedido_detalle['id_pedido'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Estado del pedido:</label>
                            <select class="form-select" name="estado">
                                <option value="pendiente" <?= $pedido_detalle['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                    Pendiente
                                </option>
                                <option value="procesando" <?= $pedido_detalle['estado'] == 'procesando' ? 'selected' : '' ?>>
                                    Procesando
                                </option>
                                <option value="enviado" <?= $pedido_detalle['estado'] == 'enviado' ? 'selected' : '' ?>>
                                    Enviado
                                </option>
                                <option value="entregado" <?= $pedido_detalle['estado'] == 'entregado' ? 'selected' : '' ?>>
                                    Entregado
                                </option>
                                <option value="cancelado" <?= $pedido_detalle['estado'] == 'cancelado' ? 'selected' : '' ?>>
                                    Cancelado
                                </option>
                            </select>
                        </div>
                        
                        <button type="submit" name="actualizar_estado" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Actualizar Estado
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Subtotal productos:</strong><br>
                        <?= formatearPrecio($pedido_detalle['subtotal']) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Comisión plataforma (15%):</strong><br>
                        <span class="text-success">
                            <?= formatearPrecio($pedido_detalle['subtotal'] * 0.15) ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Pago a vendedores:</strong><br>
                        <?= formatearPrecio($pedido_detalle['subtotal'] * 0.85) ?>
                    </p>
                    <hr>
                    <p class="mb-0">
                        <strong>Total cobrado al cliente:</strong><br>
                        <span class="fs-5 text-primary">
                            <?= formatearPrecio($pedido_detalle['total']) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Pedidos</h1>
    </div>

    <?php if($mensaje): echo mostrarAlerta($mensaje, $tipo_mensaje); endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-left-primary">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($appPedido->read()) ?></h3>
                    <p class="text-muted mb-0">Total Pedidos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-warning">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?= count(array_filter($appPedido->read(), fn($p) => $p['estado'] == 'pendiente')) ?>
                    </h3>
                    <p class="text-muted mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-info">
                <div class="card-body">
                    <h3 class="text-info">
                        <?= count(array_filter($appPedido->read(), fn($p) => $p['estado'] == 'procesando')) ?>
                    </h3>
                    <p class="text-muted mb-0">Procesando</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-success">
                <div class="card-body">
                    <h3 class="text-success">
                        <?= count(array_filter($appPedido->read(), fn($p) => $p['estado'] == 'entregado')) ?>
                    </h3>
                    <p class="text-muted mb-0">Entregados</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Filtrar por estado:</label>
                    <select class="form-select" onchange="window.location.href='?estado='+this.value">
                        <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>
                            Todos los pedidos
                        </option>
                        <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>
                            Pendientes
                        </option>
                        <option value="procesando" <?= $filtro_estado == 'procesando' ? 'selected' : '' ?>>
                            Procesando
                        </option>
                        <option value="enviado" <?= $filtro_estado == 'enviado' ? 'selected' : '' ?>>
                            Enviados
                        </option>
                        <option value="entregado" <?= $filtro_estado == 'entregado' ? 'selected' : '' ?>>
                            Entregados
                        </option>
                        <option value="cancelado" <?= $filtro_estado == 'cancelado' ? 'selected' : '' ?>>
                            Cancelados
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Pedidos (<?= count($pedidos) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Método de pago</th>
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
                                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                                    <td><?= formatearPrecio($pedido['total']) ?></td>
                                    <td><?= htmlspecialchars($pedido['metodo_pago']) ?></td>
                                    <td><?= obtenerBadgeEstado($pedido['estado']) ?></td>
                                    <td>
                                        <a href="?detalle=<?= $pedido['id_pedido'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No hay pedidos con el filtro seleccionado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include_once "views/footer.php"; ?>