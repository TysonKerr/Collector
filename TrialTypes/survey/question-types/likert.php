<?php

namespace survey_likert;

function get_likert_inputs($name, $choices, $values, $required) {
    $inputs = [];
    $name = htmlspecialchars($name, ENT_QUOTES);
    $req = $required ? 'required' : '';
    
    foreach ($choices as $i => $choice) {
        $value = $values[$i];
        $inputs[] = '<label class="likert-option">'
                  . "<input type='radio' name='$name' value='$value' $req>"
                  . "<span>$choice</span>"
                  . '</label>';
    }
    
    return $inputs;
}

function get_scale($input) {
    $scale = get_range($input);
    
    return count($scale) === 1
         ? '<div class="likert-sub-prompt">' . $scale[0] . '</div>'
         : '<div class="likert-scale">' . implode_with_tags($scale, 'div') . '</div>';
}

return [
    'display' => function($rows) {
        foreach ($rows as $row) {
            $inputs = get_likert_inputs(
                $row['Question Name'],
                $row['Choices'],
                $row['Values'],
                $row['Required']
            );
            $inputs = implode('', $inputs);
            echo '<div class="likert-container">';
            echo "<div class='likert-prompt'>{$row['Text']}</div>";
            echo get_scale($row['Text 2'] ?? '');
            echo "<div class='likert-inputs-container'>$inputs</div>";
            echo '</div>';
        }
    }
];
