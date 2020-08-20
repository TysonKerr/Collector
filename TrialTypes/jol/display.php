<?php
    if ($text === '') { 
        $text = 'How likely are you to correctly recall this item on a later '
              . 'test?|Type your response on a scale from 0-100.';
    }
    
    $texts = explode('|', $text);
    $main_text = array_shift($texts);
    $sub_texts = '<p>' . implode('</p><p>', $texts) . '</p>';
?>
<div class="textcenter">
    <h3><?= $main_text; ?></h3>
    <?= $sub_texts ?>
</div>
  
<div class="textcenter">
    <input name="Response" type="text" class="forceNumeric textcenter collectorInput" required>
    <button class="collectorButton" id="FormSubmitButton">Submit</button>
</div>

<script>
"use strict";

const jol_input = document.querySelector("input[name='Response']");
const submit_button = document.getElementById("FormSubmitButton");

const validate_jol = function() {
    const val = parseFloat(jol_input.value);
    
    if (Number.isNaN(val) || val < 0 || val > 100) {
        submit_button.disabled = true;
    } else {
        submit_button.disabled = false;
    }
}

jol_input.addEventListener("input", validate_jol);
validate_jol();
</script>
