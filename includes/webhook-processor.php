<?php
if (!defined('ABSPATH')) {
    exit; // Вихід, якщо доступ безпосередньо
}

// Запис повідомлення про помилку
function wfp_log_error($message) {
    error_log($message . PHP_EOL, 3, WFP_LOG_FILE);
}

// Функція для запису POST даних у файл
function wfp_log_post_data($post_data) {
    file_put_contents(POST_DATA_FILE, "POST data: " . print_r($post_data, true) . PHP_EOL, FILE_APPEND);
}

// Обробка POST запитів
function wfp_handle_post_request($post_data) {
        wfp_signup_user($post_data); // Виклик функції для реєстрації користувача
}

function wfp_handle_test_request($post_data) {
        echo 'POST Webhook received successfully' . PHP_EOL;
        echo 'Received Data: ' . PHP_EOL;
        print_r($post_data);
}

// Основна функція для обробки запитів
function wfp_webhook_handler () {
    if (isset($_GET[WEBHOOK_NAME])) {
        date_default_timezone_set('Europe/Kyiv');
        file_put_contents(POST_DATA_FILE, "Current date: " . date('d-m-Y H:i:s') . PHP_EOL, FILE_APPEND);
        $data = file_get_contents('php://input');
        $post_data = json_decode($data, true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (json_last_error() === JSON_ERROR_NONE) {
                wfp_log_post_data($post_data);
                switch ($_GET[WEBHOOK_NAME]) {
                    case 'wfp-signup-webhook':
                        wfp_handle_post_request($post_data);
                        break;
                    
                    case 'wfp-success-payment-webhook':
                        
                        break;
                    
                    case 'wfp-failure-payment-webhook':
                        
                        break;
                    
                    case 'wfp-test-data-webhook':
                        wfp_handle_test_request($post_data);
                        break;
    
                    default:
                        echo 'Невідомий webhook';
                        break;
                }
            } else {
                echo PHP_EOL . 'Error: Invalid JSON format';
                wfp_log_error("Помилка декодування JSON у тестовому запиті");
            }
        exit;
        }
    }
}