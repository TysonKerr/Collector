<?php

return [
    'display' => function($rows) {
        foreach ($rows as $row) {
            echo "<div class='prompt-container'>{$row['Text']}</div>";
        }
    }
];
