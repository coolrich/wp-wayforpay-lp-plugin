<?php

function generate_order_reference($time) {
    return 'DH' . $time/* . '-' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8)*/;
}

function wfp_buy_course(){
    // Дані для платежу
    if(isset($_GET[WEBHOOK_NAME])
        && $_GET[WEBHOOK_NAME] === "selfmaster"){
        $merchantAccount = "test_merch_n1";
        $merchantDomainName = "www.market.ua";
        $c_time = time() + 15;
        $orderReference = generate_order_reference($c_time);
        $orderDate = $c_time; // поточний час у форматі UNIX timestamp
        $amount = "1";
        $currency = "UAH";
        $productName = ["Онлайн-курс “Сам собі майстер манікюру” (Пакет Online)"];
        $productCount = ["1"];
        $productPrice = ["1"];
        $returnUrl = "https://www.puustovit.com/lp-profile/";
        $serviceUrl = "https://www.puustovit.com/?" . $_GET[WEBHOOK_NAME] . "=wfp-test-data-webhook";
        
        // Секретний ключ для підпису
        $secretKey = "flk3409refn54t54t*FNJRET"; // Замініть на ваш секретний ключ
        
        // Формування підпису
        $signatureParams = [
            $merchantAccount,
            $merchantDomainName,
            $orderReference,
            $orderDate,
            $amount,
            $currency,
            implode(';', $productName),
            implode(';', $productCount),
            implode(';', $productPrice)
        ];
        $dataToSign = implode(';', $signatureParams);
        $dataToSignUtf8 = mb_convert_encoding($dataToSign, 'UTF-8');
        // Генерація підпису через хешування MD5
        $merchantSignature = hash_hmac("md5", $dataToSignUtf8, $secretKey);
        
        // Дані для відправки POST-запиту
        $postData = [
            'merchantAccount' => $merchantAccount,
            'merchantDomainName' => $merchantDomainName,
            'orderReference' => $orderReference,
            'orderDate' => $orderDate,
            'amount' => $amount,
            'currency' => $currency,
            'productName' => $productName,
            'productPrice' => $productPrice,
            'productCount' => $productCount,
            'merchantSignature' => $merchantSignature,
            'returnUrl' => $returnUrl,
            'serviceUrl' => $serviceUrl
        ];
        
        // Відправка POST-запиту за допомогою cURL
        $ch = curl_init('https://secure.wayforpay.com/pay');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        
        // Виконання запиту та отримання відповіді
        $response = curl_exec($ch);
        
        // Перевірка на помилки
        if ($response === false) {
            echo 'Помилка при виконанні запиту: ' . curl_error($ch);
        } else {
            // Відображення відповіді (для тестування)
            echo 'Відповідь WayForPay: ' . $response;
            file_put_contents(POST_DATA_FILE, "Response from WFP: " . print_r($response, true) . PHP_EOL, FILE_APPEND);
        }
        
        // Закриття сесії cURL
        curl_close($ch);
        exit;
    }
}
?>