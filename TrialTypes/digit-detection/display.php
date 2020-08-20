<?php

if (count($stimuli) !== 20) trigger_error('Must have exactly 20 stimuli for this trial', E_USER_ERROR);

$digit_sequence = get_digit_sequence();
$audio_root = get_trial_type_dir_url($trial_type) . '/digits';

echo '<div>';

for ($i = 1; $i <= 9; ++$i) {
    echo "<audio class='digit$i'><source src='$audio_root/$i.mp3' type='audio/mpeg'></audio>";
}

echo '</div>';

echo '<div id="multi-trial-container">';

foreach ($stimuli as $stim_row) {
    echo '<div class="trial-container">';
    echo $stim_row['Answer'];
    echo '</div>';
}

echo '</div>';

?>

<button id="start-btn" type="button">Start</button>

<div class="hidden">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
    <input type="hidden" name="Sequence" value="<?= implode(',', $digit_sequence) ?>">
</div>

<script>
    window.digit_sequence = <?= json_encode($digit_sequence) ?>;
</script>
