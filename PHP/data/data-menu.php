<?php
    namespace data;
    echo get_link('Links/css/data-menu.css');
    echo get_link('Links/js/data-menu.js');

?><div id="menu-header"><h1>Data Menu <?= get_logout_button() ?></h1></div><?php
?><form method="post" id="data-menu" target="_blank">
<div id="controls">
    <button type="submit" name="download" value="preview" >preview</button>
    <button type="submit" name="download" value="download">download</button>
</div>
<div id="data-columns" class="data-menu-block"><?php

echo get_block_header('data-columns-header', 'Columns');
$columns = get_data_columns_for_menu();
$col_group_names = ['Trial Info' => 'c', 'Login' => 'l', 'Metadata' => 'm'];
echo get_col_group($columns['General'], 'General', '', false, $col_group_names);
unset($columns['General']);

foreach ($columns as $post => $cols) {
    $header = $post === 0 ? 'Trial Columns' : "Post $post Columns";
    $prefix = $post === 0 ? '' : "Post $post ";
    
    echo get_col_group($cols, $header, $prefix, true);
}

?></div><?php
?><div id="users" class="data-menu-block"><?php

$users = get_users_for_menu();
echo '<div class="user-list">';
echo get_user_list_group_row('Participants', 3);

foreach ($users as $exp => $exp_users) {
    echo get_user_list_group_row(clean_exp_name($exp), 2);
    
    foreach (['Finished', 'Unfinished'] as $status) {
        if (count($exp_users[$status]) === 0) continue;
        
        echo get_user_list_group_row($status, 1);
        
        foreach ($exp_users[$status] as $user => $info) {
            echo get_user_checkbox_row($user, $exp, $info);
        }
    }
}

echo '</div>';

?></div><?php
