<?php

require __DIR__ . '/download-definitions.php';

$output = get_output_fn(filter_input(INPUT_POST, 'download'));

$users      = get_users_from_input();
$trial_cols = get_cols_from_input('c');
$login_cols = get_cols_from_input('l');
$meta_cols  = get_cols_from_input('m');

$cols = array_merge(
    $trial_cols,
    get_array_with_prefixed_vals($login_cols, 'Login '),
    get_array_with_prefixed_vals($meta_cols,  'Meta ')
);

// if they are just getting the metadata
// set a flag indicating that the username should be added to the output
if (count($trial_cols) === 0 and count($login_cols) === 0) {
    $add_username_column = true;
    array_unshift($cols, 'Username');
} else {
    $add_username_column = false;
}

$output($cols);

$exps = sort_users_into_experiments($users);

foreach ($exps as $dir => $user_list) {
    $meta = count($meta_cols) > 0 ? get_meta_from_dir($dir) : [];
    
    if (count($trial_cols) > 0) {
        $login = count($login_cols) > 0 ? get_login_from_dir($dir, 'ID') : [];
        
        foreach ($user_list as $username) {
            $filename = ROOT . "/Data/$dir/Output/Output_$username.csv";
            $data = read_csv($filename);
            
            foreach ($data as $row) {
                $sorted = [];
                $id = $row['ID'];
                
                foreach ($trial_cols as $col) {
                    $sorted[] = isset($row[$col]) ? $row[$col] : '';
                }
                
                foreach ($login_cols as $col) {
                    $sorted[] = isset($login[$id][$col]) ? $login[$id][$col] : '';
                }
                
                foreach ($meta_cols as $col) {
                    $sorted[] = isset($meta[$username][$col]) ? $meta[$username][$col] : '';
                }
                
                $output($sorted);
            }
        }
    } else {
        $login = count($login_cols) > 0 ? get_login_from_dir($dir, 'Username') : [];
        
        foreach ($user_list as $username) {
            $sorted = [];
            
            if ($add_username_column) $sorted[] = $username;
            
            foreach ($login_cols as $col) {
                $sorted[] = isset($login[$username][$col]) ? $login[$username][$col] : '';
            }
            
            foreach ($meta_cols as $col) {
                $sorted[] = isset($meta[$username][$col]) ? $meta[$username][$col] : '';
            }
            
            $output($sorted);
        }
    }
}
