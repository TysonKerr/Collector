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
$references[] = $query;

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

for ($i = 0; $i < 9; ++$i) {
    if ($i === 4) {
        $class = 'comparison_source';
    } else if ($imgs[$i] === $query[0]) {
        $class = 'flip';
    } else {
        $class = '';
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

<script>

var resp1    = document.querySelector("input[name='Response1']");
var resp1_rt = document.querySelector("input[name='Response1_RT']");
var resp2    = document.querySelector("input[name='Response2']");
var resp2_rt = document.querySelector("input[name='Response2_RT']");
var start = 0;

var give_warning_for_incorrect_response = function() {
    document.querySelector(".image_container").classList.add("hidden");
    document.querySelector(".status_bar_container").classList.add("hidden");
    document.querySelector(".incorrect_warning").classList.remove("hidden");
    document.querySelector("input[name='Gave_Warning']").value = "yes";
    COLLECTOR.timer(3, function() {
        let btn = document.querySelector(".next_button");
        btn.classList.remove("disabled");
        btn.disabled = false;
        btn.addEventListener("click", submit_form);
    });
};

var made_correct_response = function() {
    let answer = document.querySelector("input[name='Target_Answer']").value;
    
    if (resp1.value !== answer && resp2.value !== answer) {
        return false;
    } else {
        return true;
    }
};

var record_unselected_answers = function() {
    let i = 1;
    let ans1 = resp1.value;
    let ans2 = resp2.value;
    
    document.querySelectorAll(".image_container img").forEach(img_node => {
        if (img_node.classList.contains("comparison_source")) return;
        
        let ans = img_node.dataset['answer'];
        
        if (ans === ans1 || ans === ans2) return;
        
        document.querySelector(`input[name='Option_Not_Selected_${i++}']`).value = ans;
    });
};

var submit_form = function() {
    $("#FormSubmitButton").click();
};

var click_img = function(img_node) {
    if (img_node.classList.contains("comparison_source")) return;
    if (img_node.classList.contains("selected")) return;
    
    let answer = img_node.dataset['answer'];
    let rt = Date.now() - start;
    
    if (!resp1.value) {
        resp1.value = answer;
        resp1_rt.value = rt;
        img_node.classList.add("selected");
    } else {
        resp2.value = answer;
        resp2_rt.value = rt;
        record_unselected_answers();
        
        if (made_correct_response()) {
            submit_form();
        } else {
            give_warning_for_incorrect_response();
        }
    }
};

document.querySelectorAll("img").forEach(
    node => node.addEventListener("click", e => click_img(e.target))
);

window.addEventListener("load", function() {
    start = Date.now();
});

document.addEventListener("contextmenu", e => {
    e.preventDefault();
});

</script>

<div class="incorrect_warning hidden"><div>
    <p>The previous trial contained an image that was a flipped version of the center image. Thus, you should have chosen this mirror image as one that is most similar to the center image. If you did not, it may be an indication that you are going through the experiment too quickly. Please slow down and make sure you are considering all images when making your choices.</p>
    <button type="button" disabled class="next_button disabled">Next</button>
    <input type="hidden" name="Gave_Warning" value="no">
</div></div>