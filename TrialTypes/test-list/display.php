<?= link_trial_type_file('study', 'style.css') ?>

<div>
  <?php foreach ($stimuli as $stim): ?>
    <div class="trial-container">
      <div class="test-phase">
        <div class="study-pair">
          <span><?= $stim['Cue'] ?></span>
          <span>:</span>
          <span><input name="Response[]" type="text" class="collectorInput"></span>
        </div>
        
        <div class="textcenter">
          <button type="submit" class="collectorButton collectorAdvance">Next</button>
        </div>
      </div>
      
      <div class="isi-phase"></div>
    </div>
  <?php endforeach; ?>
</div>
