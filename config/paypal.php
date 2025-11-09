<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

define('PAYPAL_CLIENT_ID', 'AeqzmhP781gYI5kwIHdGMoChEUV3h2O7YYa9MXaPQ8ujemthO4N4R9opKU1xTPSSiJw5TB6aTbE_QHjh');
define('PAYPAL_CLIENT_SECRET', 'EMkZgHHVMgcSwew1h_0HVWSvJtvX-8cNuONqgqWOFfT_JT62-InqI_QOrRchTFi5B9yK4dsOOYu_oudl');

define('PAYPAL_MODE', 'sandbox');

function getPayPalApiContext() {
    $apiContext = new ApiContext(
        new OAuthTokenCredential(
            PAYPAL_CLIENT_ID,
            PAYPAL_CLIENT_SECRET
        )
    );

    $apiContext->setConfig([
        'mode' => PAYPAL_MODE,
        'log.LogEnabled' => true,
        'log.FileName' => __DIR__ . '/../logs/paypal.log',
        'log.LogLevel' => 'INFO',
        'cache.enabled' => true,
        'cache.FileName' => __DIR__ . '/../cache/paypal-cache'
    ]);

    return $apiContext;
}

define('PAYPAL_RETURN_URL', BASE_URL . '/api/paypal_success.php');
define('PAYPAL_CANCEL_URL', BASE_URL . '/checkout.php?step=2&paypal=cancel');

function convertirMXNaUSD($monto_mxn) {
    $tasa_cambio = 17.5;
    return round($monto_mxn / $tasa_cambio, 2);
}

function formatearPrecioPayPal($precio) {
    return number_format($precio, 2, '.', '');
}