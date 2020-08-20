<?php
    echo link_file(__DIR__ . '/custom_style.css');
    shuffle_trial_items($currentTrial);

    $cues = explode('|', $currentTrial['Stimuli']['Cue']);
    $answers = explode('|', $currentTrial['Stimuli']['Answer']);

    foreach ($cues as $i => $this_cue): ?>
<div class="trial">
    <div class="study">
        <span class="study-left"   ><?= $this_cue    ?></span>
        <span class="study-divider"><?= ":"          ?></span>
        <span class="study-right"  ><?= $answers[$i] ?></span>
    </div>

    <div class="study alignToInput">
        <span class="study-left"   ><?= $this_cue    ?></span>
        <span class="study-divider"><?= ":"          ?></span>
        <div class="study-right">
            <input name="Response[]" type="text" value="" class="copybox collectorInput" autocomplete="off" />
        </div>
    </div>
</div>
<?php endforeach;?>

<div class="hidden">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
    <input type="hidden" name="Spacebar_Timestamps">
    <input type="hidden" name="Trials_With_Response">
</div>

<?= link_file(__DIR__ . '/custom_script.js') ?>

<script>
COLLECTOR.experiment["<?= $trialType ?>"] = initialize_trial;
</script>
