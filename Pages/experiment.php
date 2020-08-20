<?php

require ROOT . '/PHP/definitions/experiment.php';

check_if_logged_in();
check_my_eligibility();
check_if_experiment_is_done();

if (has_submitted_data_to_record()) {
    record_submitted_data();
}

// set up variables the trial type might use
$procedure = get_current_procedure();
$stimuli = get_stimuli_rows($procedure['Stim Rows']);
$trial_values = get_trial_values($procedure, $stimuli);
extract($trial_values, EXTR_SKIP|EXTR_REFS);

if (is_file(get_trial_file($trial_type, 'definitions.php'))) {
    require get_trial_file($trial_type, 'definitions.php');
}

// run the trial
link_trial_type_file($trial_type, 'style.css');

?><form method="post" class="invisible exp-form" id="content" autocomplete="off"><?php
    require get_trial_file($trial_type, 'display.php');
?></form><?php

link_trial_type_file($trial_type, 'script.js');
send_trial_values_to_javascript($trial_values);
