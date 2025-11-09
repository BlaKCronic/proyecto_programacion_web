<?php
require_once "../config/config.php";
require_once "../config/paypal.php";
require_once "../models/carrito.php";
require_once "../models/pedido.php";
require_once "../models/producto.php";

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

if(!estaLogueado()) {
    redirect('../login.php');
}

$paymentId = $_GET['paymentId'] ?? null;
$payerId = $_GET['PayerID'] ?? null;

if(!$paymentId || !$payerId) {
    $_SESSION['error_message'] = 'Información de pago incompleta';
    redirect('../checkout.php?step=2');
}

try {
    $apiContext = getPayPalApiContext();
    
    $payment = Payment::get($paymentId, $apiContext);
    
    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);
    
    $result = $payment->execute($execution, $apiContext);
    
    if($result->getState() == 'approved') {
        $usuario_id = $_SESSION['usuario_id'];
        
        $appCarrito = new Carrito();
        $appPedido = new Pedido();
        $appProducto = new Producto();
        
        $items_carrito = $appCarrito->obtenerCarrito($usuario_id);
        
        if(empty($items_carrito)) {
            throw new Exception('El carrito está vacío');
        }
        
        $subtotal = 0;
        foreach($items_carrito as $item) {
            $precio = $item['precio_descuento'] ?? $item['precio'];
            $subtotal += $precio * $item['cantidad'];
        }
        
        $envio = $subtotal >= ENVIO_GRATIS_DESDE ? 0 : COSTO_ENVIO_BASE;
        $impuestos = $subtotal * IVA;
        $total = $subtotal + $envio + $impuestos;
        
        $direccion_completa = $_SESSION['checkout_direccion']['direccion'] . ', ' .
                            $_SESSION['checkout_direccion']['ciudad'] . ', ' .
                            $_SESSION['checkout_direccion']['estado'] . ' ' .
                            $_SESSION['checkout_direccion']['codigo_postal'];
        
        $data_pedido = [
            'id_usuario' => $usuario_id,
            'total' => $total,
            'subtotal' => $subtotal,
            'envio' => $envio,
            'impuestos' => $impuestos,
            'direccion_envio' => $direccion_completa,
            'metodo_pago' => 'PayPal'
        ];
        
        $id_pedido = $appPedido->create($data_pedido);
        
        if(!$id_pedido) {
            throw new Exception('Error al crear el pedido');
        }
        
        foreach($items_carrito as $item) {
            $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
            $subtotal_item = $precio_unitario * $item['cantidad'];
            
            $data_detalle = [
                'id_pedido' => $id_pedido,
                'id_producto' => $item['id_producto'],
                'id_vendedor' => $item['id_vendedor'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $precio_unitario,
                'subtotal' => $subtotal_item
            ];
            
            $appPedido->agregarDetalle($data_detalle);
            
            $appProducto->updateStock($item['id_producto'], $item['cantidad']);
        }

        $transaction = $result->getTransactions()[0];
        $paypal_transaction_id = $transaction->getRelatedResources()[0]->getSale()->getId();
        
        error_log("Pago exitoso de PayPal - Pedido: $id_pedido, Transaction: $paypal_transaction_id");
        
        $appCarrito->vaciarCarrito($usuario_id);
        $_SESSION['cart_count'] = 0;
        
        unset($_SESSION['checkout_direccion']);
        unset($_SESSION['checkout_pago']);
        unset($_SESSION['paypal_payment_id']);
        unset($_SESSION['paypal_total_mxn']);
        
        $_SESSION['ultimo_pedido'] = $id_pedido;
        $_SESSION['success_message'] = 'Pago procesado exitosamente con PayPal';
        
        redirect('../checkout.php?step=4');
        
    } else {
        throw new Exception('El pago no fue aprobado');
    }
    
} catch(PayPal\Exception\PayPalConnectionException $ex) {
    error_log('PayPal Error: ' . $ex->getData());
    $_SESSION['error_message'] = 'Error al procesar el pago con PayPal';
    redirect('../checkout.php?step=2&paypal=error');
} catch(Exception $ex) {
    error_log('Error: ' . $ex->getMessage());
    $_SESSION['error_message'] = 'Error al completar la compra: ' . $ex->getMessage();
    redirect('../checkout.php?step=2&paypal=error');
}