<?php

function has_submitted_data_to_record() {
    return filter_input_array(INPUT_POST) !== null;
}

function record_submitted_data() {
    $data = get_processed_data();
    save_experiment_data($data);
    advance_trial();
}

function get_processed_data() {
    $data = filter_input_array(INPUT_POST);
    $responses = get_responses_without_added_data($data);
    // making aliases for custom scoring to use
    $procedure = get_current_procedure();
    $stimuli = get_stimuli_rows($procedure['Stim Rows']);
    $trial_values = get_trial_values($procedure, $stimuli);
    extract($trial_values, EXTR_SKIP|EXTR_REFS);
    
    if (is_file(get_trial_type_dir($trial_type) . '/scoring.php')) {
        if (is_file(get_trial_file($trial_type, 'definitions.php'))) {
            require get_trial_file($trial_type, 'definitions.php');
        }
        
        require get_trial_type_dir($trial_type) . '/scoring.php';
    }
    
    if (isset($data['Response'], $answer) and $answer !== '') {
        $data += get_scoring($data['Response'], $answer);
    }
    
    $data['Trial_Record_Date']    = get_date();
    $data['Trial_Record_TimeDif'] = get_time_diff_and_update_timestamp();
    
    check_for_errors_in_processed_data($data);
    return $data;
}

function get_responses_without_added_data($data) {
    $added_data_cols = [
        'Trial_Start_Timestamp',
        'Trial_Timestamp_First_Keypress',
        'Trial_Timestamp_Last_Keypress',
        'Trial_Focus',
        'Trial_Duration',
        'Trial_Window_Width',
        'Trial_Window_Height',
        'Trial_Submit_Timestamp'
    ];
    
    foreach ($added_data_cols as $col) {
        unset($data[$col]);
    }
    
    return $data;
}

function get_time_diff_and_update_timestamp() {
    $now = microtime(true);
    $diff = floor(($now - $_SESSION['Trial Timestamp']) * 1000) / 1000;
    $_SESSION['Trial Timestamp'] = $now;
    return $diff;
}

function get_scoring($resp, $ans, $criterion = null) {
    if ($criterion === null) $criterion = get_config('lenient_criteria');
    
    if (is_array($resp)) {
        $scores = [];
        $answers = explode('|', $ans);
        
        if (count($answers) < count($resp)) return [];
        
        foreach ($resp as $i => $single_resp) {
            $scores[] = get_scoring_of_scalar($single_resp, $answers, $criterion);
        }
        
        return invert_2d_array($scores);
    } else {
        return get_scoring_of_scalar($resp, $ans, $criterion);
    }
}

function get_scoring_of_scalar($resp, $ans, $criterion = null) {
    if ($criterion === null) $criterion = get_config('lenient_criteria');
    
    $resp_clean = strtolower(trim($resp));
    $ans_clean = strtolower(trim($ans));
    similar_text($resp_clean, $ans_clean, $acc);
    
    return [
        'Accuracy'   => round($acc),
        'strictAcc'  => $acc === 100.0 ? 1 : 0,
        'lenientAcc' => $acc >= $criterion ? 1 : 0
    ];
}

function save_experiment_data($data) {
    $pos = $_SESSION['Position'];
    $post = $_SESSION['Post Trial'];
    $responses = &$_SESSION['Responses'];
    
    if (!isset($responses[$pos]))        $responses[$pos] = [];
    if (!isset($responses[$pos][$post])) $responses[$pos][$post] = [];
    
    unset($responses);
    $responses = &$_SESSION['Responses'][$pos][$post];
    
    foreach ($data as $header => $val) {
        $responses[$header] = $val;
    }
}

function advance_trial() {
    $indices = get_indices_of_next_trial(
        $_SESSION['Procedure'], $_SESSION['Position'], $_SESSION['Post Trial']
    );
    
    if ($indices[0] !== $_SESSION['Position']) {
        record_current_trial_set();
    }
    
    $_SESSION['Position']   = $indices[0];
    $_SESSION['Post Trial'] = $indices[1];
    
    check_if_experiment_is_done(); // will redirect to done/ and exit if done
    redirect('experiment');
}

function get_indices_of_next_trial($procedure, $position, $post_trial) {
    // this assumes we are currently on a valid trial
    // although if you set $post_trial to -1, it should work
    do {
        ++$post_trial;
        
        if (!isset($procedure[$position][$post_trial])) {
            ++$position;
            $post_trial = 0;
            
            if (!isset($procedure[$position])) {
                return [$position, $post_trial];
            }
        }
        
        $trial_type = get_trial_proc_value($procedure[$position], $post_trial, 'Trial Type');
        
        if ($trial_type !== '') return [$position, $post_trial];
    } while(true);
}

function record_current_trial_set() {
    $rows = get_trial_set_output_rows();
    $filename = get_data_filename('output');
    write_csv($filename, $rows);
}

function get_trial_set_output_rows() {
    $unmerged_rows = get_trial_set_unmerged_rows();
    $merged_rows = get_merged_trial_set_data_rows($unmerged_rows);
    $output_rows = get_trial_rows_with_trial_metadata($merged_rows);
    return $output_rows;
}

