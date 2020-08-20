<?php
$questions = getFromFile(__DIR__ . '/questions.csv', false);
$prompts = [
    'Typical or average experiences from earliest memory to age 12...',
    'Typical or average experiences from earliest memory to age 18...',
    'Typical or average experiences from earliest memory to age 18...',
    'Typical or average experiences from earliest memory to age 18...',
    'Typical or average experiences from earliest memory to age 18...'
];

$get_category_questions = function($category) use ($questions) {
    $category_questions = [];
    
    foreach ($questions as $i => $row) {
        if ($row['Category'] == $category) {
            $category_questions[] = array_merge($row, ['Index' => $i + 1]);
        }
    }
    
    return $category_questions;
};

$get_radio = function($index, $value) {
    $input = "<input type='radio' name='Response_$index' value='$value' required>";
    return "<label class='survey-input-container'>$input</label>";
    // return "<label class='survey-option'>$input <span>$value</span></label>";
};

$display_questions = function($category) use ($get_category_questions, $get_radio) {
    $category_questions = $get_category_questions($category);
    
    $age = $category == 1 ? '12' : '18';
    $prompt = "Typical or average experiences from earliest memory to age $age...";
    
    echo '<table class="survey-table">';
    echo "<thead><tr> <th colspan='2'>$prompt</th> <th>Yes</th> <th>No</th></tr></thead>";
    echo '<tbody>';
    
    foreach ($category_questions as $row) {
        $i = $row['Index'];
        $yes = $get_radio($i, '1');
        $no = $get_radio($i, '0');
        echo "<tr> <td>{$row['Index']}</td> <td>{$row['Question']}</td>"
           . "<td>$yes</td> <td>$no</td></tr>";
    }
    
    echo '</tbody></table>';
}

?>

<style>
.section-prompt {
    font-weight: bold;
    text-decoration: underline;
}

h4 { text-align: center; }

.survey-table { 
    border-collapse: collapse;
}
.survey-table td, .survey-table th {
    border: 1px solid black;
    padding: 8px 0.5em;
}

.survey-table tbody td:first-child {
    border-right-width: 0;
}
.survey-table tbody td:nth-child(2) {
    border-left-width: 0;
}
.survey-table tbody td:first-child::after {
    content: ".";
}
.survey-table tbody td:nth-child(3),
.survey-table tbody td:nth-child(4) {
    padding: 0;
    vertical-align: middle;
    text-align: center;
}

.survey-table tbody tr {
    --bg: white;
    background-color: var(--bg);
}
.survey-table tbody tr:nth-child(odd) {
    --bg: #d0d0d0;
}

* { box-sizing: border-box; }

.survey-option {
    display: block;
    cursor: pointer;
    position: relative;
    text-align: center;
}
.survey-option input {
    position: absolute;
    opacity: 0.01;
    width: 1px;
    pointer-events: none;
}

.survey-option span {
    border: 2px solid var(--bg);
    border-radius: 25px;
    display: inline-block;
    padding: 3px 8px;
    width: 30px;
}

.survey-option:hover span {
    border-color: #00dd00 !important;
}

.survey-option input:checked + span {
    border-color: #008000;
}

.survey-input-container {
    display: block;
    padding: 5px;
    text-align: center;
}
</style>

<h4>Questionnaire of Unpredictability in Childhood (QUIC)</h4>
<h4>Glynn et al., (2019)</h4>

<p><strong>Instructions and Items:</strong></p>

<p>This set of questions asks about your childhood experiences. When we say
parents,we mean whoever in your life fills that role for you(e.g. biological
parents, step parents, grandparents, foster parents). This could be one person,
or this could be multiple people.
<br>Please list those people's relationship to you below:</p>

<div id="relationships-area">
<?php
    for ($i = 1; $i <= 5; ++$i) {
        $required = $i === 1 ? 'required' : '';
        echo "<input type='test' name='Relationship_$i' $required>";
    }
?>
</div>

<p>First,we are going to ask about a specific part of your childhood, which is
when you were less than 12 years old. These answers should be based on your own
memories <u>prior to the age of 12</u>, not on things you later learned from
your parents or others.</p>

<p class="section-prompt">
    Please answer these questions based on your typical or average experiences.
</p>

<?= $display_questions('1') ?>

<p>Now we are going to ask you about your experiences from <u>birth to age 18</u>
(or your whole life, if you are less than 18 years old). Again, this should be
based on your own memories prior to the age of 18, not on things you later
learned from your parents or others.</p>

<p class="section-prompt">
    Please answer these questions based on your typical or average experiences.
</p>

<?= $display_questions('2') ?>

<p>For the next set of questions, we are asking if this is true for at least one
of your parents.</p>

<p class="section-prompt">
    Please answer these questions based on your typical or average experiences.
</p>

<?= $display_questions('3') ?>

<p>For the next set of questions, we are asking about your household. If you
lived in more than one household, please answer about the household in which you
spent the most time.</p>

<p class="section-prompt">
    Please answer these questions based on your typical or average experiences.
</p>

<?= $display_questions('4') ?>

<div class="textcenter">
    <button class="collectorButton collectorAdvance" id="FormSubmitButton">Submit</button>
</div>
