<?php

function check_if_has_more_experiment_to_do() {
    if (isset($_SESSION['Procedure'][$_SESSION['Position']])) {
        redirect('experiment');
    }
}

function record_ending_reached() {
    if (!isset($_SESSION['Metadata']['Experiment Duration'])) {
        $time = time();
        $duration = $time - $_SESSION['Experiment Start Timestamp'];
        
        record_metadata([
            'Experiment End Timestamp' => $time,
            'Experiment Duration' => $duration
        ]);
    }
}

function has_next_experiment() {
    return (bool) get_config('next_experiment');
}

function redirect_to_next_experiment() {
    $url = get_next_url();
    wipe_session_clean(); // log them out, in case they are on a public computer
    header("Location: $url");
    exit;
}

function get_next_url() {
    $next = get_config('next_experiment');
    $next .= strpos($next, '?') === false ? '?' : '&';
    $next .= 'u=' . urlencode($_SESSION['Username']);
    return $next;
}

function get_verification_code() {
    if (!get_config('show_verification')) return '';
    
    return get_config('verification_code') . '-' . $_SESSION['ID'];
}
