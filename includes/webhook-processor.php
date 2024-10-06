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
    if (json_last_error() === JSON_ERROR_NONE) {
        wfp_log_post_data($post_data);
        wfp_signup_user($post_data); // Виклик функції для реєстрації користувача
    } else {
        echo 'Помилка: Некоректний формат JSON';
        wfp_log_error("Помилка декодування JSON");
    }
}

// Основна функція для обробки запитів
function wfp_processor() {
    if (isset($_GET[WEBHOOK_NAME])) {
        date_default_timezone_set('Europe/Kyiv');
        file_put_contents(POST_DATA_FILE, "Current date: " . date('d-m-Y H:i:s') . PHP_EOL, FILE_APPEND);

        $data = file_get_contents('php://input');
        $post_data = json_decode($data, true);

        // Обробка WayForPay запиту
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            switch ($_GET[WEBHOOK_NAME]) {
                case 'wfp-signup-user':
                    wfp_handle_post_request($post_data);
                    break;
                
                case 'wfp-failure-request-processor':
                    
                    break;
                
                case 'wfp-failure-request-processor':
                    
                    break;
                
                case 'test':
                    wfp_handle_test_request($post_data);
                    break;

                default:
                    echo 'Невідомий webhook';
                    break;
            }
            exit;
        }
    }
}

// Функція для обробки тестового запиту
function wfp_handle_test_request($post_data) {
    if (json_last_error() === JSON_ERROR_NONE) {
        echo 'POST Webhook received successfully' . PHP_EOL;
        echo 'Received Data: ' . PHP_EOL;
        print_r($post_data);
        wfp_log_post_data($post_data);
    } else {
        echo PHP_EOL . 'Error: Invalid JSON format';
        wfp_log_error("Помилка декодування JSON у тестовому запиті");
    }
}

add_action('init', 'wfp_processor');
