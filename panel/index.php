<?php
require_once "../config/config.php";
require_once "../models/usuario.php";
require_once "../models/vendedor.php";
require_once "../models/producto.php";
require_once "../models/pedido.php";
require_once "../models/categoria.php";

if(!esAdmin()) {
    redirect('login.php');
}

$appUsuario = new Usuario();
$appVendedor = new Vendedor();
$appProducto = new Producto();
$appPedido = new Pedido();
$appCategoria = new Categoria();

$total_usuarios = count($appUsuario->read());
$total_vendedores = count($appVendedor->read());
$vendedores_pendientes = count($appVendedor->readPendientes());
$total_productos = count($appProducto->read());
$total_categorias = count($appCategoria->readAll());

$pedidos = $appPedido->read();
$total_pedidos = count($pedidos);
$pedidos_pendientes = count(array_filter($pedidos, fn($p) => $p['estado'] == 'pendiente'));

$estadisticas = $appPedido->obtenerEstadisticas();
$ventas_totales = $estadisticas['ventas_totales'] ?? 0;
$comisiones_totales = $estadisticas['comisiones_totales'] ?? 0;

$ultimos_pedidos = array_slice($pedidos, 0, 5);

$usuarios = $appUsuario->read();
$ultimos_usuarios = array_slice($usuarios, 0, 5);

$pageTitle = 'Dashboard - Panel Admin';
include_once "views/header.php";
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <div class="d-none d-sm-inline-block">
            <button class="btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-download"></i> Generar Reporte
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= formatearPrecio($ventas_totales) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Comisiones (15%)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= formatearPrecio($comisiones_totales) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Pedidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $total_pedidos ?>
                            </div>
                            <small class="text-muted">
                                <?= $pedidos_pendientes ?> pendientes
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Vendedores Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $vendedores_pendientes ?>
                            </div>
                            <?php if($vendedores_pendientes > 0): ?>
                                <small class="text-danger">Requiere atención</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-shop fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-people fs-2 text-primary mb-2"></i>
                    <h4 class="mb-0"><?= $total_usuarios ?></h4>
                    <small class="text-muted">Usuarios</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-shop-window fs-2 text-success mb-2"></i>
                    <h4 class="mb-0"><?= $total_vendedores ?></h4>
                    <small class="text-muted">Vendedores</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-box fs-2 text-info mb-2"></i>
                    <h4 class="mb-0"><?= $total_productos ?></h4>
                    <small class="text-muted">Productos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-grid fs-2 text-warning mb-2"></i>
                    <h4 class="mb-0"><?= $total_categorias ?></h4>
                    <small class="text-muted">Categorías</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Pedidos</h6>
                    <a href="pedido.php" class="btn btn-sm btn-primary">Ver todos</a>
                </div>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ultimos_pedidos as $pedido): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($pedido['numero_pedido']) ?></td>
                                        <td><?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                        <td><?= formatearPrecio($pedido['total']) ?></td>
                                        <td><?= obtenerBadgeEstado($pedido['estado']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Usuarios</h6>
                    <a href="usuario.php" class="btn btn-sm btn-primary">Ver todos</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach($ultimos_usuarios as $usuario): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">
                                            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= tiempoTranscurrido($usuario['fecha_registro']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <?php if($vendedores_pendientes > 0): ?>
                        <a href="vendedor.php?action=aprobar" class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-exclamation-circle"></i> 
                            Aprobar Vendedores (<?= $vendedores_pendientes ?>)
                        </a>
                    <?php endif; ?>
                    <a href="categoria.php?action=create" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Nueva Categoría
                    </a>
                    <a href="pedido.php" class="btn btn-info w-100 mb-2">
                        <i class="bi bi-box-seam"></i> Ver Pedidos
                    </a>
                    <a href="usuario.php" class="btn btn-secondary w-100">
                        <i class="bi bi-people"></i> Gestionar Usuarios
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "views/footer.php"; ?>