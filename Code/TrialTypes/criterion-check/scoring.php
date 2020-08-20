<?php

$data = $_POST;

require __DIR__ . '/definitions.php';
include $_PATH->get('Shuffle Functions');

/** for reference, trials are stored in an array like this:
 * 
 * $trials = [
 *     0 => [
 *         'Stimuli'   => ['Cue'      => 'a',   'Answer'     => 'apple', ...],
 *         'Procedure' => ['Item'     => '2',   'Max Time'   => 'user', ...],
 *         'Response'  => ['Accuracy' => '100', 'lenientAcc' => '1', ...],
 *     ],
 *     1 => [...],
 *     ...
 *  ];
 */

if (!isset($settings)) $settings = '';

$settings = get_settings($settings);
$trials = get_previous_trials($settings['trials']);
$lenient_accs = get_trial_values($trials, 'Response', 'lenientAcc');
$lenient_accs = get_flat_array($lenient_accs);
$score = array_sum($lenient_accs);
$score_average = $score / count($lenient_accs);

if ($score_average < $settings['criterion']) {
    redo_trials($trials, $settings);
}
