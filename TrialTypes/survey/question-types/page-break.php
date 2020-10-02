<?php

return [
    'display' => function($rows) {
        foreach ($rows as $row) {
            // close the previously opened div with class "survey-page"
            // and open another one (should match survey's display.php)
            echo   '<div class="page-break-container">'
               .     '<button type="submit">Submit</button>'
               .   '</div>'
               . '</div>'
               . '<div class="survey-page">';
        }
    }
];
