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

    $subtotal_usd = 0.0;
    $envio_usd = convertirMXNaUSD($envio);
    $impuestos_usd = 0.0;
    $total_usd = 0.0;

    $apiContext = getPayPalApiContext();

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $itemList = new ItemList();
    $paypal_items = [];

    foreach($items_carrito as $item) {
        $precio_unitario = $item['precio_descuento'] ?? $item['precio'];
        $precio_usd = convertirMXNaUSD($precio_unitario);
        $precio_usd_formatted = floatval(formatearPrecioPayPal($precio_usd));

        $line_total = round($precio_usd_formatted * $item['cantidad'], 2);
        $subtotal_usd += $line_total;

        $paypal_item = new Item();
        $paypal_item->setName(substr($item['nombre'], 0, 127))
            ->setCurrency('USD')
            ->setQuantity($item['cantidad'])
            ->setSku($item['id_producto'])
            ->setPrice(formatearPrecioPayPal($precio_usd_formatted));

        $paypal_items[] = $paypal_item;
    }

    $itemList->setItems($paypal_items);

    $impuestos_usd = round($subtotal_usd * IVA, 2);
    $envio_usd = floatval(formatearPrecioPayPal($envio_usd));
    $total_usd = round($subtotal_usd + $impuestos_usd + $envio_usd, 2);

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

    $details_subtotal = floatval(formatearPrecioPayPal($subtotal_usd));
    $details_tax = floatval(formatearPrecioPayPal($impuestos_usd));
    $details_shipping = floatval(formatearPrecioPayPal($envio_usd));
    $details_total = floatval(formatearPrecioPayPal($total_usd));

    $items_sum = round($subtotal_usd, 2);

    $diff_subtotal = abs($items_sum - $details_subtotal);
    $diff_total = abs(($details_subtotal + $details_tax + $details_shipping) - $details_total);

    $THROW_THRESHOLD = 1.00;
    $AUTOCORRECT_THRESHOLD = 0.01;

    if($diff_subtotal > $THROW_THRESHOLD || $diff_total > $THROW_THRESHOLD) {
        $logPayload = [
            'items_sum' => $items_sum,
            'details' => [
                'subtotal' => $details_subtotal,
                'tax' => $details_tax,
                'shipping' => $details_shipping,
                'total' => $details_total
            ],
            'items' => array_map(function($it){
                return [
                    'name' => $it->getName(),
                    'qty' => $it->getQuantity(),
                    'price' => $it->getPrice()
                ];
            }, $paypal_items)
        ];

        $logLine = date('d-m-Y H:i:s') . " PayPal Validation Error: monto inconsistente - " . json_encode($logPayload) . "\n";
        @file_put_contents(__DIR__ . '/../logs/paypal_validation.log', $logLine, FILE_APPEND);
        error_log('PayPal validation mismatch: ' . json_encode($logPayload));
        throw new Exception('Inconsistencia en montos para PayPal. Revisa el log de validación.');
    } elseif($diff_subtotal > $AUTOCORRECT_THRESHOLD || $diff_total > $AUTOCORRECT_THRESHOLD) {
        $before = [
            'details' => [
                'subtotal' => $details_subtotal,
                'tax' => $details_tax,
                'shipping' => $details_shipping,
                'total' => $details_total
            ]
        ];

        $details->setSubtotal(formatearPrecioPayPal($items_sum));
        $details->setTax(formatearPrecioPayPal($impuestos_usd));
        $details->setShipping(formatearPrecioPayPal($envio_usd));
        $amount->setDetails($details);
        $amount->setTotal(formatearPrecioPayPal($total_usd));

        $after = [
            'details' => [
                'subtotal' => floatval(formatearPrecioPayPal($items_sum)),
                'tax' => floatval(formatearPrecioPayPal($impuestos_usd)),
                'shipping' => floatval(formatearPrecioPayPal($envio_usd)),
                'total' => floatval(formatearPrecioPayPal($total_usd))
            ]
        ];

        $logLine = date('d-m-Y H:i:s') . " PayPal Validation Auto-correct: antes=" . json_encode($before) . " despues=" . json_encode($after) . "\n";
        @file_put_contents(__DIR__ . '/../logs/paypal_validation.log', $logLine, FILE_APPEND);
        error_log('PayPal validation auto-correct applied: ' . json_encode($after));
    }

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