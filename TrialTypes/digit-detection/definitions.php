<?php

function get_digit_sequence() {
    $sequence_length = 60;
    $odd_sequences = 8;
    $other_digits = $sequence_length - 3 * $odd_sequences;
    
    $sequence_base = array_fill(0, $other_digits, 'either')
                   + array_fill($other_digits, $odd_sequences, 'odd-triple');
                   
    $sequence = get_sequence_from_sequence_base($sequence_base);
    verify_sequence($sequence);
    return $sequence;
}

function get_sequence_from_sequence_base($sequence_base) {
    $shuffled_base = get_shuffled_sequence_base($sequence_base);
    $odds   = [1, 3, 5, 7, 9];
    $evens  = [2, 4, 6, 8];
    $digits = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    $sequence = [];
    $si = -1;
    
    foreach ($shuffled_base as $i => $base_component) {
        if ($base_component === 'odd-triple') {
            $sequence[] = $odds[rand(0, 4)];
            $sequence[] = $odds[rand(0, 4)];
            $sequence[] = $odds[rand(0, 4)];
            $si += 3;
        } else {
            // if the previous 2 elements have been odd numbers
            // or the next element is "odd-triple", add an even number
            // otherwise, pick a random number
            if ((isset($sequence[$si]) and $sequence[$si] % 2 === 1
                 and isset($sequence[$si - 1]) and $sequence[$si - 1] % 2 === 1)
             or (isset($shuffled_base[$i + 1]) and $shuffled_base[$i + 1] === 'odd-triple')
            ) {
                $sequence[] = $evens[rand(0, 3)];
            } else {
                $sequence[] = $digits[rand(0, 8)];
            }
            
            $si += 1;
        }
    }
    
    return $sequence;
}

function get_shuffled_sequence_base($sequence_base) {
    $base_count = count($sequence_base);
    $window_length = 20;
    // make sure that there are no more than 5 odd triples within
    // any 30-digit sequence
    // to do this, note that odd triples are currently encoded as a single
    // element of "odd-triple"
    // which means that if you look at a subset of 20 items, you should find
    // at most 5 instances of "odd-triple"
    do {
        shuffle($sequence_base);
        $shuffle_again = false;
        
        // prevent 2 triples from appearing back to back
        for ($i = 1; $i < $base_count; ++$i) {
            if ($sequence_base[$i] === 'odd-triple' and $sequence_base[$i - 1] === 'odd-triple') {
                $shuffle_again = true;
                continue 2;
            }
        }
        
        // prevent more than 5 triples from appearing within 20 "components" of each other
        $vals = array_count_values(array_slice($sequence_base, 0, $window_length));
        $triples = $vals['odd-triple'];
        
        for ($i = 0; $i < $base_count - $window_length; ++$i) {
            if ($triples > 5) {
                $shuffle_again = true;
                break;
            }
            
            if ($sequence_base[$i] === 'odd-triple') --$triples;
            if ($sequence_base[$i + $window_length] === 'odd-triple') ++$triples;
        }
    } while ($shuffle_again);
    
    return $sequence_base;
}

function verify_sequence($sequence) {
    if (!is_valid_sequence($sequence)) {
        echo implode(' ', $sequence);
        trigger_error('Failed to generate a valid sequence of digits, please examine algorithm.', E_USER_ERROR);
    }
}

function is_valid_sequence($sequence) {
    $prev_odds = 0;
    $triple_indices = [];
    $sequence_length = count($sequence);
    
    for ($i = 0; $i < $sequence_length; ++$i) {
        if ($sequence[$i] % 2 === 1) {
            ++$prev_odds;
            
            if ($prev_odds === 3) {
                $triple_indices[] = $i;
            } else if ($prev_odds === 4) {
                return false;
            }
        } else {
            $prev_odds = 0;
        }
    }
    
    if (count($triple_indices) !== 8) {
        return false;
    }
    
    for ($i = 0; $i < 3; ++$i) {
        if ($triple_indices[$i + 5] - $triple_indices[$i] < 21) {
            return false;
        }
    }
    
    return true;
}