function get_trial_set_unmerged_rows() {
    $trial_set_unmerged_rows = [];
    $trial_set_data = get_trial_set_data();
    
    foreach ($trial_set_data as $post => $trial_data) {
        $trial_set_unmerged_rows[$post] = get_trial_data_rows($trial_data);
    }
    
    return $trial_set_unmerged_rows;
}

function get_trial_set_data() {
    $trial_set_data = [];
    $resp_levels = array_keys($_SESSION['Responses'][$_SESSION['Position']]);
    
    foreach ($resp_levels as $post_level) {
        $trial_data = get_trial_data($_SESSION['Position'], $post_level);
        
        if ($trial_data !== false) {
            $trial_set_data[$post_level] = $trial_data;
        }
    }
    
    return $trial_set_data;
}

function get_trial_data($position, $post_trial) {
    if (!isset($_SESSION['Procedure'][$position])) return false;
    
    $trial_set = $_SESSION['Procedure'][$position];
    $procedure = get_trial_proc_values($trial_set, $post_trial);
    
    if ($procedure === false) return false;
    if ($procedure['Trial Type'] === '') return false;
    
    return [
        'Procedure' => $procedure,
        'Stimuli'   => get_stimuli_rows($procedure['Stim Rows']),
        'Responses' => get_trial_responses($position, $post_trial)
    ];
}

function get_trial_responses($position, $post_trial) {
    $responses = [];
    
    if (isset($_SESSION['Responses'][$position])
        and isset($_SESSION['Responses'][$position][$post_trial])
    ) {
        $responses = $_SESSION['Responses'][$position][$post_trial];
    }
    
    return $responses;
}

function get_trial_data_rows($trial_data) {
    $row_base = get_array_with_prefixed_keys($trial_data['Procedure'], 'Proc ');
    $expanded_columns = [];
    $rows = [];
    $longest_resp = 1;
    $stim_count = count($trial_data['Stimuli']);
    
    foreach ($trial_data['Responses'] as $col => $val) {
        if (is_array($val)) {
            $longest_resp = max(count($val), $longest_resp);
            $expanded_columns["Resp $col"] = $val;
        } else {
            $row_base["Resp $col"] = $val;
        }
    }
    
    if ($stim_count > 0) {
        $stim = invert_2d_array($trial_data['Stimuli']);
        
        if ($stim_count === $longest_resp) {
            foreach ($stim as $col => $vals) {
                $expanded_columns["Stim $col"] = $vals;
            }
            
            // also expand the "Proc Stim Rows" column
            $stim_range = $row_base['Proc Stim Rows'];
            unset($row_base['Proc Stim Rows']);
            $row_numbers = get_valid_stim_row_numbers($stim_range, $_SESSION['Stimuli']);
            $expanded_columns['Proc Stim Rows'] = $row_numbers;
        } else {
            foreach ($stim as $col => $vals) {
                $row_base["Stim $col"] = implode('|', $vals);
            }
        }
    }
    
    for ($i = 0; $i < $longest_resp; ++$i) {
        $row = $row_base;
        
        foreach ($expanded_columns as $col => $vals) {
            $row[$col] = isset($vals[$i]) ? $vals[$i] : '';
        }
        
        $rows[] = $row;
    }
    
    return $rows;
}

function get_merged_trial_set_data_rows($unmerged_rows) {
    $rows = [];
    $sub_row_count = [];
    
    foreach ($unmerged_rows as $post => $sub_rows) {
        $sub_row_count[$post] = count($sub_rows);
    }
    
    $row_count = max($sub_row_count);
    
    for ($i = 0; $i < $row_count; ++$i) {
        $row = [];
        
        foreach ($unmerged_rows as $post => $trial_rows) {
            if (isset($trial_rows[$i])) {
                foreach ($trial_rows[$i] as $col => $val) {
                    $col = $post === 0 ? $col : "Post $post $col";
                    $row[$col] = $val;
                }
            } else {
                foreach ($trial_rows[0] as $col => $val) {
                    $col = $post === 0 ? $col : "Post $post $col";
                    $row[$col] = $sub_row_count[$post] === 1 ? $val : '';
                }
            }
        }
        
        $rows[] = $row;
    }
    
    return $rows;
}

function get_trial_rows_with_trial_metadata($rows) {
    $output_rows = [];
    $trial_metadata = get_trial_set_metadata();
    
    foreach ($rows as $row) {
        $output_rows[] = array_merge($trial_metadata, $row);
    }
    
    return $output_rows;
}

function get_trial_set_metadata() {
    return [
        'Username'   => $_SESSION['Username'],
        'ID'         => $_SESSION['ID'],
        'Experiment' => CURR_EXP,
        'Session'    => $_SESSION['Session'],
        'Trial'      => $_SESSION['Position'] + 1
    ] + get_array_with_prefixed_keys($_SESSION['Condition'], 'Cond ');
}

function check_for_errors_in_processed_data($data) {
    foreach ($data as $col => $vals) {
        if (preg_match('/^Post (\\d+) (.+)/', $col, $matches)) {
            throw new Exception("Data field $col not allowed, output must not be in the format 'Post X ...'.");
        }
    }
}
