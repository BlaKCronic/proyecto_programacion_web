<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/resena.php';

if(!estaLogueado()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if(!$data) {
    // also support form posts
    $data = $_POST;
}

$id_producto = isset($data['id_producto']) ? (int)$data['id_producto'] : (isset($data['producto_id']) ? (int)$data['producto_id'] : 0);
$calificacion = isset($data['calificacion']) ? (int)$data['calificacion'] : 0;
$titulo = isset($data['titulo']) ? trim($data['titulo']) : '';
$comentario = isset($data['comentario']) ? trim($data['comentario']) : '';

if($id_producto <= 0 || $calificacion < 1 || $calificacion > 5) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$usuario = obtenerUsuarioActual();
if(!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit();
}

$appResena = new Resena();
// Verificamos que el usuario pueda reseñar (esto es redundante pero seguro)
if(!$appResena->usuarioPuedeResenar($usuario['id'], $id_producto)) {
    echo json_encode(['success' => false, 'message' => 'No puedes reseñar este producto']);
    exit();
}

$payload = [
    'id_producto' => $id_producto,
    'id_usuario' => $usuario['id'],
    'calificacion' => $calificacion,
    'titulo' => $titulo,
    'comentario' => $comentario
];

$res = $appResena->create($payload);
if($res && $res > 0) {
    echo json_encode(['success' => true, 'message' => 'Reseña creada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la reseña']);
}

?>