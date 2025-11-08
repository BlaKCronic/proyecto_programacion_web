<?php
require_once "../config/config.php";
require_once "../models/vendedor.php";
require_once "../models/producto.php";
require_once "../models/pedido.php";

if(!esVendedor()) {
    redirect('login.php');
}

$appVendedor = new Vendedor();
$appProducto = new Producto();
$appPedido = new Pedido();

$vendedor_id = $_SESSION['vendedor_id'];
$vendedor = $appVendedor->readOne($vendedor_id);

$productos = $appProducto->readByVendedor($vendedor_id);
$total_productos = count($productos);
$productos_activos = count(array_filter($productos, function($p) { return $p['activo'] == 1; }));
$productos_sin_stock = count(array_filter($productos, function($p) { return $p['stock'] == 0; }));

$estadisticas = $appPedido->obtenerEstadisticas($vendedor_id);
$total_pedidos = $estadisticas['total_pedidos'] ?? 0;
$ventas_totales = $estadisticas['ventas_totales'] ?? 0;
$comisiones_totales = $estadisticas['comisiones_totales'] ?? 0;
$ganancias_netas = $ventas_totales - $comisiones_totales;

$pedidos_recientes = $appPedido->readByVendedor($vendedor_id);
$pedidos_recientes = array_slice($pedidos_recientes, 0, 5);

$ventas_por_dia = [];
for($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    
    $sql_dia = "SELECT SUM(dp.subtotal) as total
                FROM detalle_pedidos dp
                INNER JOIN pedidos p ON dp.id_pedido = p.id_pedido
                WHERE dp.id_vendedor = :vendedor_id 
                AND DATE(p.fecha_pedido) = :fecha";
    
    $sistema = new Sistema();
    $sistema->conect();
    $stmt = $sistema->_BD->prepare($sql_dia);
    $stmt->bindParam(':vendedor_id', $vendedor_id, PDO::PARAM_INT);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $ventas_por_dia[] = [
        'fecha' => date('d/m', strtotime($fecha)),
        'total' => $resultado['total'] ?? 0
    ];
}

$sql_top = "SELECT p.nombre, SUM(dp.cantidad) as total_vendido, p.imagen_principal
            FROM detalle_pedidos dp
            INNER JOIN productos p ON dp.id_producto = p.id_producto
            WHERE dp.id_vendedor = :vendedor_id
            GROUP BY dp.id_producto
            ORDER BY total_vendido DESC
            LIMIT 5";

$sistema = new Sistema();
$sistema->conect();
$stmt = $sistema->_BD->prepare($sql_top);
$stmt->bindParam(':vendedor_id', $vendedor_id, PDO::PARAM_INT);
$stmt->execute();
$top_productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_categorias = "SELECT c.nombre, COUNT(p.id_producto) as cantidad
                   FROM productos p
                   INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                   WHERE p.id_vendedor = :vendedor_id
                   GROUP BY c.id_categoria
                   ORDER BY cantidad DESC";

$stmt = $sistema->_BD->prepare($sql_categorias);
$stmt->bindParam(':vendedor_id', $vendedor_id, PDO::PARAM_INT);
$stmt->execute();
$productos_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stock_alto = count(array_filter($productos, fn($p) => $p['stock'] >= 20));
$stock_medio = count(array_filter($productos, fn($p) => $p['stock'] >= 10 && $p['stock'] < 20));
$stock_bajo = count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] < 10));
$sin_stock = $productos_sin_stock;

