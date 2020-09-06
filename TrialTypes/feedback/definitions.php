<?php

function get_scores($settings) {
    $columns = ['Accuracy', 'lenientAcc', 'strictAcc'];
    
    $data = get_response_values_from_trials($settings, $columns);
    
    $data['Accuracy']   = get_array_average($data['Accuracy']);
    $data['lenientAcc'] = get_array_sum($data['lenientAcc']);
    $data['strictAcc']  = get_array_sum($data['strictAcc']);
    
    return $data;
}
