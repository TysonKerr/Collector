<?php

echo "<div class='instructions'>$text</div>";
$number_of_inputs = get_number_of_inputs($settings, $stimuli);
echo '<div><div class="input-container">';

if ($number_of_inputs === 1) {
    echo '<textarea rows="20" cols="55" name="Response" wrap="soft"></textarea>';
} else {
    for ($i = 0; $i < $number_of_inputs; ++$i) {
        echo '<input type="text" name="Response[]">';
        
        if ($i % 4 === 3) echo '<br>';
    }
}

echo '</div></div>';

?>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
