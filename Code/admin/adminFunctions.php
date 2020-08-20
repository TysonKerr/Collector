<?php
namespace admin;

function require_admin_status() {
    if (!isset($_SESSION)) {
        throw new Exception('Cannot require admin status without session');
    }
    
    if (!password_is_set()) {
        if (filter_has_var(INPUT_POST, 'col_adm_new_pas')) {
            create_password(filter_input(INPUT_POST, 'col_adm_new_pas'));
            $_SESSION['admin'] = time();
            refresh_self();
        } else {
            ask_for_new_password();
        }
    }
    
    if (filter_has_var(INPUT_POST, 'col_adm_logout')) {
        unset($_SESSION['admin']);
    }
    
    if (password_has_been_submitted()) {
        if (password_is_correct(filter_input(INPUT_POST, 'col_adm_pas'))) {
            $_SESSION['admin'] = time();
            refresh_self();
        }
    }
    
    if (isset($_SESSION['admin'])) {
        if ((time() - $_SESSION['admin']) > 60 * 20) {
            unset($_SESSION['admin']);
        }
    }
    
    if (!isset($_SESSION['admin'])) {
        ask_for_password();
    }
}

function password_is_set() {
    return is_file(get_password_filename());
}

function get_password_filename() {
    return dirname(dirname(__DIR__)) . '/Experiment/p.txt';
}

function get_password_hash() {
    return file_get_contents(get_password_filename());
}

function ask_for_new_password() {
    require __DIR__ . '/adminPasswordCreation.php';
    exit;
}

function create_password($new_password) {
    $filename = get_password_filename();
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    file_put_contents($filename, $hash);
}

function password_is_correct($submitted) {
    return password_verify($submitted, get_password_hash());
}

function url_to_root() {
    $cd = getcwd();
    $url = '.';
    $root = dirname(dirname(__DIR__));
    $count = 0;
    
    while ($cd !== $root and $count < 10) {
        $cd = dirname($cd);
        $url .= '/..';
        ++$count;
    }
    
    return $url;
}

function password_has_been_submitted() {
    return filter_has_var(INPUT_POST, 'col_adm_pas');
}

function ask_for_password() {
    require __DIR__ . '/adminLogin.php';
    exit;
}

function get_login_error_class() {
    return password_has_been_submitted() ? 'error' : 'invis';
}

function refresh_self() {
    $page = get_server_var($name);
    header("Location: $page");
    exit;
}

function get_server_var($name) {
    if (filter_has_var(INPUT_SERVER, $name)) {
        return filter_input(INPUT_SERVER, $name);
    } else if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    } else {
        return null;
    }
}
