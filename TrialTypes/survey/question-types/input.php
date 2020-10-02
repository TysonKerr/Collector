<?php

return [
    'display' => function($rows) {
        foreach ($rows as $row) {
            $name = htmlspecialchars($row['Question Name'], ENT_QUOTES);
            $req = $row['Required'] ? 'required' : '';
            $settings = $row['Settings'];
            $settings['type'] = $settings['type'] ?? 'text';
            $attrs = '';
            
            foreach ($settings as $key => $val) {
                $attrs .= " $key='$val'";
            }
            
            echo '<label class="input-container">';
            echo "<span>{$row['Text']}</span>";
            echo "<input name='$name' $req $attrs>";
            echo '</label>';
        }
    }
];
