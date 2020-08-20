<?php

function define_exp_constants() {
    if (getenv('REDIRECT_EXP'))  define('CURR_EXP', getenv('REDIRECT_EXP'));
    if (getenv('REDIRECT_PAGE')) define('PAGE',     getenv('REDIRECT_PAGE'));
}

function initialize_page() {
    if (!is_file(get_requested_page_path())) give_404();
    
    define('URL_TO_ROOT', '../..');
    start_session();
    start_main_output_buffer();
    require get_requested_page_path();
}

function start_main_output_buffer() {
    // require_into_string uses ob_get_clean,
    // which cant be called inside ob_start callback
    $wrapper = require_into_string(dirname(__DIR__) . '/html-wrapper.php');
    // if too much memory is used (like when an infinite loop is started)
    // the output buffer can't have the wrapper html added
    // so, try to see how much memory is available before adding
    // this way, we might see the actual error message, rather than
    // just a white screen
    $memory_limit = ini_get('memory_limit');
    
    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
        if ($matches[2] == 'M') {
            $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
        } else if ($matches[2] == 'K') {
            $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
        }
    }
    
    ob_start(function($buffer) use ($wrapper, $memory_limit) {
        if ($memory_limit - memory_get_usage() < 5000) return $buffer;
        
        return str_replace(
            ['%action%', '%content%'],
            [defined('ACTION') ? ACTION : '', $buffer],
            $wrapper
        );
    });
}

function require_into_string($filename) {
    ob_start();
    require $filename;
    return ob_get_clean();
}

function give_404() {
    header('HTTP/1.0 404 Not Found');
    echo 'Cannot find page: ' . PAGE;
    exit;
}

function start_session() {
    $sess_dir = get_session_save_path();
    
    if (!is_dir($sess_dir)) mkdir($sess_dir, 0777, true);
    
    session_save_path($sess_dir);
    session_start();
    
    if (is_attempting_to_logout()) {
        wipe_session_clean();
        redirect('login');
    }
    
    attempt_session_restore();
    register_session_shutdown_function();
}

function is_attempting_to_logout() {
    return filter_has_var(INPUT_POST, 'collector_logout');
}

function wipe_session_clean() {
    $_SESSION = [];
}

function get_session_save_path() {
    return defined('CURR_EXP')
         ? get_exp_data_dir() . '/sess'
         : session_save_path();
}

function attempt_session_restore() {
    if (isset($_SESSION['Username']) and defined('CURR_EXP')) {
        if (has_session($_SESSION['Username'])) {
            $_SESSION = get_session($_SESSION['Username']);
        }
    }
}

function register_session_shutdown_function() {
    register_shutdown_function(function() {
        if (isset($_SESSION['Username']) and defined('CURR_EXP')) {
            save_session($_SESSION);
            $_SESSION = ['Username' => $_SESSION['Username']];
        }
    });
}

function prepare_exception_handler() {
    set_exception_handler('exception_handler');
}

function exception_handler($e) {
    $give_stack_trace = false;
    
    if (get_class($e) === 'Exception') {
        if (defined('PAGE') and PAGE === 'experiment') {
            return handle_trial_error($e);
        }
        
        $msg = $give_stack_trace ? $e : $e->getMessage();
        $msg = htmlspecialchars($msg);
        echo "<div class='dump'><pre>{$msg}</pre></div>";
    } else {
        echo "<div class='dump'><pre>{$e}</pre></div>";
    }
}