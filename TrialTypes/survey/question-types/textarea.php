<?php

return [
    'display' => function($rows) {
        foreach ($rows as $row) {
            echo '<div class="textarea-container">';
            echo "<div class='prompt-container'>{$row['Text']}</div>";
            $name = htmlspecialchars($row['Question Name'], ENT_QUOTES);
            $req = $row['Required'] ? 'required' : '';
            echo "<textarea class='survey-textarea' name='$name' $req></textarea>";
            echo '</div>';
        }
    }
];
