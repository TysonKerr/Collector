<?php

$data['Score'] = get_score($settings);

if ($data['Score'] < $settings['criterion']) {
    $data['Passed'] = 'Failed';
    
    if (is_numeric($settings['max_iterations'])
        and isset($repeat_count) and is_numeric($repeat_count)
        and ($repeat_count + 1) >= $settings['max_iterations']
    ) {
        give_fail_trial_type($settings['trial_type_on_fail']);
    } else {
        repeat_trials($settings);
    }
} else {
    $data['Passed'] = 'Passed';
}