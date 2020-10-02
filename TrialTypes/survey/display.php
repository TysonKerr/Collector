<?php

$filename = get_survey_filename($cue ?? false, $settings);
$survey = read_survey($filename);
$types = get_survey_question_types();
$i = 0;

?><div class="survey-container">
    <div class="survey-page current-page"><?php

while (isset($survey[$i])) {
    $rows = [$survey[$i]];
    $type = $survey[$i]['Type'];
    $choices = $survey[$i]['Choices'];
    ++$i;
    
    while (isset($survey[$i])) {
        if ($survey[$i]['Type'] !== $type or $survey[$i]['Choices'] !== $choices) {
            break;
        }
        
        $rows[] = $survey[$i];
        ++$i;
    }
    
    $types[strtolower($type)]['display']($rows);
}

    ?><div class="page-break-container">
        <button type="submit" id="FormSubmitButton">Submit</button>
      </div>
    </div>
</div><?php
