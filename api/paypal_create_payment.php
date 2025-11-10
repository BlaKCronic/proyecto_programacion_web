<?php
require_once "../config/config.php";
require_once "../config/paypal.php";
require_once "../models/carrito.php";

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

header('Content-Type: application/json');

if(!estaLogueado()) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión'
    ]);
    exit();
}

if(!isset($_SESSION['checkout_direccion'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Completa la información de envío primero'
    ]);
    exit();
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    $appCarrito = new Carrito();
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

    $subtotal_usd = convertirMXNaUSD($subtotal);
    $envio_usd = convertirMXNaUSD($envio);
    $impuestos_usd = convertirMXNaUSD($impuestos);
    $total_usd = convertirMXNaUSD($total);

    $apiContext = getPayPalApiContext();

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $itemList = new ItemList();
    $paypal_items = [];

    foreach($items_carrito as $item) {
        $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
        $precio_usd = convertirMXNaUSD($precio_unitario);
        
        $paypal_item = new Item();
        $paypal_item->setName(substr($item['nombre'], 0, 127))
            ->setCurrency('USD')
            ->setQuantity($item['cantidad'])
            ->setSku($item['id_producto'])
            ->setPrice(formatearPrecioPayPal($precio_usd));
        
        $paypal_items[] = $paypal_item;
    }

    $itemList->setItems($paypal_items);

    $shippingAddress = new \PayPal\Api\ShippingAddress();
    $shippingAddress->setLine1($_SESSION['checkout_direccion']['direccion'])
        ->setCity($_SESSION['checkout_direccion']['ciudad'])
        ->setState($_SESSION['checkout_direccion']['estado'])
        ->setPostalCode($_SESSION['checkout_direccion']['codigo_postal'])
        ->setCountryCode('MX');
    
    $itemList->setShippingAddress($shippingAddress);

    $details = new Details();
    $details->setShipping(formatearPrecioPayPal($envio_usd))
        ->setTax(formatearPrecioPayPal($impuestos_usd))
        ->setSubtotal(formatearPrecioPayPal($subtotal_usd));

    $amount = new Amount();
    $amount->setCurrency('USD')
        ->setTotal(formatearPrecioPayPal($total_usd))
        ->setDetails($details);

    $transaction = new Transaction();
    $transaction->setAmount($amount)
        ->setItemList($itemList)
        ->setDescription('Compra en Amazon Lite')
        ->setInvoiceNumber(uniqid('ALT-'));

    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl(PAYPAL_RETURN_URL)
        ->setCancelUrl(PAYPAL_CANCEL_URL);

    $payment = new Payment();
    $payment->setIntent('sale')
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions([$transaction]);

    $payment->create($apiContext);

    $_SESSION['paypal_payment_id'] = $payment->getId();
    $_SESSION['paypal_total_mxn'] = $total;

    $approvalUrl = $payment->getApprovalLink();

    echo json_encode([
        'success' => true,
        'approval_url' => $approvalUrl
    ]);

} catch(PayPal\Exception\PayPalConnectionException $ex) {
    error_log('PayPal Connection Error: ' . $ex->getData());
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con PayPal: ' . $ex->getMessage()
    ]);
} catch(Exception $ex) {
    error_log('PayPal Error: ' . $ex->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el pago: ' . $ex->getMessage()
    ]);
}