<?= link_trial_type_file('study', 'style.css') ?>

<div class="study-pair">
    <span><?= $cue;    ?></span>
    <span><?= ':'      ?></span>
    <span><?= $answer; ?></span>
    
    <span><?= $cue;    ?></span>
    <span><?= ':'      ?></span>
    <span><input name="Response" type="text" class="collectorInput"></span>
</div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>
