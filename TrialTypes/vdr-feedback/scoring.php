<?php

// just so that we can confirm the values shown during this trial,
// i am going to copy the values calculated into the response columns

$scores = get_word_scores($stimuli);

foreach ($scores as $category => $val) {
    $data[$category] = $val;
}
