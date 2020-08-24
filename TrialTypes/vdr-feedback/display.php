<?php
    /**
     * This is designed to give feedback for the vdr-free-recall trial type
     *
     * To use this trial, set the "Stim Rows" column to be the same as 
     * whatever you used for the vdr-free-recall trial
     *
     * The Text column will have its contents modified, substituting values
     * for the following keywords:
     *
     * $possibleAcc: the number of words they could have recalled
     * $possibleVal: the max value they could have received
     * $lenientAcc: number of words they recalled with at most 1 typo
     * $lenientVal: value of the words they recalled with at most 1 typo
     * $strictAcc: number of words they recalled exactly, with no typos
     * $strictVal: value of the words they recalled exactly
     *
     * example Text column:
     * "You received $lenientVal points out of $possibleVal possible."
     */
    $scores = get_word_scores($stimuli);
    
    foreach ($scores as $category => $val) {
        $text = str_replace('$' . $category, $val, $text);
    }
?>

<div><?= $text ?></div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>
