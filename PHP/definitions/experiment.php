<?php

require __DIR__ . '/experiment-record.php';

function check_if_experiment_is_done() {
    if (!isset($_SESSION['Procedure'][$_SESSION['Position']])) {
        redirect('done');
    }
}

function get_current_procedure() {
    $position   = $_SESSION['Position'];
    $post_trial = $_SESSION['Post Trial'];
    $trial_set  = $_SESSION['Procedure'][$position];
    
    return get_trial_proc_values($trial_set, $post_trial);
}

function get_stimuli_rows($stim_range) {
    $row_numbers = get_valid_stim_row_numbers($stim_range, $_SESSION['Stimuli']);
    return get_stimuli_rows_from_row_numbers($row_numbers, $_SESSION['Stimuli']);
}

function get_stimuli_rows_from_row_numbers($row_numbers, $stimuli) {
    $stim_rows = [];
    
    foreach ($row_numbers as $i) {
        $stim_rows[] = $stimuli[$i - 2];
    }
    
    return $stim_rows;
}

function get_valid_stim_row_numbers($stim_range, $stimuli) {
    $range = get_range($stim_range);
    $row_numbers = [];
    
    foreach ($range as $i) {
        if (is_numeric($i) and isset($stimuli[$i - 2])) $row_numbers[] = $i;
    }
    
    return $row_numbers;
}

function get_trial_values($procedure, $stimuli) {
    $values = [];
    
    $stim_cols = get_scalar_stimuli($stimuli);
    
    foreach ($stim_cols as $col => $joined_vals) {
        $values[get_alias_name($col)] = $joined_vals;
    }
    
    foreach ($procedure as $col => $val) {
        $values[get_alias_name($col)] = $val;
    }
    
    return $values;
}

function get_scalar_stimuli($stim_rows) {
    $stimuli = invert_2d_array($stim_rows);
    
    foreach ($stimuli as $col => $vals) {
        $stimuli[$col] = implode('|', $vals);
    }
    
    return $stimuli;
}

function get_alias_name($col) {
    return strtolower(str_replace(' ', '_', $col));
}

function link_trial_type_file($trial_type, $file) {
    $filename = get_trial_type_dir($trial_type) . "/$file";
    
    if (is_file($filename)) echo get_link($filename);
}

function send_trial_values_to_javascript($trial_values) {
    define('ACTION', $trial_values['trial_type']);
    
    ?><script>
        COLLECTOR.trial_values = <?= json_encode($trial_values) ?>;
    </script><?php
}

## Error handling

function handle_trial_error(Exception $e) {
    $trial_set = $_SESSION['Procedure'][$_SESSION['Position']];
    $proc_row = get_original_proc_row($trial_set);
    echo '<div style="display: inline-block; text-align: left; margin: auto;">';
    echo '<h4>Error: cannot run trial because of the following problem:</h4>';
    echo '<div>' . $e->getMessage() . '</div>';
    echo '<p>Displaying trial info below:</p>';
    echo '<p>';
    echo '<div>Procedure row: ' . ($_SESSION['Position'] + 2) . '</div>';
    echo '<div>Post Trial Level: ' . $_SESSION['Post Trial'] . '</div>';
    echo '</p>';
    echo '<h4>Procedure (' . $_SESSION['Condition']['Procedure'] . ') row: </h4>';
    dump($proc_row);
    echo '<h4>Stimuli (' . $_SESSION['Condition']['Stimuli'] . '): </h4>';
    display_csv_table($_SESSION['Stimuli']);
    echo '</div>';
}

function get_original_proc_row($trial_set) {
    $row = [];
    
    foreach ($trial_set as $i => $trial) {
        foreach ($trial as $col => $val) {
            $col = $i === 0 ? $col : "Post $i $col";
            $row[$col] = $val;
        }
    }
    
    return $row;
}
