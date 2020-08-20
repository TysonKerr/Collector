<?php

// sample function
// this file must define a function called "generate_procedure" to have an effect
// it should take in the combined procedure files of a condition
// and output the procedure you want
// after you give the proc data back, login will run the shuffle functions
/*
function generate_procedure($proc) {
    $output = [];
    
    foreach ($proc as $i => $row) {
        $row['Index'] = $i;
        
        if ($row['Stim Row'] === '*generate') {
            for ($j = 0; $j < 4; ++$j) {
                $row['Stim Row'] = $i + $j;
                $output[] = $row;
            }
        } else {
            $output[] = $row;
        }
    }
    
    return $output;
}
//*/
