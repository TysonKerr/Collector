<?php

/* returns the minimum amount of time participants should take
 * responding to the current trial, based on how quickly
 * they finished previous trials. If there aren't enough
 * previous trials to check, 0 will be returned
 */
function get_min_time_required_before_responding() {
    $min_time_per_trial = 2000; // measured in milliseconds
    $number_of_previous_trials_to_check = 4;
    $trial_types_to_check = ['similarityjudgmenttimed', 'similaritytriad'];
    
    $position = $_SESSION['Position'];
    $number_of_checked_trial_types = 0;
    $time_taken = 0;
    
    while (--$position >= 1) {
        $trial = $_SESSION['Trials'][$position];
        $trial_type = strtolower($trial['Procedure']['Trial Type']);
        
        if (in_array($trial_type, $trial_types_to_check)) {
            ++$number_of_checked_trial_types;
            $time_taken += $trial['Response']['RT'];
            
            if ($number_of_checked_trial_types >= $number_of_previous_trials_to_check) break;
        }
    }
    
    if ($number_of_checked_trial_types < $number_of_previous_trials_to_check) {
        return 0;
    } else {
        $m = $min_time_per_trial;
        $p = $number_of_previous_trials_to_check;
        $t = $time_taken;
        return $m * ($p + 1) - $t;
    }
    
    return $min_time_per_trial;
}
