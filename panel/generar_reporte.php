<?php
require_once "../config/config.php";
require_once "../models/pedido.php";
require_once "../models/producto.php";
require_once "../models/usuario.php";
require_once "../models/vendedor.php";
require_once "../vendor/autoload.php";

use Spipu\Html2Pdf\Html2Pdf;

if(!esAdmin()) {
    die('Acceso denegado');
}

$tipo_reporte = $_POST['tipo_reporte'] ?? 'ventas';
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
$tipo_fecha = $_POST['tipo_fecha'] ?? 'personalizado';

if($tipo_fecha == 'mes_actual') {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-t');
} elseif($tipo_fecha == 'mes_anterior') {
    $fecha_inicio = date('Y-m-01', strtotime('first day of last month'));
    $fecha_fin = date('Y-m-t', strtotime('last day of last month'));
} elseif($tipo_fecha == 'ultimo_trimestre') {
    $fecha_inicio = date('Y-m-01', strtotime('-3 months'));
    $fecha_fin = date('Y-m-d');
} elseif($tipo_fecha == 'todo') {
    $fecha_inicio = '2020-01-01';
    $fecha_fin = date('Y-m-d');
}

$appPedido = new Pedido();
$appProducto = new Producto();
$appUsuario = new Usuario();
$appVendedor = new Vendedor();

$datos = [];
$titulo_reporte = '';

switch($tipo_reporte) {
    case 'ventas':
        $titulo_reporte = 'Reporte de Ventas';
        $datos = obtenerDatosVentas($appPedido, $fecha_inicio, $fecha_fin);
        break;
    case 'productos':
        $titulo_reporte = 'Reporte de Productos';
        $datos = obtenerDatosProductos($appProducto, $appPedido, $fecha_inicio, $fecha_fin);
        break;
    case 'usuarios':
        $titulo_reporte = 'Reporte de Usuarios';
        $datos = obtenerDatosUsuarios($appUsuario, $fecha_inicio, $fecha_fin);
        break;
    case 'vendedores':
        $titulo_reporte = 'Reporte de Vendedores';
        $datos = obtenerDatosVendedores($appVendedor, $appPedido, $fecha_inicio, $fecha_fin);
        break;
}

$html = generarHTMLReporte($tipo_reporte, $datos, $fecha_inicio, $fecha_fin, $titulo_reporte);

try {
    $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8');
    $html2pdf->setDefaultFont('Arial');
    $html2pdf->writeHTML($html);
    
    $nombre_archivo = 'reporte_' . $tipo_reporte . '_' . date('Ymd_His') . '.pdf';
    $html2pdf->output($nombre_archivo, 'D');
} catch(Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}


function obtenerDatosVentas($appPedido, $fecha_inicio, $fecha_fin) {
    $pedidos = $appPedido->read();
    
    $pedidos_filtrados = array_filter($pedidos, function($p) use ($fecha_inicio, $fecha_fin) {
        $fecha_pedido = date('Y-m-d', strtotime($p['fecha_pedido']));
        return $fecha_pedido >= $fecha_inicio && $fecha_pedido <= $fecha_fin;
    });
    
    $total_ventas = array_sum(array_column($pedidos_filtrados, 'total'));
    $total_pedidos = count($pedidos_filtrados);
    $comision_plataforma = $total_ventas * 0.15;
    
    $ventas_por_estado = [];
    foreach($pedidos_filtrados as $p) {
        if(!isset($ventas_por_estado[$p['estado']])) {
            $ventas_por_estado[$p['estado']] = ['cantidad' => 0, 'total' => 0];
        }
        $ventas_por_estado[$p['estado']]['cantidad']++;
        $ventas_por_estado[$p['estado']]['total'] += $p['total'];
    }
    
    $ventas_por_dia = [];
    foreach($pedidos_filtrados as $p) {
        $fecha = date('Y-m-d', strtotime($p['fecha_pedido']));
        if(!isset($ventas_por_dia[$fecha])) {
            $ventas_por_dia[$fecha] = 0;
        }
        $ventas_por_dia[$fecha] += $p['total'];
    }
    ksort($ventas_por_dia);
    
    return [
        'total_ventas' => $total_ventas,
        'total_pedidos' => $total_pedidos,
        'comision_plataforma' => $comision_plataforma,
        'promedio_venta' => $total_pedidos > 0 ? $total_ventas / $total_pedidos : 0,
        'ventas_por_estado' => $ventas_por_estado,
        'ventas_por_dia' => $ventas_por_dia,
        'pedidos' => array_slice($pedidos_filtrados, 0, 20)
    ];
}

