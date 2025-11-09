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

$ventas_por_dia = [];
for($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $ventas_dia = array_filter($pedidos, function($p) use ($fecha) {
        return date('Y-m-d', strtotime($p['fecha_pedido'])) == $fecha;
    });
    $total_dia = array_sum(array_column($ventas_dia, 'total'));
    $ventas_por_dia[] = [
        'fecha' => date('d/m', strtotime($fecha)),
        'total' => $total_dia
    ];
}

$estados_pedidos = [
    'pendiente' => 0,
    'procesando' => 0,
    'enviado' => 0,
    'entregado' => 0,
    'cancelado' => 0
];
foreach($pedidos as $pedido) {
    if(isset($estados_pedidos[$pedido['estado']])) {
        $estados_pedidos[$pedido['estado']]++;
    }
}

$productos_por_categoria = [];
$productos = $appProducto->read();
foreach($categorias = $appCategoria->readAll() as $cat) {
    $count = count(array_filter($productos, fn($p) => $p['id_categoria'] == $cat['id_categoria']));
    if($count > 0) {
        $productos_por_categoria[] = [
            'nombre' => $cat['nombre'],
            'cantidad' => $count
        ];
    }
}

$vendedores_todos = $appVendedor->read();
usort($vendedores_todos, function($a, $b) {
    return $b['total_ventas'] - $a['total_ventas'];
});
$top_vendedores = array_slice($vendedores_todos, 0, 5);

$pageTitle = 'Dashboard - Panel Admin';
include_once "views/header.php";
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <div class="d-none d-sm-inline-block">
            <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalReportes">
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
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Pedidos por Estado
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="pedidosEstadoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bar-chart"></i> Productos por Categoría
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="productosCategoria"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-trophy"></i> Top 5 Vendedores
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="topVendedoresChart"></canvas>
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

<div class="modal fade" id="modalReportes" tabindex="-1" aria-labelledby="modalReportesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReportesLabel">
                    <i class="bi bi-file-earmark-pdf"></i> Generar Reporte PDF
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="generar_reporte.php" method="POST" target="_blank" id="formReporte">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-file-text"></i> Tipo de Reporte
                        </label>
                        <select class="form-select" name="tipo_reporte" required>
                            <option value="ventas">📊 Reporte de Ventas</option>
                            <option value="productos">📦 Reporte de Productos</option>
                            <option value="usuarios">👥 Reporte de Usuarios</option>
                            <option value="vendedores">🏪 Reporte de Vendedores</option>
                        </select>
                        <div class="form-text">Selecciona el tipo de información que deseas incluir en el reporte</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar-range"></i> Período
                        </label>
                        <select class="form-select" name="tipo_fecha" id="tipoFecha" required>
                            <option value="mes_actual">📅 Mes Actual</option>
                            <option value="mes_anterior">📅 Mes Anterior</option>
                            <option value="ultimo_trimestre">📅 Último Trimestre (3 meses)</option>
                            <option value="personalizado">📅 Personalizado</option>
                            <option value="todo">📅 Todo hasta la fecha</option>
                        </select>
                    </div>

                    <div id="fechasPersonalizadas" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" name="fecha_inicio" 
                                       value="<?= date('Y-m-01') ?>" id="fechaInicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" name="fecha_fin" 
                                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" id="fechaFin">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Nota:</strong> El reporte se generará en formato PDF y se descargará automáticamente. 
                        Puede tardar unos segundos dependiendo de la cantidad de datos.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoFechaSelect = document.getElementById('tipoFecha');
    const fechasPersonalizadas = document.getElementById('fechasPersonalizadas');
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    
    if(tipoFechaSelect) {
        tipoFechaSelect.addEventListener('change', function() {
            if(this.value === 'personalizado') {
                fechasPersonalizadas.style.display = 'block';
                fechaInicio.required = true;
                fechaFin.required = true;
            } else {
                fechasPersonalizadas.style.display = 'none';
                fechaInicio.required = false;
                fechaFin.required = false;
            }
        });
    }
    
    if(fechaFin) {
        fechaFin.addEventListener('change', function() {
            if(fechaInicio.value && fechaFin.value) {
                if(new Date(fechaFin.value) < new Date(fechaInicio.value)) {
                    alert('La fecha fin debe ser posterior a la fecha de inicio');
                    fechaFin.value = fechaInicio.value;
                }
            }
        });
    }
    
    const formReporte = document.getElementById('formReporte');
    if(formReporte) {
        formReporte.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';
            
            setTimeout(function() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 3000);
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctxVentas = document.getElementById('ventasDiasChart').getContext('2d');
new Chart(ctxVentas, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($ventas_por_dia, 'fecha')) ?>,
        datasets: [{
            label: 'Ventas',
            data: <?= json_encode(array_column($ventas_por_dia, 'total')) ?>,
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
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

const ctxEstado = document.getElementById('pedidosEstadoChart').getContext('2d');
new Chart(ctxEstado, {
    type: 'doughnut',
    data: {
        labels: ['Pendiente', 'Procesando', 'Enviado', 'Entregado', 'Cancelado'],
        datasets: [{
            data: [
                <?= $estados_pedidos['pendiente'] ?>,
                <?= $estados_pedidos['procesando'] ?>,
                <?= $estados_pedidos['enviado'] ?>,
                <?= $estados_pedidos['entregado'] ?>,
                <?= $estados_pedidos['cancelado'] ?>
            ],
            backgroundColor: [
                'rgb(246, 194, 62)',
                'rgb(54, 185, 204)',
                'rgb(78, 115, 223)',
                'rgb(28, 200, 138)',
                'rgb(231, 74, 59)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

const ctxCategorias = document.getElementById('productosCategoria').getContext('2d');
new Chart(ctxCategorias, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($productos_por_categoria, 'nombre')) ?>,
        datasets: [{
            label: 'Productos',
            data: <?= json_encode(array_column($productos_por_categoria, 'cantidad')) ?>,
            backgroundColor: 'rgba(54, 185, 204, 0.8)'
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
                    stepSize: 1
                }
            }
        }
    }
});

const ctxVendedores = document.getElementById('topVendedoresChart').getContext('2d');
new Chart(ctxVendedores, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach($top_vendedores as $v): ?>
                '<?= htmlspecialchars(substr($v['nombre_tienda'], 0, 20)) ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Total Ventas',
            data: [
                <?php foreach($top_vendedores as $v): ?>
                    <?= $v['total_ventas'] ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(28, 200, 138, 0.8)'
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
</script>

<?php include_once "views/footer.php"; ?>