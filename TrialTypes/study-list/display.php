<div>
  <?php foreach ($stimuli as $stim): ?>
    <div class="trial-container">
      <div class="study-phase">
        <div style="font-size: 200%;"><?= $stim['Answer'] ?></div>
        
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
