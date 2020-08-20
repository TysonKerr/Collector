<?php

link_trial_type_file($trial_type, 'jquery-ui.min.js');

$list_html = '<ul id="sortList">';

foreach ($stimuli as $stim_row) {
    $ans = $stim_row['Answer'];
    $inp = "<input name='Response[]' type='hidden' readonly value='$ans'>";
    $list_html .= "<li>$inp$ans</li>";
}
?>

<style>
</style>

<section class="vcenter">
  <div class="textcenter"><div class="topMsg"><?= $text; ?></div></div>
  
  <div class="listContainer">
    <?= $list_html ?>
  </div>

  <div class="textcenter">
    <input class="collectorButton collectorAdvance" id="FormSubmitButton" type="submit" value="Submit">
  </div>
</section>

<script>
$(function() {
    $("#sortList").sortable();
    $("#sortList").disableSelection();
});
  
function on_trial_start() {
    $("#sortList").width($("#sortList").width()+15);
}
</script>