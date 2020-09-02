<?php

$scores = get_scores($settings);

foreach ($scores as $category => $val) {
    $text = str_replace('$' . $category, $val, $text);
}

?>
<div><?= $text ?></div>

<div class="textcenter">
    <button id="FormSubmitButton" class="collectorAdvance collectorButton">Next</button>
</div>
