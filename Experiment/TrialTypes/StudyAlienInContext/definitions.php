<?php

function parse_settings($settings) {
    $settings_parsed = json_decode('{' . $settings . '}', true);

    $error = function($msg) {
        echo "<div>$msg</div>";
    };
    
    $numbers = ['alien_min_delay', 'alien_max_delay', 'alien_appear_time', 'alien_catch_time', 'alien_final_study_time', 'feedback_time'];
    
    foreach ($numbers as $num) {
        if (!isset($settings_parsed[$num])
            or !is_numeric($settings_parsed[$num])
        ) {
            $error("Settings incorrectly defined: '$num' not set or not numeric.");
        }
    }
    /*
    foreach (['left', 'right'] as $val) {
        if (!isset($settings_parsed["{$val}_key"])
            or strlen($settings_parsed["{$val}_key"]) > 1
        ) {
            $error("Settings incorrectly defined: '{$val}_key' not set or more than a single character long.");
        }
    }
    */
    if (isset($settings_parsed['force_alien_side'])
        and $settings_parsed['force_alien_side'] !== 'left'
        and $settings_parsed['force_alien_side'] !== 'right'
    ) {
        $error("Settings incorrectly defined: 'force_alien_side' defined, but not set to either 'left' or 'right'");
    }
    
    return $settings_parsed;
}
