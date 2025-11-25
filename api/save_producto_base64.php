<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/producto.php';

$resp = ['success' => false, 'message' => ''];

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $resp['message'] = 'Método no permitido';
    echo json_encode($resp);
    exit;
}

$productoId = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'principal';

if($productoId <= 0) {
    http_response_code(400);
    $resp['message'] = 'producto_id inválido';
    echo json_encode($resp);
    exit;
}

$appProducto = new Producto();

function file_to_data_url($tmpPath) {
    $data = file_get_contents($tmpPath);
    if($data === false) return null;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    $b64 = base64_encode($data);
    return "data:$mime;base64,$b64";
}

try {
    if($tipo === 'principal') {
        if(!isset($_FILES['imagen_principal']) || $_FILES['imagen_principal']['error'] !== 0) {
            throw new Exception('Falta archivo imagen_principal o error en upload');
        }

        $dataUrl = file_to_data_url($_FILES['imagen_principal']['tmp_name']);
        if(!$dataUrl) throw new Exception('No se pudo leer el archivo');

        $rows = $appProducto->saveImagenPrincipalBase64($productoId, $dataUrl);

        $ext = pathinfo($_FILES['imagen_principal']['name'], PATHINFO_EXTENSION);
        $newname = uniqid() . '.' . $ext;
        @move_uploaded_file($_FILES['imagen_principal']['tmp_name'], __DIR__ . '/../img/productos/' . $newname);

        $resp['success'] = true;
        $resp['message'] = 'Imagen principal codificada (data URL) y guardada en BD';
        $resp['rows'] = $rows;
        echo json_encode($resp);
        exit;
    } else {
        if(!isset($_FILES['imagenes_adicionales'])) {
            throw new Exception('Faltan archivos imagenes_adicionales');
        }

        $count = count($_FILES['imagenes_adicionales']['name']);
        $dataUrls = [];
        $savedFiles = [];

        for($i=0;$i<$count;$i++) {
            if($_FILES['imagenes_adicionales']['error'][$i] !== 0) continue;
            $tmp = $_FILES['imagenes_adicionales']['tmp_name'][$i];
            $dataUrl = file_to_data_url($tmp);
            if($dataUrl) $dataUrls[] = $dataUrl;

            $ext = pathinfo($_FILES['imagenes_adicionales']['name'][$i], PATHINFO_EXTENSION);
            $newname = uniqid() . '.' . $ext;
            if(move_uploaded_file($tmp, __DIR__ . '/../img/productos/' . $newname)) {
                $savedFiles[] = $newname;
            }
        }

        if(empty($dataUrls)) throw new Exception('No se procesaron imágenes adicionales');

        $json = json_encode($dataUrls);
        $rows = $appProducto->saveImagenesAdicionalesBase64($productoId, $json);

        foreach($savedFiles as $f) {}

        $resp['success'] = true;
        $resp['message'] = 'Imágenes adicionales codificadas (data URLs) y guardadas en BD';
        $resp['count'] = count($dataUrls);
        echo json_encode($resp);
        exit;
    }
} catch(Exception $e) {
    http_response_code(500);
    $resp['message'] = $e->getMessage();
    echo json_encode($resp);
    exit;
}

?>