function obtenerDatosProductos($appProducto, $appPedido, $fecha_inicio, $fecha_fin) {
    $productos = $appProducto->read();
    $pedidos = $appPedido->read();
    
    $pedidos_filtrados = array_filter($pedidos, function($p) use ($fecha_inicio, $fecha_fin) {
        $fecha_pedido = date('Y-m-d', strtotime($p['fecha_pedido']));
        return $fecha_pedido >= $fecha_inicio && $fecha_pedido <= $fecha_fin;
    });
    
    $productos_vendidos = [];
    foreach($pedidos_filtrados as $pedido) {
        $detalles = (new Pedido())->readDetalle($pedido['id_pedido']);
        foreach($detalles as $detalle) {
            if(!isset($productos_vendidos[$detalle['id_producto']])) {
                $productos_vendidos[$detalle['id_producto']] = [
                    'nombre' => $detalle['producto'],
                    'cantidad' => 0,
                    'total' => 0
                ];
            }
            $productos_vendidos[$detalle['id_producto']]['cantidad'] += $detalle['cantidad'];
            $productos_vendidos[$detalle['id_producto']]['total'] += $detalle['subtotal'];
        }
    }
    
    usort($productos_vendidos, fn($a, $b) => $b['cantidad'] - $a['cantidad']);
    
    return [
        'total_productos' => count($productos),
        'productos_activos' => count(array_filter($productos, fn($p) => $p['activo'])),
        'productos_sin_stock' => count(array_filter($productos, fn($p) => $p['stock'] == 0)),
        'productos_vendidos' => array_slice($productos_vendidos, 0, 10),
        'valor_inventario' => array_sum(array_map(fn($p) => $p['precio'] * $p['stock'], $productos))
    ];
}

function obtenerDatosUsuarios($appUsuario, $fecha_inicio, $fecha_fin) {
    $usuarios = $appUsuario->read();
    
    $usuarios_filtrados = array_filter($usuarios, function($u) use ($fecha_inicio, $fecha_fin) {
        $fecha_registro = date('Y-m-d', strtotime($u['fecha_registro']));
        return $fecha_registro >= $fecha_inicio && $fecha_registro <= $fecha_fin;
    });
    
    // Registros por mes
    $registros_por_mes = [];
    foreach($usuarios_filtrados as $u) {
        $mes = date('Y-m', strtotime($u['fecha_registro']));
        if(!isset($registros_por_mes[$mes])) {
            $registros_por_mes[$mes] = 0;
        }
        $registros_por_mes[$mes]++;
    }
    
    return [
        'total_usuarios' => count($usuarios),
        'usuarios_nuevos' => count($usuarios_filtrados),
        'usuarios_activos' => count(array_filter($usuarios, fn($u) => $u['activo'])),
        'registros_por_mes' => $registros_por_mes,
        'ultimos_usuarios' => array_slice($usuarios_filtrados, 0, 10)
    ];
}

function obtenerDatosVendedores($appVendedor, $appPedido, $fecha_inicio, $fecha_fin) {
    $vendedores = $appVendedor->read();
    $pedidos = $appPedido->read();
    
    $pedidos_filtrados = array_filter($pedidos, function($p) use ($fecha_inicio, $fecha_fin) {
        $fecha_pedido = date('Y-m-d', strtotime($p['fecha_pedido']));
        return $fecha_pedido >= $fecha_inicio && $fecha_pedido <= $fecha_fin;
    });
    
    $ventas_vendedor = [];
    foreach($pedidos_filtrados as $pedido) {
        $detalles = (new Pedido())->readDetalle($pedido['id_pedido']);
        foreach($detalles as $detalle) {
            $id_vend = $detalle['id_vendedor'];
            if(!isset($ventas_vendedor[$id_vend])) {
                $ventas_vendedor[$id_vend] = [
                    'nombre' => $detalle['nombre_tienda'],
                    'ventas' => 0,
                    'total' => 0,
                    'comision' => 0
                ];
            }
            $ventas_vendedor[$id_vend]['ventas']++;
            $ventas_vendedor[$id_vend]['total'] += $detalle['subtotal'];
            $ventas_vendedor[$id_vend]['comision'] += $detalle['comision_plataforma'];
        }
    }
    
    usort($ventas_vendedor, fn($a, $b) => $b['total'] - $a['total']);
    
    return [
        'total_vendedores' => count($vendedores),
        'vendedores_aprobados' => count(array_filter($vendedores, fn($v) => $v['estado_aprobacion'] == 'aprobado')),
        'vendedores_pendientes' => count(array_filter($vendedores, fn($v) => $v['estado_aprobacion'] == 'pendiente')),
        'ventas_por_vendedor' => array_slice($ventas_vendedor, 0, 10),
        'total_comisiones' => array_sum(array_column($ventas_vendedor, 'comision'))
    ];
}

