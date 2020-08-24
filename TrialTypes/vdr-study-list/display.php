<?php
    echo link_trial_type_file('study', 'style.css');
    
    $defaults = [
        'study' => 2,
        'isi' => 0.5
    ];
    
    $settings = parse_settings($settings, $defaults);
?>

<div>
  <?php foreach ($stimuli as $stim): ?>
    <div class="trial-container">
      <div class="study-phase">
        <div class="study-pair">
          <span><?= $stim['Answer'] ?></span>
          <span>:</span>
          <span><?= $stim['Value'] ?></span>
        </div>
        
        <div class="textcenter">
          <button type="submit" class="collectorButton collectorAdvance">Next</button>
        </div>
      </div>
      
      <div class="isi-phase"></div>
    </div>
  <?php endforeach; ?>
</div>

<script>
    multi_trial.phases = ["study", "isi"];
</script>
