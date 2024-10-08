<?php
/*
Plugin Name: WayForPay Plugin
Description: Плагін для інтеграції WayForPay
*/

// Захист від прямого доступу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Визначаємо шляхи до файлів плагіну
define('WFP_LOG_FILE', __DIR__ . '/error_log.txt');
define('POST_DATA_FILE', __DIR__ . '/webhook_post_data.txt');
define('WEBHOOK_NAME', 'wfp_hook');

// Підключаємо інші файли плагіну
require_once plugin_dir_path( __FILE__ ) . 'includes/webhook-processor.php'; // Обробка вебхуків
require_once plugin_dir_path( __FILE__ ) . 'includes/user-functions.php'; // Робота з користувачами
require_once plugin_dir_path( __FILE__ ) . 'includes/helper-functions.php'; // Допоміжні функції

// Створюємо лог файл, якщо його немає
if (!file_exists(WFP_LOG_FILE)) {
    if (!touch(WFP_LOG_FILE)) {
        error_log("Не вдалося створити файл логів", 3, '/path/to/error.log');
    }
}

// Ініціалізуємо процесор вебхуків
add_action('init', 'wfp_webhook_handler');