function generarHTMLReporte($tipo, $datos, $fecha_inicio, $fecha_fin, $titulo) {
    $html = '<style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        h1 { color: #232F3E; font-size: 18pt; margin-bottom: 5px; }
        h2 { color: #FF9900; font-size: 14pt; margin-top: 15px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #232F3E; color: white; padding: 8px; text-align: left; font-size: 9pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { font-size: 24pt; color: #FF9900; font-weight: bold; }
        .info-box { background-color: #f5f5f5; padding: 10px; margin: 10px 0; border-left: 4px solid #FF9900; }
        .total { font-weight: bold; font-size: 11pt; color: #232F3E; }
        .footer { position: fixed; bottom: 10mm; width: 100%; text-align: center; font-size: 8pt; color: #666; }
    </style>';
    
    $html .= '<page backtop="10mm" backbottom="15mm" backleft="10mm" backright="10mm">';
    
    $html .= '<div class="header">';
    $html .= '<div class="logo">AMAZON LITE</div>';
    $html .= '<h1>' . $titulo . '</h1>';
    $html .= '<p style="font-size: 10pt;">Período: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>';
    $html .= '<p style="font-size: 9pt; color: #666;">Generado: ' . date('d/m/Y H:i:s') . '</p>';
    $html .= '</div>';
    
    switch($tipo) {
        case 'ventas':
            $html .= generarReporteVentas($datos);
            break;
        case 'productos':
            $html .= generarReporteProductos($datos);
            break;
        case 'usuarios':
            $html .= generarReporteUsuarios($datos);
            break;
        case 'vendedores':
            $html .= generarReporteVendedores($datos);
            break;
    }
    
    $html .= '<div class="footer">';
    $html .= '<p>Amazon Lite - Panel de Administración | Página [[page_cu]] de [[page_nb]]</p>';
    $html .= '</div>';
    
    $html .= '</page>';
    
    return $html;
}

function generarReporteVentas($datos) {
    $html = '<h2>Resumen General</h2>';
    $html .= '<table>';
    $html .= '<tr><th style="width: 70%;">Métrica</th><th style="width: 30%; text-align: right;">Valor</th></tr>';
    $html .= '<tr><td>Total de Ventas</td><td class="total" style="text-align: right;">$' . number_format($datos['total_ventas'], 2) . '</td></tr>';
    $html .= '<tr><td>Total de Pedidos</td><td style="text-align: right;">' . $datos['total_pedidos'] . '</td></tr>';
    $html .= '<tr><td>Promedio por Venta</td><td style="text-align: right;">$' . number_format($datos['promedio_venta'], 2) . '</td></tr>';
    $html .= '<tr><td>Comisión Plataforma (15%)</td><td class="total" style="text-align: right;">$' . number_format($datos['comision_plataforma'], 2) . '</td></tr>';
    $html .= '</table>';
    
    if(!empty($datos['ventas_por_estado'])) {
        $html .= '<h2>Ventas por Estado</h2>';
        $html .= '<table>';
        $html .= '<tr><th style="width: 50%;">Estado</th><th style="width: 25%; text-align: center;">Cantidad</th><th style="width: 25%; text-align: right;">Total</th></tr>';
        foreach($datos['ventas_por_estado'] as $estado => $info) {
            $html .= '<tr>';
            $html .= '<td>' . ucfirst($estado) . '</td>';
            $html .= '<td style="text-align: center;">' . $info['cantidad'] . '</td>';
            $html .= '<td style="text-align: right;">$' . number_format($info['total'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    if(!empty($datos['pedidos'])) {
        $html .= '<h2>Últimos Pedidos</h2>';
        $html .= '<table>';
        $html .= '<tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th style="text-align: right;">Total</th><th>Estado</th></tr>';
        foreach($datos['pedidos'] as $pedido) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($pedido['numero_pedido']) . '</td>';
            $html .= '<td>' . htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($pedido['fecha_pedido'])) . '</td>';
            $html .= '<td style="text-align: right;">$' . number_format($pedido['total'], 2) . '</td>';
            $html .= '<td>' . ucfirst($pedido['estado']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    return $html;
}

function generarReporteProductos($datos) {
    $html = '<h2>Resumen de Inventario</h2>';
    $html .= '<table>';
    $html .= '<tr><th style="width: 70%;">Métrica</th><th style="width: 30%; text-align: right;">Valor</th></tr>';
    $html .= '<tr><td>Total de Productos</td><td style="text-align: right;">' . $datos['total_productos'] . '</td></tr>';
    $html .= '<tr><td>Productos Activos</td><td style="text-align: right;">' . $datos['productos_activos'] . '</td></tr>';
    $html .= '<tr><td>Productos sin Stock</td><td style="text-align: right;">' . $datos['productos_sin_stock'] . '</td></tr>';
    $html .= '<tr><td>Valor del Inventario</td><td class="total" style="text-align: right;">$' . number_format($datos['valor_inventario'], 2) . '</td></tr>';
    $html .= '</table>';
    
    if(!empty($datos['productos_vendidos'])) {
        $html .= '<h2>Top 10 Productos Más Vendidos</h2>';
        $html .= '<table>';
        $html .= '<tr><th style="width: 10%;">#</th><th style="width: 50%;">Producto</th><th style="width: 20%; text-align: center;">Cantidad</th><th style="width: 20%; text-align: right;">Total Ventas</th></tr>';
        $i = 1;
        foreach($datos['productos_vendidos'] as $prod) {
            $html .= '<tr>';
            $html .= '<td>' . $i++ . '</td>';
            $html .= '<td>' . htmlspecialchars($prod['nombre']) . '</td>';
            $html .= '<td style="text-align: center;">' . $prod['cantidad'] . '</td>';
            $html .= '<td style="text-align: right;">$' . number_format($prod['total'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    return $html;
}

function generarReporteUsuarios($datos) {
    $html = '<h2>Resumen de Usuarios</h2>';
    $html .= '<table>';
    $html .= '<tr><th style="width: 70%;">Métrica</th><th style="width: 30%; text-align: right;">Valor</th></tr>';
    $html .= '<tr><td>Total de Usuarios</td><td style="text-align: right;">' . $datos['total_usuarios'] . '</td></tr>';
    $html .= '<tr><td>Usuarios Nuevos (período)</td><td style="text-align: right;">' . $datos['usuarios_nuevos'] . '</td></tr>';
    $html .= '<tr><td>Usuarios Activos</td><td style="text-align: right;">' . $datos['usuarios_activos'] . '</td></tr>';
    $html .= '</table>';
    
    if(!empty($datos['registros_por_mes'])) {
        $html .= '<h2>Registros por Mes</h2>';
        $html .= '<table>';
        $html .= '<tr><th style="width: 70%;">Mes</th><th style="width: 30%; text-align: right;">Registros</th></tr>';
        foreach($datos['registros_por_mes'] as $mes => $cantidad) {
            $meses_es = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
                         'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
                         'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];
            $fecha_formateada = date('F Y', strtotime($mes . '-01'));
            $fecha_formateada = str_replace(array_keys($meses_es), array_values($meses_es), $fecha_formateada);
            $html .= '<tr>';
            $html .= '<td>' . $fecha_formateada . '</td>';
            $html .= '<td style="text-align: right;">' . $cantidad . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    if(!empty($datos['ultimos_usuarios'])) {
        $html .= '<h2>Últimos Usuarios Registrados</h2>';
        $html .= '<table>';
        $html .= '<tr><th>Nombre</th><th>Email</th><th>Fecha Registro</th></tr>';
        foreach($datos['ultimos_usuarios'] as $usuario) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . '</td>';
            $html .= '<td>' . htmlspecialchars($usuario['email']) . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($usuario['fecha_registro'])) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    return $html;
}

function generarReporteVendedores($datos) {
    $html = '<h2>Resumen de Vendedores</h2>';
    $html .= '<table>';
    $html .= '<tr><th style="width: 70%;">Métrica</th><th style="width: 30%; text-align: right;">Valor</th></tr>';
    $html .= '<tr><td>Total de Vendedores</td><td style="text-align: right;">' . $datos['total_vendedores'] . '</td></tr>';
    $html .= '<tr><td>Vendedores Aprobados</td><td style="text-align: right;">' . $datos['vendedores_aprobados'] . '</td></tr>';
    $html .= '<tr><td>Vendedores Pendientes</td><td style="text-align: right;">' . $datos['vendedores_pendientes'] . '</td></tr>';
    $html .= '<tr><td>Total Comisiones</td><td class="total" style="text-align: right;">$' . number_format($datos['total_comisiones'], 2) . '</td></tr>';
    $html .= '</table>';
    
    if(!empty($datos['ventas_por_vendedor'])) {
        $html .= '<h2>Top 10 Vendedores</h2>';
        $html .= '<table>';
        $html .= '<tr><th style="width: 10%;">#</th><th style="width: 40%;">Tienda</th><th style="width: 15%; text-align: center;">Ventas</th><th style="width: 20%; text-align: right;">Total</th><th style="width: 15%; text-align: right;">Comisión</th></tr>';
        $i = 1;
        foreach($datos['ventas_por_vendedor'] as $vend) {
            $html .= '<tr>';
            $html .= '<td>' . $i++ . '</td>';
            $html .= '<td>' . htmlspecialchars($vend['nombre']) . '</td>';
            $html .= '<td style="text-align: center;">' . $vend['ventas'] . '</td>';
            $html .= '<td style="text-align: right;">$' . number_format($vend['total'], 2) . '</td>';
            $html .= '<td style="text-align: right;">$' . number_format($vend['comision'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    return $html;
}
?>