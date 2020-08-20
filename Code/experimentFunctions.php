<?php

function recordTrial($extraData = array(), $exitIfDone = true, $advancePosition = true) {
    global $_CONFIG, $_PATH;
    #### setting up aliases (for later use)
    $currentPos   =& $_SESSION['Position'];
    $currentTrial =& $_SESSION['Trials'][$currentPos];

    #### Calculating time difference from current to last trial
    $oldTime = $_SESSION['Timestamp'];
    $_SESSION['Timestamp'] = microtime(true);
    $timeDif = $_SESSION['Timestamp'] - $oldTime;
    
    #### Writing to data file
    $data = array(  'Username'              =>  $_SESSION['Username'],
                    'ID'                    =>  $_SESSION['ID'],
                    'ExperimentName'        =>  $_CONFIG->experiment_name,
                    'Session'               =>  $_SESSION['Session'],
                    'Trial'                 =>  $_SESSION['Position'],
                    'Date'                  =>  date("c"),
                    'TimeDif'               =>  $timeDif,
                    'Condition Number'      =>  $_SESSION['Condition']['Number'],
                    'Stimuli File'          =>  $_SESSION['Condition']['Stimuli'],
                    'Order File'            =>  $_SESSION['Condition']['Procedure'],
                    'Condition Description' =>  $_SESSION['Condition']['Condition Description'],
                    'Condition Notes'       =>  $_SESSION['Condition']['Condition Notes']
                  );
    foreach ($currentTrial as $category => $array) {
        $data += AddPrefixToArray($category . '*', $array);
    }
    
    if (!is_array($extraData)) {
        $extraData = array($extraData);
    }
    foreach ($extraData as $header => $datum) {
        $data[$header] = $datum;
    }
    
    $writtenArray = arrayToLine($data, $_PATH->get('Experiment Output'));                                       // write data line to the file
    ###########################################

    // progresses the trial counter
    if ($advancePosition) {
        $currentPos++;
        $_SESSION['PostNumber'] = 0;
    }

    // are we done with the experiment? if so, send to finalQuestions.php
    if ($exitIfDone) {
        $item = $_SESSION['Trials'][$currentPos]['Procedure']['Item'];
        if ($item == 'ExperimentFinished') {
            $_SESSION['finishedTrials'] = true;             // stops people from skipping to the end
            header('Location: ' . $_PATH->get('Final Questions Page'));
            exit;
        }
    }
    return $writtenArray;
}

// this probably wont work with post trials, so dont use post trials with
// trials types that want to record multiple rows of data
function record_data_to_multiple_rows($data, $expanded_data, $keyMod, $expand_stim = false) {
    global $_PATH;
    
    foreach ($expanded_data as $col => $vals) {
        $data[$col] = $vals;
    }
    
    $pos = $_SESSION['Position'];
    $trial =& $_SESSION['Trials'][$pos];
    $trial['Response'] = placeData($data, $trial['Response'], $keyMod);
    $expanded_data = AddPrefixToArray($keyMod . 'Response*', $expanded_data);
    
    if ($expand_stim) {
        foreach ($trial['Stimuli'] as $col => $val) {
            $expanded_data["Stimuli*$col"] = explode('|', $val);
        }
        
        $expanded_data['Procedure*Item'] = rangeToArray($trial['Procedure']['Item']);
    }
    
    $expanded_rows = invert_2d_array($expanded_data);
    
    $last_index = count($expanded_rows) - 1;
    
    foreach ($expanded_rows as $i => $row) {
        $is_last_trial = $i === $last_index;
        recordTrial($row, $is_last_trial, $is_last_trial);
    }
    
    header('Location: ' . $_PATH->get('Experiment Page'));
    exit;
}

function get_multi_scoring($resps, $answers, $criterion = 0.75) {
    if (!is_array($answers)) $answers = explode('|', $answers);
    
    $scores = [];
    
    foreach ($resps as $i => $resp) {
        $scores[] = get_scoring($resp, $answers[$i], $criterion);
    }
    
    return invert_2d_array($scores);
}

function get_scoring($resp, $ans, $criterion) {
    $resp_clean = strtolower(trim($resp));
    $ans_clean = strtolower(trim($ans));
    similar_text($resp_clean, $ans_clean, $acc);
    
    return [
        'Accuracy' => $acc,
        'strictAcc' => $acc === 100.0 ? 1 : 0,
        'lenientAcc' => $acc >= $criterion ? 1 : 0
    ];
}

function shuffle_trial_items(&$trial) {
    $items = rangeToArray($trial['Procedure']['Item']);
    $stim = get_stimuli_as_rows($trial['Stimuli']);
    $order = array_keys($items);
    shuffle($order);
    $trial['Procedure']['Item'] = implode(',', resort($items, $order));
    $trial['Stimuli'] = get_stim_rows_as_values(resort($stim, $order));
}

function get_stimuli_as_rows($stim) {
    foreach ($stim as $col => $vals) {
        $stim[$col] = explode('|', $vals);
    }
    
    return invert_2d_array($stim);
}

function get_stim_rows_as_values($rows) {
    $stim = invert_2d_array($rows);
    
    foreach ($stim as $col => $vals) {
        $stim[$col] = implode('|', $vals);
    }
    
    return $stim;
}

function resort($arr, $order) {
    $new_arr = [];
    
    foreach ($order as $old => $new) {
        $new_arr[$new] = $arr[$old];
    }
    
    ksort($new_arr);
    return $new_arr;
}
