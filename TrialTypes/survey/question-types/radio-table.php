<?php

namespace survey_radio;

function get_inputs($name, $values, $required) {
    $name = htmlspecialchars($name, ENT_QUOTES);
    $inputs = [];
    
    foreach($values as $val) {
        $req = $required ? 'required' : '';
        $inputs[] = "<input type='radio' name='$name' value='$val' $req>";
    }
    
    return $inputs;
}

function get_row_headers($choices, $header_width = false) {
    if (!$header_width) {
        array_unshift($choices, '');
        return wrap($choices, 'div', 'table-header');
    }
    
    $headers = '<div class="table-header"></div>';
    
    foreach ($choices as $choice) {
        $headers .= "<div class='table-header' style='width:$header_width'>$choice</div>";
    }
    
    return $headers;
}

return [
    'display' => function($rows) {
        echo '<div class="survey-table">';
        $choices = array_values($rows[0]['Choices']);
        $header_width = $rows[0]['Settings']['header_width'] ?? false;
        $headers = get_row_headers($choices, $header_width);
        echo "<div class='table-row'>$headers</div>";
        
        foreach ($rows as $row) {
            $question = "<div class='table-question table-cell'>{$row['Text']}</div>";
            $inputs = get_inputs($row['Question Name'], $row['Values'], $row['Required']);
            $input_cells = implode_with_tags($inputs, 'label', 'table-cell');
            echo "<div class='table-row'>$question $input_cells</div>";
        }
        
        echo '</div>';
    }
];
