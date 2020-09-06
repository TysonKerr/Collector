<?php

require __DIR__ . '/experiment-record.php';

function check_if_experiment_is_done() {
    if (!isset($_SESSION['Procedure'][$_SESSION['Position']])) {
        redirect('done');
    }
}

function get_current_procedure() {
    return get_trial_procedure($_SESSION['Position'], $_SESSION['Post Trial']);
}

function get_trial_procedure($position, $post_trial) {
    $trial_set = $_SESSION['Procedure'][$position];
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
    
    $values['settings'] = parse_trial_settings($values['settings'], $procedure['Trial Type']);
    
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

## Retrieving previous trial data

function get_average_response_from_trials($settings, $columns) {
    $values = get_response_values_from_trials($settings, $columns);
    $scores = [];
    
    foreach ($values as $col => $vals) {
        $scores[$col] = get_array_average($vals);
    }
    
    return $scores;
}

function get_response_values_from_trials($settings, $columns) {
    $trials = get_previous_trial_responses($settings, false);
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

function get_previous_trial_responses($settings, $with_proc_and_stim = true) {
    $response_indices = get_selected_trial_indices($settings);
    $trial_data = [];
    
    foreach ($response_indices as $trial_index) {
        $trial_data[] = get_previous_trial_data(
            $trial_index[0],
            $trial_index[1],
            $with_proc_and_stim
        );
    }
    
    return $trial_data;
}

function get_previous_trial_data($position, $post_trial, $with_proc_and_stim) {
    $trial = [
        'Responses' => $_SESSION['Responses'][$position][$post_trial]
    ];
    
    if ($with_proc_and_stim) {
        $trial['Procedure'] = get_trial_procedure($position, $post_trial);
        $trial['Stimuli']   = get_stimuli_rows($trial['Procedure']['Stim Rows']);
    }
    
    return $trial;
}

function get_selected_trial_indices($settings) {
    $trial_indices = get_previous_response_indices();
    $indices = get_trial_absolute_indices($settings, $trial_indices);
    $selected = [];
    
    foreach ($indices as $trial_index) {
        $selected[] = $trial_indices[$trial_index];
    }
    
    return $selected;
}

function get_trial_absolute_indices($settings, $trial_indices) {
    $indices = [];
    $prev_trials = $settings['previous_trials'] ?? false;
    $prev_range  = $settings['previous_range']  ?? false;
    $abs_range   = $settings['absolute_range']  ?? false;
    $labels      = $settings['labels']          ?? false;
    $prev_rows   = $settings['previous_rows']   ?? false;
    $trial_count = count($trial_indices);
    
    $indices = array_merge(
        get_prev_trial_indices($prev_trials, $trial_count),
        get_prev_range_indices($prev_range,  $trial_count),
        get_abs_range_indices( $abs_range,   $trial_count),
        get_labled_indices(    $labels,      $trial_indices),
        get_prev_row_indices(  $prev_rows,   $trial_indices)
    );
    
    $indices = array_keys(array_flip($indices));
    sort($indices);
    return $indices;
}

function get_previous_response_indices() {
    $indices = [];
    
    foreach ($_SESSION['Responses'] as $pos => $set) {
        foreach ($set as $post => $trial_responses) {
            $indices[] = [$pos, $post];
        }
    }
    
    return $indices;
}

function get_prev_trial_indices($prev_trials, $trial_count) {
    $indices = [];
    
    if (is_numeric($prev_trials)) {
        $prev_trials = (int) $prev_trials;
        
        for ($i = 1; $i <= $prev_trials; ++$i) {
            $indices[] = $trial_count - $i;
        }
    }
    
    return $indices;
}

function get_prev_range_indices($prev_range, $trial_count) {
    $indices = [];
    
    if (is_string($prev_range) and strlen($prev_range) > 0) {
        $range = get_range($prev_range);
        
        foreach ($range as $i) {
            if (is_numeric($i) and $i > 0) $indices[] = $trial_count - $i;
        }
    }
    
    return $indices;
}

function get_abs_range_indices($abs_range, $trial_count) {
    $indices = [];
    
    if (is_string($abs_range) and strlen($abs_range) > 0) {
        $range = get_range($abs_range);
        
        foreach ($range as $i) {
            if (is_numeric($i) and $i >= 0 and $i < $trial_count) $indices[] = $i;
        }
    }
    
    return $indices;
}

function get_labled_indices($labels, $trial_indices) {
    $indices = [];
    
    if (is_string($labels)) $labels = [$labels];
    
    if (is_array($labels)) {
        $labels = array_flip($labels);
        
        foreach ($trial_indices as $i => $index_pair) {
            $trial = $_SESSION['Procedure'][$index_pair[0]][$index_pair[1]];
            $label = $trial['Label'] ?? false;
            
            if (isset($labels[$label])) $indices[] = $i;
        }
    }
    
    return $indices;
}

function get_prev_row_indices($prev_rows, $trial_indices) {
    if ($prev_rows === false) return [];
    
    $trial_count = 0;
    $pos = $_SESSION['Position'];
    
    for ($i = $pos - $prev_rows; $i <= $pos; ++$i) {
        if (!isset($_SESSION['Responses'][$i])) continue;
        
        foreach ($_SESSION['Responses'][$i] as $post => $resp) {
            ++$trial_count;
        }
    }
    
    if ($trial_count < 1) return [];
    
    return array_slice(array_keys($trial_indices), -$trial_count);
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
