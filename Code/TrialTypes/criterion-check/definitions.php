<?php

function add_trials($trials) {
    array_splice($_SESSION['Trials'], $_SESSION['Position'] + 1, 0, $trials);
};

function get_trial($index) {
    return $_SESSION['Trials'][$index];
}

function get_settings($settings) {
    $settings = json_decode('{' . $settings . '}', true);
    
    if (!$settings) $settings = [];
    
    $default_settings = get_default_settings();
    
    return array_merge($default_settings, $settings);
}

function get_default_settings() {
    $path = __DIR__ . '/settings.json';
    
    return is_file($path)
         ? json_decode(file_get_contents($path), true)
         : [];
}

function get_previous_trials($count) {
    $trials = [];

    for ($i = $count; $i > 0; --$i) {
        $trial_index = $_SESSION['Position'] - $i;
        $trials[] = get_trial($trial_index);
    }
    
    return $trials;
}

function get_trial_values($trials, $category, $key) {
    if ($category !== 'Stimuli'
        and $category !== 'Procedure'
        and $category !== 'Response'
    ) {
        trigger_error('$category argument improperly defined, should be '
            . '"Stimuli", "Procedure", or "Response".', E_USER_ERROR);
    }
    
    $vals = [];
    
    foreach ($trials as $trial) {
        if (!is_array($trial)) continue;
        
        $vals[] = (!isset($trial[$category]) or !isset($trial[$category][$key]))
                ? ''
                : $trial[$category][$key];
    }
    
    return $vals;
}

function redo_trials($trials, $settings) {
    if ($settings['reshuffle']) $trials = shuffle_trials($trials);
    
    $trials[] = get_current_trial();
    $trials = array_map('get_trial_with_cleared_responses', $trials);
    add_trials($trials);
}

function get_trial_with_cleared_responses($trial) {
    foreach ($trial['Response'] as $key => $val) {
        $trial['Response'][$key] = '';
    }
    
    return $trial;
}

function shuffle_trials($trials) {
    $procedures = array_map('flatten_trial_into_procedure', $trials);
    $procedures = multiLevelShuffle($procedures);
    $procedures = shuffle2dArray($procedures);
    $trials = array_map('unflatten_trial_from_procedure', $procedures);
    return $trials;
}

function flatten_trial_into_procedure($trial) {
    $trial['Procedure']['__TEMP_STIMULI__'] = $trial['Stimuli'];
    $trial['Procedure']['__TEMP_RESPONSE__'] = $trial['Response'];
    return $trial['Procedure'];
}

function unflatten_trial_from_procedure($procedure) {
    $trial = [
        'Stimuli'   => $procedure['__TEMP_STIMULI__'],
        'Procedure' => $procedure,
        'Response'  => $procedure['__TEMP_RESPONSE__'],
    ];
    
    unset($trial['Procedure']['__TEMP_STIMULI__']);
    unset($trial['Procedure']['__TEMP_RESPONSE__']);
    
    return $trial;
}

function get_current_trial() {
    return get_trial($_SESSION['Position']);
}

function get_flat_array($arr) {
    $output = [];
    
    $arrs = [$arr];
    
    while (count($arrs) > 0) {
        $next_arr = array_shift($arrs);
        
        foreach ($next_arr as $v) {
            if (is_array($v)) {
                $arrs[] = $v;
            } else {
                $output[] = $v;
            }
        }
    }
    
    return $output;
}
