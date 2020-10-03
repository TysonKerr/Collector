<?php

function get_number_of_inputs($settings, $stim_rows) {
    if (is_numeric($settings['inputs'])) {
        return max(1, (int) $settings['inputs']);
    } else {
        return count($stim_rows);
    }
}

function get_dam_lev_dist_with_limit($str1, $str2, $limit) {
    $str1 = trim(strtolower($str1));
    $str2 = trim(strtolower($str2));
    $len1 = strlen($str1);
    $len2 = strlen($str2);
    
    if ($str1 === $str2) return 0;
    if (abs($len1 - $len2) > $limit) return false;
    if ($str1 === '') return $len2 > $limit ? false : $len2;
    if ($str2 === '') return $len1 > $limit ? false : $len1;
    
    //after this, i understand nothing
    $score = array();
    
    $inf = $len1 + $len2;
    $score[0][0] = $inf;
    
    for ($i = 0; $i <= $len1; ++$i) {
        $score[$i + 1][1] = $i;
        $score[$i + 1][0] = $inf;
    }
    
    for ($i = 0; $i <= $len2; ++$i) {
        $score[1][$i + 1] = $i;
        $score[0][$i + 1] = $inf;
    }
    
    $sd = [];
    
    foreach (str_split($str1 . $str2) as $char) {
        $sd[$char] = 0;
    }
    
    for ($i = 1; $i <= $len1; ++$i) {
        $db = 0;
        
        for ($j = 1; $j <= $len2; ++$j) {
            $i1 = $sd[$str2[$j-1]];
            $j1 = $db;
            
            if ($str1[$i-1] === $str2[$j-1]) {
                $score[$i+1][$j+1] = $score[$i][$j];
                $db = $j;
            } else {
                $score[$i+1][$j+1] = min($score[$i][$j], $score[$i+1][$j], $score[$i][$j+1]) + 1;
            }
            
            $score[$i+1][$j+1] = min($score[$i+1][$j+1], ($score[$i1][$j1] + $i - $i1 + $j - $j1 - 1));
        }
        
        $sd[$str1[$i-1]] = $i;
        
        if ($score[$i][max(1, $j + $i - $len1 - 1)] > $limit ) {
            return false; 
        }
    }
    
    if ($score[$len1+1][$len2+1] > $limit ) {
        return false;
    }
    
    return $score[$len1+1][$len2+1];
}

function get_selectivity_index($values, $score, $num_correct) {
    if (count($values) === 0) return 0;
    
    $max = array_sum($values);
    $ave = $max / count($values);
    $chance = $ave * $num_correct;
    $ideal = get_ideal_score($values, $num_correct);
    
    if ($ideal - $chance == 0) return 0;
    
    $selectivity_index = ($score - $chance) / ($ideal - $chance);
    return round($selectivity_index * 1000) / 1000;
}

function get_ideal_score($values, $num_correct) {
    sort($values);
    $score = 0;
    
    for ($i = 0; $i < $num_correct; ++$i) {
        $score += array_pop($values);
    }
    
    return $score;
}

function get_arr_with_numeric_vals($arr) {
    $output = [];
    
    foreach ($arr as $val) {
        $output[] = (float) $val;
    }
    
    return $output;
}

function get_word_responses($response) {
    if (is_array($response)) {
        $responses = [];
        
        foreach ($response as $resp) {
            $trimmed = trim($resp);
            
            if ($trimmed !== '') {
                $responses[] = $trimmed;
            }
        }
    } else {
        // replace most symbols with spaces, so that if they entered like
        // "word,word,word", we get separate words
        $remove_punctuation = preg_replace("/[^a-zA-Z0-9'\- ]+/", ' ', $response);
        // then, set all spaces and newlines to a single space.
        // this assumes that the answers dont have non-alphanumerical characters in them
        $remove_extra_space = trim(preg_replace('/\s+/', ' ', $remove_punctuation));
        $responses = $remove_extra_space === '' ? [] : explode(' ', $remove_extra_space);
    }
    
    return $responses;
}

function get_dam_lev_distances_with_limit($responses, $answers, $leniencies) {
    $dists = ['ans' => [], 'res' => []];
    
    foreach ($answers as $ans_i => $ans) {
        foreach($responses as $res_i => $res) {
            $dist = get_dam_lev_dist_with_limit($ans, $res, $leniencies[$ans_i]);
            
            if ($dist === false) continue;
            
            $dists['ans'][$ans_i][$res_i] = $dist;
            $dists['res'][$res_i][$ans_i] = $dist;
        }
    }
    
    return $dists;
}

function get_word_matches($responses, $answers, $leniencies, $values) {
    $dists = get_dam_lev_distances_with_limit($responses, $answers, $leniencies);
    $matches = get_matches_default($answers, $values);
    // keep going until all of our Answer rows are empty, meaning they have had all possible matches removed
    while (count($dists['ans'], COUNT_RECURSIVE) !== count($dists['res'])) {
        foreach ($dists['ans'] as $ans_i => $resp_dists_to_ans) {
            $min_dist_for_ans = min($resp_dists_to_ans);
            
            foreach ($resp_dists_to_ans as $res_i => $diff) {
                if ($diff === $min_dist_for_ans and $diff === min($dists['res'][$res_i])) {
                    $ans = $answers[$ans_i];
                    $matches[$ans]['word']         = $responses[$res_i];
                    $matches[$ans]['diff']         = $diff;
                    $matches[$ans]['output_order'] = $res_i + 1;
                    // remove all references to this match in both arrays, along their columns and rows
                    foreach ($dists['res'][$res_i] as $ans_j => $_) {
                        unset($dists['ans'][$ans_j][$res_i]);
                    }
                    
                    foreach ($dists['ans'][$ans_i] as $res_j => $_) {
                        unset($dists['res'][$res_j][$ans_i]);
                    }
                    
                    unset($dists['ans'][$ans_i]);
                    unset($dists['res'][$res_i]);
                    break 2;
                }
            }
        }
    }
    
    return $matches;
}

function get_matches_default($answers, $values) {
    $matches = [];
    
    foreach ($answers as $i => $ans) {
        $matches[$ans] = [
            'word' => false,
            'diff' => false,
            'output_order' => false,
            'value' => $values[$i]
        ];
    }
    
    return $matches;
}

function get_sub_array($arr, $sub) {
    $output = [];
    
    foreach ($arr as $i => $sub_arr) {
        $output[$i] = $sub_arr[$sub];
    }
    
    return $output;
}

function get_default_columns() {
    $cols = ['Response', 'Accuracy', 'possibleAcc', 'possibleVal', 'lenientAcc',
        'lenientVal', 'strictAcc', 'strictVal', 'Selectivity_Index_Strict',
        'Selectivity_Index_Lenient', 'Word_Order', 'Matched_Resp',
        'Matched_Diff', 'Output_Order', 'Word_lenientAcc', 'Word_lenientVal',
        'Word_strictAcc', 'Word_strictVal'];
    return array_combine($cols, array_fill(0, count($cols), ''));
}

function get_raw_response($resp) {
    return is_array($resp) ? implode('|', $resp) : $resp;
}
