<?php

function check_for_errors_in_conditions($conditions) {
    $exp_dir = get_exp_dir();
    $stim_dir = "$exp_dir/Stimuli";
    $proc_dir = "$exp_dir/Procedure";
    $conditions_count = 0;
    
    foreach ($conditions as $condition) {
        if (!condition_is_flagged($condition)) ++$conditions_count;
        
        if ($condition['Stimuli'] !== '' and !is_file("$stim_dir/{$condition['Stimuli']}")) {
            throw new Exception("The stimuli file '{$condition['Stimuli']}' could not be found. Please check the Stimuli folder to make sure that file exists.");
        }
        
        if (!isset($condition['Procedure'])) {
            throw new Exception('The conditions file is missing the "Procedure" column.');
        }else if (!is_file("$proc_dir/{$condition['Procedure']}")) {
            throw new Exception("The procedure file '{$condition['Procedure']}' could not be found. Please check the Procedure folder to make sure that file exists.");
        }
    }
    
    if ($conditions_count < 1) throw new Exception('No valid conditions listed in conditions file. At least 1 condition must exist that isn\'t flagged (i.e., must not start with the "#" character).');
}

function check_exp_data_for_errors($data, $type) {
    if ($type === 'Stimuli') {
        check_for_errors_in_stimuli($data);
    } else {
        check_for_errors_in_procedure($data);
    }
}

function check_for_errors_in_procedure($procedure) {
    // things to check for:
    // 1. all trial types listed actually exist
    // 2. everything that should be inherited actually can be
    $trial_types = get_trial_types();
    $errors = [];
    
    foreach ($procedure as $row_index => $trial_set) {
        $row_num = $row_index + 2; // human readable form
        
        foreach ($trial_set as $post => $_) {
            try {
                $proc_values = get_trial_proc_values($trial_set, $post);
            } catch (Exception $e) {
                $errors[] = "Row $row_num: " . $e->getMessage();
            }
            
            $trial_type = strtolower($proc_values['Trial Type']);
            
            if ($trial_type !== '' and !isset($trial_types[$trial_type])) {
                $col = $post === 0 ? 'Trial Type' : "Post $post Trial Type";
                $errors[] = "Row $row_num: trial type '$trial_type' does not exist.";
            }
        }
    }
    
    if (count($errors) > 0) {
        throw new Exception(implode("\n", $errors));
    }
}

function check_for_errors_in_stimuli($stim) {
    $errors = [];
    
    foreach ($stim as $i => $row) {
        if (isset($row['Cue'])) {
            $cue = $row['Cue'];
            $start = strtolower(substr($cue, 0, 6));
            
            if (($start === 'media/' or $start === 'media\\')
                and !fileExists(ROOT . "/$cue")
            ) {
                $row_num = $i + 2;
                $errors[] = "On line $row_num, the Cue '$cue' looks like it should be found in the media folder, but no such file exists."
                          . " There might be a typo in the cue.";
            }
        }
    }
        
    // post trials might record post trial stimuli
    // so, dont allow the stim file to create columns that look like those
    if (isset($stim[0])) {
        foreach ($stim[0] as $col => $_) {
            if (preg_match('/^Post (\\d+) (.+)/', $col, $matches)) {
                $errors[] = "Column '{$matches[0]}' not allowed, stim file columns shouldn't look like post trial columns";
            }
        }
    }
    
    if (count($errors) > 0) {
        throw new Exception(implode("\n", $errors));
    }
}

function check_all_conditions() {
    $conditions_indices = get_possible_conditions_indices();
    $procs = [];
    $stims = [];
    $all_errors = [];
    
    foreach ($conditions_indices as $index) {
        $errors = [];
        
        try {
            $conditions = get_conditions($index);
            
            foreach ($conditions as $condition) {
                if (!isset($procs[$condition['Procedure']])) {
                    $procs[$condition['Procedure']] = true;
                    
                    try {
                        get_procedure($condition['Procedure']);
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
                
                if (!isset($stims[$condition['Stimuli']])) {
                    $stims[$condition['Stimuli']] = true;
                    
                    try {
                        get_stimuli($condition['Stimuli']);
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        if (count($errors) > 0) {
            $all_errors[] = implode("\n", $errors);
        }
    }
    
    $all_errors = array_merge($all_errors, check_config_proc_files());
    
    if (count($all_errors) > 0) {
        throw new Exception("\nErrors in experiment files.\nPlease correct these before continuing.\n\n" . implode("\n", $all_errors));
    }
}

function check_config_proc_files() {
    $errors = [];
    $proc_dir = get_exp_dir() . '/Procedure';
    
    foreach (['prepend_procedure', 'append_procedure'] as $config_proc) {
        try {
            $file_list = get_config_proc_filename($config_proc);
            
            if ($file_list) {
                $files = explode(',', $file_list);
                
                foreach ($files as $file) {
                    if (!is_file("$proc_dir/$file")) {
                        throw new Exception(
                            "The config setting '$config_proc' is set to '$file_list', but the file '$file' cannot be found."
                          . " Please make sure this file really exists in the Procedure folder."
                        );
                    }
                    
                    get_procedure($file);
                }
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    return $errors;
}
