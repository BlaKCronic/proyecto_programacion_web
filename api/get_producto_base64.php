<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/producto.php';

$resp = ['success' => false, 'data' => null];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) {
    http_response_code(400);
    $resp['message'] = 'id inválido';
    echo json_encode($resp);
    exit;
}

$appProducto = new Producto();
$producto = $appProducto->readOne($id);

if(!$producto) {
    http_response_code(404);
    $resp['message'] = 'Producto no encontrado';
    echo json_encode($resp);
    exit;
}

$out = [];
if(!empty($producto['imagen_principal'])) {
    $val = $producto['imagen_principal'];
    if(strpos($val, 'data:') === 0) {
        $out['imagen_principal'] = $val;
    } else {
        $path = __DIR__ . '/../img/productos/' . $val;
        if(file_exists($path)) {
            $data = file_get_contents($path);
            $mime = mime_content_type($path);
            $b64 = base64_encode($data);
            $out['imagen_principal'] = "data:$mime;base64,$b64";
        } else {
            $out['imagen_principal'] = null;
        }
    }
}

if(!empty($producto['imagenes_adicionales'])) {
    $raw = trim($producto['imagenes_adicionales']);
    $arr = null;
    if(strlen($raw) > 0 && $raw[0] === '[') {
        $arr = json_decode($raw, true);
    }

    $result = [];
    if(is_array($arr)) {
        foreach($arr as $item) {
            if(strpos($item, 'data:') === 0) {
                $result[] = $item;
            } else {
                $path = __DIR__ . '/../img/productos/' . $item;
                if(file_exists($path)) {
                    $data = file_get_contents($path);
                    $mime = mime_content_type($path);
                    $b64 = base64_encode($data);
                    $result[] = "data:$mime;base64,$b64";
                }
            }
        }
    } else {
        $files = array_filter(array_map('trim', explode(',', $raw)));
        foreach($files as $f) {
            $path = __DIR__ . '/../img/productos/' . $f;
            if(file_exists($path)) {
                $data = file_get_contents($path);
                $mime = mime_content_type($path);
                $b64 = base64_encode($data);
                $result[] = "data:$mime;base64,$b64";
            }
        }
    }

    $out['imagenes_adicionales'] = $result;
}

$resp['success'] = true;
$resp['data'] = $out;
echo json_encode($resp);
exit;

?>
