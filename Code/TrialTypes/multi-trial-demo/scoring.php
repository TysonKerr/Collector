<?php

$data = $_POST;

$expanded_data = [
    'Response' => $data['Response'],
    'Trials_With_Response' => explode(',', $data['Trials_With_Response']),
];

$expanded_data += get_multi_scoring($expanded_data['Response'], $answer, $_CONFIG->lenient_criteria);

record_data_to_multiple_rows($data, $expanded_data, $keyMod, true);
