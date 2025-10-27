<?php
define('DB_HOST', 'mariadb');
define('DB_NAME', 'database');
define('DB_USER', 'user');
define('DB_PASS', 'password');

define('BASE_URL', 'http://localhost');
define('IMG_PATH', __DIR__ . '/../img/');
define('IMG_URL', BASE_URL . '/img/');

define('APP_NAME', 'Amazon Lite');
define('ITEMS_PER_PAGE', 12);
define('COMISION_PLATAFORMA', 0.15);

define('COSTO_ENVIO_BASE', 99.00);
define('ENVIO_GRATIS_DESDE', 500.00);
define('IVA', 0.16);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function esVendedor() {
    return isset($_SESSION['vendedor_id']);
}

function esAdmin() {
    return isset($_SESSION['admin_id']);
}

function obtenerUsuarioActual() {
    if(estaLogueado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email']
        ];
    }
    return null;
}

function obtenerVendedorActual() {
    if(esVendedor()) {
        return [
            'id' => $_SESSION['vendedor_id'],
            'nombre_tienda' => $_SESSION['vendedor_nombre_tienda'],
            'email' => $_SESSION['vendedor_email']
        ];
    }
    return null;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function mostrarAlerta($mensaje, $tipo = 'info') {
    $iconos = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info' => 'bi-info-circle-fill'
    ];
    
    $icono = $iconos[$tipo] ?? $iconos['info'];
    
    return "<div class='alert alert-{$tipo} alert-dismissible fade show' role='alert'>
                <i class='bi {$icono} me-2'></i>
                {$mensaje}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

function formatearPrecio($precio) {
    return '$' . number_format($precio, 2);
}

function calcularDescuento($precio_original, $precio_descuento) {
    if($precio_descuento && $precio_descuento < $precio_original) {
        $descuento = (($precio_original - $precio_descuento) / $precio_original) * 100;
        return round($descuento);
    }
    return 0;
}

function obtenerPrecioFinal($producto) {
    return $producto['precio_descuento'] ?? $producto['precio'];
}

function tiempoTranscurrido($fecha) {
    $ahora = time();
    $tiempo = strtotime($fecha);
    $diferencia = $ahora - $tiempo;
    
    if($diferencia < 60) {
        return 'hace un momento';
    } elseif($diferencia < 3600) {
        $minutos = floor($diferencia / 60);
        return "hace $minutos " . ($minutos == 1 ? 'minuto' : 'minutos');
    } elseif($diferencia < 86400) {
        $horas = floor($diferencia / 3600);
        return "hace $horas " . ($horas == 1 ? 'hora' : 'horas');
    } elseif($diferencia < 604800) {
        $dias = floor($diferencia / 86400);
        return "hace $dias " . ($dias == 1 ? 'día' : 'días');
    } else {
        return date('d/m/Y', $tiempo);
    }
}

function generarEstrellas($calificacion, $total = 5) {
    $html = '';
    $calificacion_entera = floor($calificacion);
    $tiene_media = ($calificacion - $calificacion_entera) >= 0.5;
    
    for($i = 1; $i <= $total; $i++) {
        if($i <= $calificacion_entera) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif($i == $calificacion_entera + 1 && $tiene_media) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    
    return $html;
}

function obtenerBadgeEstado($estado) {
    $badges = [
        'pendiente' => 'warning',
        'procesando' => 'info',
        'enviado' => 'primary',
        'entregado' => 'success',
        'cancelado' => 'danger',
        'confirmado' => 'info',
        'preparando' => 'warning',
        'aprobado' => 'success',
        'rechazado' => 'danger'
    ];
    
    $clase = $badges[$estado] ?? 'secondary';
    return "<span class='badge bg-{$clase}'>" . ucfirst($estado) . "</span>";
}
?>