<?php

function get_max_time_for_waiting($max_time) {
    if (is_numeric($max_time)) {
        $time_left = get_max_time_for_waiting_specific_amount($max_time);
    } else {
        $time_left = get_max_time_for_waiting_for_relogin();
    }
    
    return $time_left;
}

function get_max_time_for_waiting_specific_amount($max_time) {
    if (!isset($_SESSION['resume time'])) {
        $_SESSION['resume time'] = get_resume_time($max_time);
        $_SESSION['Session']++;
    }

    // this will automatically set the "Max Time" to not submit until the right time
    // and it will autosubmit if they wait until the time has elapsed
    return $_SESSION['resume time'] - time();
}

function get_resume_time($max_time) {
    $time_to_wait = (int) $max_time;

    if ($time_to_wait < 1) $time_to_wait = 1;
    
    return time() + $time_to_wait;
}

function get_max_time_for_waiting_for_relogin() {
    // if a numeric value wasn't provided in the Settings column,
    // then simply check if they have gotten a new ID by returning to the
    // login page and entering their username again
    if (!isset($_SESSION['old ID'])) {
        $_SESSION['Session']++;
        $_SESSION['old ID'] = $_SESSION['ID'];
    }
    
    if ($_SESSION['ID'] === $_SESSION['old ID']) {
        $max_time = 60 * 60 * 24 * 2; // just set it to some arbitrarily large time
    } else {
        $max_time = 0;
    }
    
    return $max_time;
}

function get_wait_time_message($max_time) {
    $wait_time_msg = get_formatted_wait_time($max_time);
    return "Please return in $wait_time_msg.";
}

function get_formatted_wait_time($seconds) {
    $units = get_units_of_time_remaining($seconds);
    $displayed_units = get_largest_and_subsequent_non_zero_units($units);
    $displays = [];
    $previous_count = null;
    
    foreach ($displayed_units as $unit => $count) {
        if ($previous_count > 12) continue;
        
        $unit_display = $count > 1 ? $unit : substr($unit, 0, -1);
        $displays[] = "$count $unit_display";
        $previous_count = $count;
    }
    
    return implode(" and ", $displays);
}

function get_units_of_time_remaining($seconds) {
    $seconds = max(0, (int) $seconds);
    
    $units = [
        'days'    => 60 * 60 * 24,
        'hours'   => 60 * 60,
        'minutes' => 60
    ];
    
    $counts = [];
    
    foreach ($units as $unit => $seconds_per_unit) {
        $counts[$unit] = floor($seconds / $seconds_per_unit);
        $seconds = $seconds % $seconds_per_unit;
    }
    
    $counts['seconds'] = $seconds;
    
    return $counts;
}

function get_largest_and_subsequent_non_zero_units($units) {
    $largest_units = [];
    $largest_unit = null;
    
    foreach ($units as $unit => $count) {
        if ($largest_unit !== null) {
            if ($count > 0) $largest_units[$unit] = $count;
            break;
        }
        
        if ($count > 0) {
            $largest_units[$unit] = $count;
            $largest_unit = $unit;
        }
    }
    
    return $largest_units;
}
