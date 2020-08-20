<?php

$settings_parsed = json_decode('{' . $settings . '}', true);

if ($settings_parsed === null) {
    exit('error: Settings column invalid, contents: ' . htmlspecialchars($settings, ENT_QUOTES));
}

if (isset($settings2)) {
    $settings2_parsed = json_decode('{' . $settings2 . '}', true);
    
    if ($settings2_parsed !== null) {
        $settings_parsed = array_merge($settings_parsed, $settings2_parsed);
    }
}

$status_html = '';

if (isset($settings_parsed['status_progress'])) {
    $progress = $settings_parsed['status_progress'] * 100 . '%';
    
    if (isset($settings_parsed['status_message'])) {
        $message = $settings_parsed['status_message'];
    } else {
        $message = '';
    }
    
    $status_html = "
    <div class='status_bar_container'>
      progress:
      <div class='status_bar'>
        <div class='status_progress' style='width: $progress'></div>
        <div class='status_message'>$message</div>
      </div>
    </div>";
}

// choose 9 random stimuli
$stim = $_SESSION['Stimuli'];

unset($stim[0]); // damn padding
unset($stim[1]);

$sets = explode(',', $settings_parsed['sets']);
$use_stim_within_subgroup = isset($settings_parsed['within_subgroup']) and $settings_parsed['within_subgroup'];

foreach ($stim as $i => $stim_row) {
    if (!in_array($stim_row['Similarity Judgment'], $sets)) {
        unset($stim[$i]);
    }
}

shuffle($stim);

$query = null;
$stim_row = null; // will be used after foreach loop to get last examined row
$subgroup = null;
$references = [];
$used_cues = [];

// get query
if (isset($settings_parsed['query'])) {
    foreach ($stim as $i => $stim_row) {
        if ($stim_row['Cue'] === $settings_parsed['query']) {
            $query = [$stim_row['Cue'], $stim_row['Answer']];
            // unset($stim[$i]); // need this row to reselect item as reference
            break;
        }
    }
} elseif (isset($settings_parsed['ref'])) {
    foreach ($stim as $i => $stim_row) {
        if ($stim_row['Cue'] === $settings_parsed['ref']) continue;
        
        $query = [$stim_row['Cue'], $stim_row['Answer']];
        // unset($stim[$i]);
        break;
    }
} else {
    $stim_row = array_shift($stim);
    $query = [$stim_row['Cue'], $stim_row['Answer']];
}

$used_cues[] = $query[0];

// update stim if only using within subgroups
if ($use_stim_within_subgroup) {
    $subgroup = $stim_row['Similarity Judgment Subgroup'];
    
    foreach ($stim as $i => $stim_row) {
        if ($stim_row['Similarity Judgment Subgroup'] !== $subgroup) {
            unset($stim[$i]);
        }
    }
}

shuffle($stim);

// get references
if (isset($settings_parsed['ref'])) {
    foreach ($stim as $i => $stim_row) {
        if ($stim_row['Cue'] === $settings_parsed['ref']) {
            $references[] = [$stim_row['Cue'], $stim_row['Answer']];
            $used_cues[] = $stim_row['Cue'];
            unset($stim[$i]);
            break;
        }
    }
}

foreach ($stim as $stim_row) {
    if (in_array($stim_row['Cue'], $used_cues)) continue;
    
    $references[] = [$stim_row['Cue'], $stim_row['Answer']];
    $used_cues[] = $stim_row['Cue'];
    
    if (count($references) > 7) break;
}

if (count($references) !== 8) {
    exit('error: not enough stimuli found for sets ' . $settings_parsed['sets']
         . ($use_stim_within_subgroup ? ' and only using stim within subgroups' : ''));
}

shuffle($references);

$imgs = [];
$answers = [];

foreach ($references as $i => $ref) {
    if ($i === 4) {
        $imgs[] = $query[0];
        $answers[] = $query[1];
    }
    
    $imgs[]    = $ref[0];
    $answers[] = $ref[1];
}

// show the 8 stimuli and the target img in a 3x3 grid, with the target in the center
$show_img = function($file, $answer, $class = '') {
    echo "<img src='../Experiment/$file' class='$class' data-answer='$answer'>";
};

echo '<div class="image_container">';

$flip_ref = isset($settings_parsed['flip_ref']) and $settings_parsed['flip_ref'];

for ($i = 0; $i < 9; ++$i) {
    $class = $i === 4 ? 'comparison_source' : '';
    
    if ($flip_ref
        and $i !== 4
        and isset($settings_parsed['ref'])
        and $imgs[$i] === $settings_parsed['ref']) {
        $class .= 'flip';
    }
    
    $show_img($imgs[$i], $answers[$i], $class);
}

echo '</div>';

echo $status_html;

?>
<div class="hidden">
    <input type="hidden" name="Response1">
    <input type="hidden" name="Response1_RT">
    <input type="hidden" name="Response2">
    <input type="hidden" name="Response2_RT">
    <input type="hidden" name="Option_Not_Selected_1">
    <input type="hidden" name="Option_Not_Selected_2">
    <input type="hidden" name="Option_Not_Selected_3">
    <input type="hidden" name="Option_Not_Selected_4">
    <input type="hidden" name="Option_Not_Selected_5">
    <input type="hidden" name="Option_Not_Selected_6">
    <input type="hidden" name="Target_Cue"    value="<?= $imgs[4] ?>">
    <input type="hidden" name="Target_Answer" value="<?= $answers[4] ?>">
    <input type="hidden" name="All_Answers" value="<?= implode(',', $answers) ?>">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Next</button>
</div>

<?php

require __DIR__ . '/catchBadResponses.php';
$min_time_required_before_responding = get_min_time_required_before_responding();

?><script>
    min_time_before_response = <?= $min_time_required_before_responding ?>;
</script>

<div class="rushing_warning hidden"><div>
    <p>You appear to be going through these trials very quickly.</p>
    </p>Please make sure you are carefully considering all options when making your choices.</p>
    <button type="button" disabled class="next_button disabled">Next</button>
    <input type="hidden" name="Gave_Warning" value="no">
</div></div>
