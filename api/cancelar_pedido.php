<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/pedido.php';

header('Content-Type: application/json; charset=utf-8');

if(!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$user_id = $_SESSION['usuario_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit();
}

$pedidoModel = new Pedido();
$pedido = $pedidoModel->readOne($id);

if(!$pedido) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit();
}

if($pedido['id_usuario'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para cancelar este pedido']);
    exit();
}

if($pedido['estado'] != 'pendiente') {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'El pedido no puede ser cancelado en su estado actual']);
    exit();
}

$updated = $pedidoModel->cancelarPedido($id);

if($updated) {
    $detalles = $pedidoModel->readDetalle($id);
    $vendedores = [];
    foreach($detalles as $d) {
        if(isset($d['id_vendedor'])) $vendedores[] = $d['id_vendedor'];
    }
    $vendedores = array_values(array_unique($vendedores));

    $logEntry = [
        'tipo' => 'cancelacion_pedido',
        'pedido_id' => $id,
        'usuario_id' => $user_id,
        'vendedores' => $vendedores,
        'fecha' => date('c')
    ];
    $logPath = __DIR__ . '/../logs/cancelaciones.log';
    @file_put_contents($logPath, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

    echo json_encode(['success' => true, 'message' => 'Pedido cancelado correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cancelar el pedido']);
}

exit();

?>
