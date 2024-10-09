<?php
if (!defined('ABSPATH')) {
    exit; // Вихід, якщо доступ безпосередньо
}

// Запис повідомлення про помилку
function wfp_log_error($message) {
    error_log($message . PHP_EOL, 3, WFP_LOG_FILE);
}

// Функція для запису POST даних у файл
function wfp_log_post_data() {
    file_put_contents(POST_DATA_FILE, "POST data: " . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
}

function wfp_test_request_handler() {
        echo 'POST Webhook test request received successfully' . PHP_EOL;
        print_r($_POST);
}

// Основна функція для обробки запитів
function wfp_webhook_handler () {
    // Додати перевірку на джерело, з якого отримується інформація
    if (isset($_GET[WEBHOOK_NAME])) {
        date_default_timezone_set('Europe/Kyiv');
        file_put_contents(POST_DATA_FILE, "Current date: " . date('d-m-Y H:i:s') . PHP_EOL, FILE_APPEND);
        // $data = file_get_contents('php://input');
        // $decoded_post_data = json_decode($data, true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                wfp_log_post_data();
                switch ($_GET[WEBHOOK_NAME]) {
                    case 'wfp-signup-webhook':
                        wfp_signup_user_handler();
                        break;
                    
                    case 'wfp-success-payment-webhook':
                        wfp_successful_payment_handler();
                        break;
                    
                    case 'wfp-failure-payment-webhook':
                        wfp_failure_payment_handler();
                        break;
                    
                    case 'wfp-test-data-webhook':
                        wfp_test_request_handler();
                        break;
    
                    default:
                        echo 'Невідомий webhook';
                        break;
                }
        exit;
        }
    }
}