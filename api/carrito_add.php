<?php
require_once "../config/config.php";
require_once "../models/carrito.php";
require_once "../models/producto.php";

header('Content-Type: application/json');

if(!estaLogueado()) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para agregar productos al carrito'
    ]);
    exit();
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!isset($data['producto_id']) || !isset($data['cantidad'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

$producto_id = (int)$data['producto_id'];
$cantidad = (int)$data['cantidad'];
$usuario_id = $_SESSION['usuario_id'];

if($cantidad < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'La cantidad debe ser mayor a 0'
    ]);
    exit();
}

$appProducto = new Producto();
$producto = $appProducto->readOne($producto_id);

if(!$producto) {
    echo json_encode([
        'success' => false,
        'message' => 'El producto no existe'
    ]);
    exit();
}

if($producto['stock'] < $cantidad) {
    echo json_encode([
        'success' => false,
        'message' => 'Stock insuficiente. Solo hay ' . $producto['stock'] . ' disponibles'
    ]);
    exit();
}

if(!$producto['activo']) {
    echo json_encode([
        'success' => false,
        'message' => 'El producto no está disponible'
    ]);
    exit();
}

$appCarrito = new Carrito();
$resultado = $appCarrito->agregar($usuario_id, $producto_id, $cantidad);

if($resultado > 0) {
    $_SESSION['cart_count'] = $appCarrito->contarItems($usuario_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito',
        'cart_count' => $_SESSION['cart_count']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar el producto al carrito'
    ]);
}
?>