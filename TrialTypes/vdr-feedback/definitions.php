<?php

function get_word_scores($stimuli) {
    $scores = get_default_scores();
    
    foreach ($stimuli as $stim_row) {
        $ans = $stim_row['Answer'];
        $match = $_SESSION['Words recalled'][$ans];
        $val = $match['value'];
        $scores['possibleAcc'] += 1;
        $scores['possibleVal'] += $val;
        
        if ($match['word'] !== false) {
            $scores['lenientAcc'] += 1;
            $scores['lenientVal'] += $val;
            
            if ($match['diff'] === 0) {
                $scores['strictAcc'] += 1;
                $scores['strictVal'] += $val;
            }
        }
    }
    
    return $scores;
}

function get_default_scores() {
    return [
        'possibleAcc' => 0,
        'possibleVal' => 0,
        'lenientAcc' => 0,
        'lenientVal' => 0,
        'strictAcc' => 0,
        'strictVal' => 0
    ];
}