$pageTitle = 'Dashboard - ' . $vendedor['nombre_tienda'];
include_once "views/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <?php include_once "views/sidebar.php"; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-speedometer2 text-warning"></i> Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-calendar"></i> Hoy
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-calendar-week"></i> Semana
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-calendar-month"></i> Mes
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100">
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
                                    <i class="bi bi-currency-dollar fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Ganancias Netas
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= formatearPrecio($ganancias_netas) ?>
                                    </div>
                                    <small class="text-muted">Después de comisión</small>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-graph-up fs-2 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Pedidos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $total_pedidos ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-box-seam fs-2 text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Productos Activos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $productos_activos ?> / <?= $total_productos ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-shop fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($productos_sin_stock > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atención:</strong> Tienes <?= $productos_sin_stock ?> producto(s) sin stock.
                    <a href="productos.php?sin_stock=1" class="alert-link">Ver productos</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($vendedor['estado_aprobacion'] == 'pendiente'): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Tu cuenta está pendiente de aprobación.</strong> Recibirás un email cuando sea aprobada.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-graph-up"></i> Ventas de los últimos 7 días
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ventasDiasChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-boxes"></i> Estado del Inventario
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-trophy"></i> Top 5 Productos Más Vendidos
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($top_productos)): ?>
                                <canvas id="topProductosChart"></canvas>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p>Aún no tienes ventas registradas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-pie-chart"></i> Productos por Categoría
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($productos_por_categoria)): ?>
                                <canvas id="categoriasChart"></canvas>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p>No tienes productos registrados</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-clock-history"></i> Pedidos Recientes
                            </h6>
                            <a href="pedidos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($pedidos_recientes)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Pedido</th>
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Total</th>
                                                <th>Estado</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($pedidos_recientes as $pedido): ?>
                                                <tr>
                                                    <td class="fw-bold"><?= htmlspecialchars($pedido['numero_pedido']) ?></td>
                                                    <td><?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                                    <td><?= formatearPrecio($pedido['total']) ?></td>
                                                    <td><?= obtenerBadgeEstado($pedido['estado']) ?></td>
                                                    <td>
                                                        <a href="pedidos.php?detalle=<?= $pedido['id_pedido'] ?>" 
                                                           class="btn btn-sm btn-outline-info">
                                                            Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No hay pedidos aún</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-shop"></i> Tu Tienda
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <?php if($vendedor['logo']): ?>
                                    <img src="../img/vendedores/<?= $vendedor['logo'] ?>" 
                                         class="img-fluid rounded" style="max-height: 100px;" 
                                         alt="<?= htmlspecialchars($vendedor['nombre_tienda']) ?>">
                                <?php else: ?>
                                    <div class="bg-warning text-white rounded d-inline-flex align-items-center justify-content-center" 
                                         style="width: 100px; height: 100px;">
                                        <i class="bi bi-shop fs-1"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h5 class="text-center mb-3"><?= htmlspecialchars($vendedor['nombre_tienda']) ?></h5>
                            
                            <hr>
                            
                            <div class="mb-2">
                                <strong>Calificación:</strong>
                                <div class="float-end">
                                    <?= generarEstrellas($vendedor['calificacion_promedio']) ?>
                                    <span class="text-muted">(<?= number_format($vendedor['calificacion_promedio'], 1) ?>)</span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <strong>Total ventas:</strong>
                                <span class="float-end"><?= $vendedor['total_ventas'] ?></span>
                            </div>
                            <div class="mb-2">
                                <strong>Comisión:</strong>
                                <span class="float-end"><?= $vendedor['comision'] ?>%</span>
                            </div>
                            <div class="mb-2">
                                <strong>Miembro desde:</strong>
                                <span class="float-end"><?= date('Y', strtotime($vendedor['fecha_registro'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-lightning-fill"></i> Acciones Rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <a href="productos.php?action=create" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-plus-circle"></i> Nuevo Producto
                            </a>
                            <a href="pedidos.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-box-seam"></i> Ver Pedidos
                            </a>
                            <a href="productos.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-grid"></i> Mis Productos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctxVentas = document.getElementById('ventasDiasChart');
if(ctxVentas) {
    new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($ventas_por_dia, 'fecha')) ?>,
            datasets: [{
                label: 'Ventas',
                data: <?= json_encode(array_column($ventas_por_dia, 'total')) ?>,
                borderColor: 'rgb(254, 189, 105)',
                backgroundColor: 'rgba(254, 189, 105, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

const ctxStock = document.getElementById('stockChart');
if(ctxStock) {
    new Chart(ctxStock, {
        type: 'doughnut',
        data: {
            labels: ['Stock Alto (20+)', 'Stock Medio (10-19)', 'Stock Bajo (1-9)', 'Sin Stock'],
            datasets: [{
                data: [<?= $stock_alto ?>, <?= $stock_medio ?>, <?= $stock_bajo ?>, <?= $sin_stock ?>],
                backgroundColor: [
                    'rgb(28, 200, 138)',
                    'rgb(54, 185, 204)',
                    'rgb(246, 194, 62)',
                    'rgb(231, 74, 59)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
}

const ctxTopProductos = document.getElementById('topProductosChart');
if(ctxTopProductos) {
    new Chart(ctxTopProductos, {
        type: 'bar',
        data: {
            labels: [
                <?php foreach($top_productos as $tp): ?>
                    '<?= htmlspecialchars(substr($tp['nombre'], 0, 30)) . (strlen($tp['nombre']) > 30 ? '...' : '') ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Unidades vendidas',
                data: [
                    <?php foreach($top_productos as $tp): ?>
                        <?= $tp['total_vendido'] ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: 'rgba(254, 189, 105, 0.8)'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

const ctxCategorias = document.getElementById('categoriasChart');
if(ctxCategorias) {
    new Chart(ctxCategorias, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($productos_por_categoria, 'nombre')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($productos_por_categoria, 'cantidad')) ?>,
                backgroundColor: [
                    'rgb(78, 115, 223)',
                    'rgb(28, 200, 138)',
                    'rgb(54, 185, 204)',
                    'rgb(246, 194, 62)',
                    'rgb(231, 74, 59)',
                    'rgb(133, 135, 150)',
                    'rgb(90, 92, 105)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
}
</script>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.text-xs {
    font-size: 0.7rem;
    font-weight: bold;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}
</style>

<?php include_once "views/footer.php"; ?>