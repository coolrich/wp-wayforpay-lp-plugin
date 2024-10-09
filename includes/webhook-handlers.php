<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'LP_REST_Admin_Tools_Controller' ) ) {
			include_once LP_PLUGIN_PATH . 'inc/rest-api/v1/admin/class-lp-admin-rest-tools-controller.php';
		}

// Функція для реєстрації або авторизації користувача
function wfp_signup_user_handler() {
    $data = $_POST;
    if (isset($data['orderReference']) 
        && isset($data['email']) 
        && isset($data['merchantSignature']) ) {
        
        $received_signature = $data['merchantSignature'];
        $order_reference = $data['orderReference'];
        $secret_key = "0123456789"; // Замініть на свій ключ
        $generated_signature = generate_wayforpay_signature($data, $secret_key);

        if (!hash_equals($received_signature, $generated_signature)) {
            error_log("Signatures do not match", 3, WFP_LOG_FILE);
            exit;
        }

        $userEmail = sanitize_email($data['email']);
        if (!is_email($userEmail)) {
            error_log("Неправильний формат email", 3, WFP_LOG_FILE);
            return;
        }

        $user = get_user_by('email', $userEmail);
        if (!$user) {
            $password = wp_generate_password();
            $user_id = wp_create_user($userEmail, $password, $userEmail);
            if (is_wp_error($user_id)) {
                error_log("Помилка створення користувача: " . $user_id->get_error_message(), 3, WFP_LOG_FILE);
                return;
            }
            wp_update_user(array('ID' => $user_id, 'role' => 'student'));
        } else {
            $user_id = $user->ID;
            $password = '';
        }

        // Логування даних
        file_put_contents(
            POST_DATA_FILE,
            "User id: " . $user_id . "\n" .  
            "User email: " . $userEmail . "\n" .
            "Order reference: " . $order_reference . "\n" .
            "User password: " . $password . "\n\n",  
            FILE_APPEND 
        );

        $creds = array(
            'user_login'    => $userEmail,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            error_log("Помилка аутентифікації користувача: " . $user->get_error_message(), 3, WFP_LOG_FILE);
            return;
        }
        // Призначаємо курс користувачу
        $controller = new LP_REST_Admin_Tools_Controller();
        $request = new WP_REST_Request();
        $request->set_param('data', 
            array(['user_id' => (int)$user_id, 
                   'course_id' => (int)$data['orderReference']]));
        $response = $controller->assign_courses_to_users($request);
        print_r($response);
    }
}


function wfp_successful_payment_handler(){
    //echo "Success payment handler: <br>" . PHP_EOL;
    $data = $_POST;
    // Перевіряємо, чи прийшли POST-дані
    if (isset($data['login'])) {
        // Якщо кнопка була натиснута, пробуємо авторизувати користувача
        $username = sanitize_text_field($data['username']);
        $password = sanitize_text_field($data['password']);
        
        // Виконуємо авторизацію на WordPress
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        );
        
        $user = wp_signon($creds, false);
    
        if (is_wp_error($user)) {
            // Якщо авторизація невдала, виводимо повідомлення про помилку
            echo '<p>Невдалий логін. Перевірте дані та спробуйте ще раз.</p>';
        } else {
            // Якщо авторизація успішна, перенаправляємо на сторінку курсів
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_redirect(home_url('/lp-profile')); // Зміна URL на вашу сторінку курсів
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['username'], $data['password'])) {
        // Зберігаємо отримані POST-дані
        $username = sanitize_text_field($data['username']); // Логін
        $password = sanitize_text_field($data['password']); // Пароль
        
        // Відображаємо сторінку з кнопкою
        ?>
        <!DOCTYPE html>
        <html lang="uk">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Перейти до курсу</title>
            <style>
                body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background-color: #f4f4f4;
                }
                .button-container {
                    text-align: center;
                }
                button {
                    padding: 10px 20px;
                    font-size: 18px;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="button-container">
                <form action="" method="POST">
                    <input type="hidden" name="username" value="<?php echo esc_attr($username); ?>">
                    <input type="hidden" name="password" value="<?php echo esc_attr($password); ?>">
                    <button type="submit" name="login">Перейти до профілю</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        // Якщо дані не були отримані, відображаємо повідомлення
        echo '<p>Немає POST-даних для обробки.</p>';
    }
}

function wfp_failure_payment_handler(){
    echo "Failure payment handler";
    $data = $_POST;
    function wfp_failure_payment_handler() {
    // Перевіряємо, чи були передані дані POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['repayUrl'])) {
        // Зберігаємо отримані POST-дані
        $repayUrl = sanitize_text_field($data['repayUrl']); // URL для повторної оплати
        
        // Відображаємо сторінку з повідомленням про невдалу оплату та кнопкою для повторення оплати
        ?>
        <!DOCTYPE html>
        <html lang="uk">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Невдала оплата</title>
            <style>
                body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background-color: #f4f4f4;
                }
                .button-container {
                    text-align: center;
                }
                button {
                    padding: 10px 20px;
                    font-size: 18px;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="button-container">
                <h2>Оплата не пройшла успішно. Будь ласка, повторіть спробу.</h2>
                <form action="<?php echo esc_url($repayUrl); ?>" method="GET">
                    <button type="submit">Повторити оплату</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        // Якщо дані не були отримані, виводимо повідомлення
        echo '<p>Немає POST-даних для обробки.</p>';
    }
}

}