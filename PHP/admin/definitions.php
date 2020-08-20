<?php
namespace admin;

function start_session() {
    $dir = __DIR__ . '/sess';
    
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    session_save_path($dir);
    session_start();
    
    check_if_logging_out();
    ensure_user_has_logged_in();
}

function check_if_logging_out() {
    if (filter_has_var(INPUT_POST, 'logout')) {
        set_logged_in_status(false);
    }
}

function ensure_user_has_logged_in() {
    ensure_password_has_been_set();
    
    if (!is_logged_in()) {
        if (has_submitted_login_data()) {
            login();
        } else {
            give_login_page();
        }
    }
}

function ensure_password_has_been_set() {
    if (get_password() === false) {
        if (filter_has_var(INPUT_POST, 'np')) {
            set_password(filter_input(INPUT_POST, 'np'));
            set_logged_in_status(true);
            refresh_page();
        } else {
            give_password_creation_page();
        }
    }
}

function get_password() {
    $filename = __DIR__ . '/pass.txt';
    return is_file($filename) ? file_get_contents($filename) : false;
}

function set_password($password) {
    $hash = password_hash($password,  PASSWORD_DEFAULT);
    file_put_contents(__DIR__ . '/pass.txt', $hash);
}

function give_password_creation_page() {
    give_page('get-new-password');
}

function give_page($page, $page_settings = []) {
    start_ob();
    require __DIR__ . "/pages/$page.php";
    exit;
}

function start_ob() {
    $wrapper = require_into_string(__DIR__ . '/pages/html-wrapper.php');
    
    ob_start(function($buffer) use ($wrapper) {
        return str_replace('%content%', $buffer, $wrapper);
    });
}

function refresh_page() {
    header('Location: .');
    exit;
}

function is_logged_in() {
    // must be set and truthy
    return isset($_SESSION['logged in']) and $_SESSION['logged in'];
}

function set_logged_in_status($is_logged_in = true) {
    if ($is_logged_in) {
        $_SESSION['logged in'] = true;
    } else {
        unset($_SESSION['logged in']);
    }
}

function has_submitted_login_data() {
    return filter_has_var(INPUT_POST, 's');
}

function login() {
    $password = get_password();
    $submitted = filter_input(INPUT_POST, 's');
    $is_correct = password_verify($submitted, $password);
    
    if ($is_correct) {
        set_logged_in_status(true);
        refresh_page();
    } else {
        give_login_page(true);
    }
}

function give_login_page($submitted_wrong_password = false) {
    give_page('login', ['wrong pass' => $submitted_wrong_password]);
}
