<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/resena.php';

header('Content-Type: application/json; charset=utf-8');

if(!estaLogueado()){
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión para dejar una reseña.']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if(!$input) $input = $_POST;

$id_producto = isset($input['id_producto']) ? (int)$input['id_producto'] : 0;
$calificacion = isset($input['calificacion']) ? (int)$input['calificacion'] : 0;
$titulo = trim($input['titulo'] ?? '');
$comentario = trim($input['comentario'] ?? '');

if($id_producto <= 0 || $calificacion < 1 || $calificacion > 5 || mb_strlen($comentario) == 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$appResena = new Resena();
$id_usuario = $_SESSION['usuario_id'];

if(!$appResena->usuarioPuedeResenar($id_usuario, $id_producto)){
    echo json_encode(['success' => false, 'message' => 'No puedes reseñar este producto.']);
    exit;
}

$data = [
    'id_producto' => $id_producto,
    'id_usuario' => $id_usuario,
    'calificacion' => $calificacion,
    'titulo' => $titulo,
    'comentario' => $comentario
];

$ok = $appResena->create($data);

if($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar la reseña.']);
}

?>