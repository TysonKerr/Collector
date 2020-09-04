<?php

function get_score($settings) {
    $trial_selector = ['previous_rows' => $settings['rows']];
    $col = $settings['scoring_column'];
    $score = get_average_response_from_trials($trial_selector, [$col]);
    return $score[$col];
}

function repeat_trials($settings, $shuffle = true) {
    $rows = get_proc_rows($settings);
    if ($shuffle) shuffle_csv_array($rows);
    increment_repeat_counter($rows);
    $trial_sets = convert_rows_to_trial_sets($rows);
    array_splice($_SESSION['Procedure'], $_SESSION['Position'] + 1, 0, $trial_sets);
}

function get_proc_rows($settings) {
    $trial_sets = get_trial_sets($settings);
    $proc_rows = [];
    
    foreach ($trial_sets as $set) {
        $row = $set[0];
        $post_trials = count($set) - 1;
        
        for ($i = 1; $i <= $post_trials; ++$i) {
            foreach ($set[$i] as $col => $val) {
                $row["Post $i $col"] = $val;
            }
        }
        
        $proc_rows[] = $row;
    }
    
    return $proc_rows;
}

function get_trial_sets($settings) {
    $pos = $_SESSION['Position'];
    $proc = $_SESSION['Procedure'];
    $trial_sets = [];
    
    for ($i = 0; $i <= $settings['rows']; ++$i) {
        $trial_sets[] = $proc[$pos - $i];
    }
    
    return array_reverse($trial_sets);
}

function increment_repeat_counter(&$proc_rows) {
    foreach ($proc_rows as &$row) {
        if (!isset($row['Repeat_Count']) or !is_numeric($row['Repeat_Count'])) {
            $row['Repeat_Count'] = 1;
        } else {
            ++$row['Repeat_Count'];
        }
    }
}

function give_fail_trial_type($trial_type) {
    $trial = ['Trial Type' => $trial_type, 'Max Time' => ''];
    $trial_set = [$trial];
    array_splice(
        $_SESSION['Procedure'],
        $_SESSION['Position'] + 1,
        count($_SESSION['Procedure']),
        [$trial_set]
    );
}

function convert_rows_to_trial_sets($proc_rows) {
    $trial_sets = [];
    
    foreach ($proc_rows as $row) {
        $trial_sets[] = get_post_trials_from_row($row);
    }
    
    return $trial_sets;
}
