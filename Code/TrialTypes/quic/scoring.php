<?php

$data = $_POST;

$rubric = [
    'QUIC_Parental_Monitoring' => ['1', '3r', '4r', '5r', '6r', '7r', '9r', '10r', '14r'],
    'QUIC_Parental_Predictability' => ['2', '8r', '11', '12', '15r', '16', '17r', '31', '32', '33', '34', '35'],
    'QUIC_Parental_Environment' => ['18', '19', '21', '22', '28', '29', '30'],
    'QUIC_Physical_Environment' => ['13', '20', '26', '27', '36r', '37', '38'],
    'QUIC_Safety' => ['23', '24', '25']
];

$scores = [];

foreach ($rubric as $subscale => $questions) {
    $score = 0;
    
    foreach ($questions as $q) {
        if (substr($q, -1) === 'r') {
            $reverse = true;
            $index = substr($q, 0, -1);
        } else {
            $reverse = false;
            $index = $q;
        }
        
        $resp = (int) $data["Response_$index"];
        
        if ($reverse) $resp = 1 - $resp;
        
        $score += $resp;
    }
    
    $scores[$subscale] = $score;
    $data[$subscale] = $score;
}

$scores['QUIC_Overall'] = array_sum($scores);
$data['QUIC_Overall'] = $scores['QUIC_Overall'];
$responses = [];

for ($i = 1; $i <= 38; ++$i) {
    $responses[] = $data["Response_$i"];
    unset($data["Response_$i"]);
}

$data['Response'] = implode(',', $responses);

// save_extra_metadata($_SESSION['Username'], $scores);
