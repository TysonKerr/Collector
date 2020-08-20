<?php
    $option_texts = explode('|', $option_text);
    $option_vals  = explode('|', $options);
?>

<p class="question"><?= $text ?></p>

<div class="option-list">
<?php
for ($i = 0; $i < count($option_vals); ++$i) {
    $val = htmlspecialchars($option_vals[$i], ENT_QUOTES);
    $opt_text = isset($option_texts[$i]) ? $option_texts[$i] : $option_vals[$i];
    $opt_text = htmlspecialchars($opt_text);
    $input = "<input type='radio' name='Response' value='$val'>";
    echo "<label><span>$input</span> <span>$opt_text</span></label>";
}
?>
</div>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>
