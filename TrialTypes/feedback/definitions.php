<?php

function get_scores($settings) {
    $trials = get_previous_trials($settings);
    
    $columns = ['Accuracy', 'lenientAcc', 'strictAcc'];
    $values = get_response_values($trials, $columns);
    $scores = [];
    
    foreach ($values as $col => $vals) {
        $scores[$col] = get_array_average($vals);
    }
    
    return $scores;
}

function get_response_values($trials, $columns) {
    $values = [];
    
    foreach ($columns as $col) {
        $values[$col] = [];
    }
    
    foreach ($trials as $trial) {
        foreach ($columns as $col) {
            if (isset($trial['Responses'][$col])) {
                $values[$col][] = $trial['Responses'][$col];
            }
        }
    }
    
    return $values;
}

function get_array_average($values) {
    $numbers = get_numeric_values(get_flat_array($values));
    
    return count($numbers) === 0
         ? NAN
         : array_sum($numbers) / count($numbers);
}

function get_numeric_values($arr) {
    $numbers = [];
    
    foreach ($arr as $val) {
        if (is_numeric($val)) $numbers[] = $val;
    }
    
    return $numbers;
}
