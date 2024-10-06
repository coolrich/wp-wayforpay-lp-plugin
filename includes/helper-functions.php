<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Функція для генерації підпису WayForPay
function generate_wayforpay_signature($data, $secret_key) {
    $signature_string = implode(';', array(
        $data['merchantAccount'],
        $data['orderReference'],
        $data['amount'],
        $data['currency'],
        $data['authCode'],
        $data['cardPan'],
        $data['transactionStatus'],
        $data['reasonCode']
    ));

    return hash_hmac('md5', $signature_string, $secret_key);
}
