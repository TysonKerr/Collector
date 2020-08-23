<?php
namespace data;

function define_constants() {
    if (getenv('REDIRECT_D_EXP')) {
        define('D_EXP', getenv('REDIRECT_D_EXP'));
        define('URL_TO_ROOT', '../..');
    } else {
        define('URL_TO_ROOT', '..');
    }
}

## Columns

function get_data_columns_for_menu() {
    $columns = get_data_columns();
    $trial_columns = get_trial_cols_sorted_into_post_levels($columns['trial']);
    $trial_columns = get_trial_cols_with_sub_categories($trial_columns);
    $gen_trial_cols = extract_gen_trial_cols($trial_columns);
    $gen_trial_cols = custom_sort_trial_misc_cols($gen_trial_cols);
    $gen_cols = [
        'Trial Info' => $gen_trial_cols,
        'Login'      => $columns['login'],
        'Metadata'   => $columns['metadata']
    ];
    $trial_columns['General'] = $gen_cols;
    return $trial_columns;
}

## getting all the raw data

function get_data_columns() {
    $data_dirs = get_data_dirs();
    $cols = ['metadata' => [], 'trial' => [], 'login' => []];
    
    foreach ($data_dirs as $dir) {
        $cols['metadata'][] = get_metadata_columns($dir);
        $cols['trial'][]    = get_trial_columns($dir);
        $cols['login'][]    = get_login_columns($dir);
    }
    
    foreach ($cols as $category => $cols_2d_array) {
        $cols[$category] = array_merge_unique($cols_2d_array);
    }
    
    return $cols;
}

function get_data_dirs() {
    if (defined('D_EXP')) {
        $path = ROOT . '/Data/' . D_EXP . '-Data';
        
        return is_dir($path) ? [$path] : [];
    } else {
        return get_dir_entries(ROOT . '/Data', 2, true);
    }
}

function get_dir_entries($dir, $type = 1, $merge_dir = false) {
    if (!is_dir($dir)) return [];
    
    $entries = [];
    
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' or $entry === '..') continue;
        
        $path = "$dir/$entry";
        
        if (($type <= 1 and is_file($path))
         or ($type >= 1 and is_dir($path))
        ) {
            $entries[] = $merge_dir ? $path : $entry;
        }
    }
    
    return $entries;
}

function get_metadata_columns($dir) {
    $data = get_exp_metadata($dir);
    $headers = [];
    
    foreach ($data as $user) {
        foreach ($user as $header => $_) {
            $headers[$header] = true;
        }
    }
    
    return array_keys($headers);
}

function get_exp_metadata($dir) {
    static $data = [];
    
    if (!isset($data[$dir])) {
        $filename = "$dir/metadata.csv";
        $data[$dir] = read_file_as_metadata($filename);
    }
    
    return $data[$dir];
}

function get_login_columns($dir) {
    $filename = "$dir/login.csv";
    
    if (!is_file($filename)) return [];
    
    $first_line = get_first_non_empty_line($filename);
    
    return $first_line ? $first_line : [];
}

function get_first_non_empty_line($filename, $encoding = false) {
    $handle = fopen($filename, 'r');
    
    if (!$encoding) $encoding = get_config('encoding');
    
    $headers = get_next_csv_line($handle, $encoding);
    fclose($handle);
    
    return $headers ? $headers : [];
}

function get_trial_columns($dir) {
    $files = get_dir_entries("$dir/Output", 0, true);
    $encoding = get_config('encoding');
    $columns = [];
    
    foreach ($files as $filename) {
        $first_line = get_first_non_empty_line($filename, $encoding);
        
        if ($first_line) $columns += array_flip($first_line);
    }
    
    return array_keys($columns);
}

function array_merge_unique($arrs) {
    $merged = [];
    
    foreach ($arrs as $arr) {
        $merged += array_flip($arr);
    }
    
    return array_keys($merged);
}

## arranging the columns

function get_trial_cols_sorted_into_post_levels($trial_cols) {
    $trial_cols_by_level = [];
    
    foreach ($trial_cols as $col) {
        if (preg_match('/^Post (\\d+) (.+)/', $col, $matches)) {
            $trial_cols_by_level[$matches[1]][] = $matches[2];
        } else {
            $trial_cols_by_level[0][] = $col;
        }
    }
    
    ksort($trial_cols_by_level);
    return $trial_cols_by_level;
}

function get_trial_cols_with_sub_categories($cols) {
    foreach ($cols as $post => $trial_cols) {
        $cols[$post] = get_sorted_trial_cols_of_post_level($trial_cols);
    }
    
    return $cols;
}

function get_sorted_trial_cols_of_post_level($cols) {
    $groups = ['Misc' => [], 'Proc' => [], 'Stim' => [], 'Resp' => []];
    
    foreach ($cols as $col) {
        $start = substr($col, 0, 5);
        
        switch ($start) {
            case 'Resp ': $groups['Resp'][] = substr($col, 5); break;
            case 'Stim ': $groups['Stim'][] = substr($col, 5); break;
            case 'Proc ': $groups['Proc'][] = substr($col, 5); break;
            default:      $groups['Misc'][] = $col;
        }
    }
    
    foreach ($groups as $name => &$group) {
        if ($name !== 'Misc') {
            sort($group);
        }
    }
    
    unset($group);
    return $groups;
}

function extract_gen_trial_cols(&$trial_cols) {
    $gen = [];
    
    foreach ($trial_cols as $post => $cols) {
        $gen += array_flip($cols['Misc']);
        unset($trial_cols[$post]['Misc']);
    }
    
    return array_keys($gen);
}

function custom_sort_trial_misc_cols($cols) {
    $flipped = array_flip($cols);
    $sorted = [];
    $cond_cols = [];
    $remaining = [];
    
    foreach (['Username', 'ID', 'Experiment', 'Session', 'Trial'] as $col) {
        if (isset($flipped[$col])) {
            $sorted[] = $col;
            unset($flipped[$col]);
        }
    }
    
    foreach ($flipped as $header => $_) {
        if (substr($header, 0, 5) === 'Cond ') {
            $cond_cols[] = $header;
        } else {
            $remaining[] = $header;
        }
    }
    
    sort($remaining);
    sort($cond_cols);
    $sorted = array_merge($sorted, $remaining, $cond_cols);
    return $sorted;
}

## users

function get_users_for_menu() {
    $users = get_users();
    change_user_data_timestamps_to_dates($users);
    return sort_users_into_finished_and_unfinished($users);
}

function get_users() {
    $dirs = get_data_dirs();
    $users = [];
    
    foreach ($dirs as $dir) {
        $exp_name = get_exp_name($dir);
        $users[$exp_name] = get_users_in_exp($dir);
    }
    
    return $users;
}

function get_exp_name($dir) {
    return pathinfo($dir, PATHINFO_BASENAME);
}

function get_users_in_exp($dir) {
    $end_data = get_user_end_metadata($dir);
    $login = get_user_first_login($dir);
    $user_list = get_user_list($dir);
    $users = [];
    $e1 = 'Experiment End Timestamp';
    $e2 = 'Experiment Duration';
    $login_fields = ['ID', 'Cond', 'Timestamp'];
    
    foreach ($user_list as $user) {
        $users[$user] = isset($end_data[$user])
                       ? $end_data[$user]
                       : [$e1 => false, $e2 => false];
        
        foreach ($login_fields as $field) {
            $users[$user][$field] = isset($login[$user][$field]) ? $login[$user][$field] : false;
        }
    }
    
    uasort($users, function($u1, $u2) {
        return $u1['Timestamp'] < $u2['Timestamp'] ? -1 : 1;
    });
    
    return $users;
}

function get_user_end_metadata($dir) {
    $data = get_exp_metadata($dir);
    $end_data = [];
    $e1 = 'Experiment End Timestamp';
    $e2 = 'Experiment Duration';
    
    foreach ($data as $user => $row) {
        $end_data[$user] = [
            $e1 => isset($row[$e1]) ? $row[$e1] : false,
            $e2 => isset($row[$e2]) ? $row[$e2] : false,
        ];
    }
    
    return $end_data;
}

function get_user_list($dir) {
    $output_files = get_dir_entries("$dir/Output", 0);
    $user_list = [];
    
    foreach ($output_files as $filename) {
        $user_list[] = substr($filename, 7, -4);
    }
    
    return $user_list;
}

function get_user_first_login($dir) {
    if (!is_file("$dir/login.csv")) return [];
    
    $login_data = read_csv("$dir/login.csv");
    $user_first_login = [];
    
    foreach ($login_data as $row) {
        $user = $row['Username'];
        
        if ($row['Prev_ID'] === '') {
            $user_first_login[$user] = [
                'ID'        => [$row['ID']],
                'Timestamp' => $row['Timestamp'],
                'Cond'      => isset($row['Condition_Name'])
                               ? $row['Condition_Name']
                               : $row['Cond_Index']
            ];
        } else {
            $user_first_login[$user]['ID'][] = $row['ID'];
        }
    }
    
    return $user_first_login;
}

## display users

function change_user_data_timestamps_to_dates(&$users) {
    foreach ($users as $exp => $user_list) {
        foreach ($user_list as $user => $data) {
            $start = $data['Timestamp'];
            $end   = $data['Experiment End Timestamp'];
            
            unset($users[$exp][$user]['Timestamp']);
            unset($users[$exp][$user]['Experiment End Timestamp']);
            
            $users[$exp][$user]['Start'] = $start ? get_date_from_timestamp($start) : '';
            $users[$exp][$user]['End']   = $end   ? get_date_from_timestamp($end)   : '';
        }
    }
}

function get_date_from_timestamp($timestamp) {
    return date('m-d H:i', $timestamp);
}

function sort_users_into_finished_and_unfinished($users) {
    $exps = [];

    foreach ($users as $exp => $user_list) {
        $exps[$exp] = ['Finished' => [], 'Unfinished' => []];
        
        foreach ($user_list as $user => $data) {
            if ($data['End'] === '') {
                $exps[$exp]['Unfinished'][$user] = $data;
            } else {
                $exps[$exp]['Finished'][$user] = $data;
            }
        }
    }
    
    return $exps;
}

## generating html

function get_block_header($header_id, $content) {
    return "<label id='$header_id'>"
         .   '<span><input type="checkbox" class="col-group-checkbox"></span>'
         .   "<span>$content</span>"
         . '</label>';
}

function get_col_group($cols, $header, $prefix = '', $add_sub_header = false, $inp_name = 'c') {
    $group_inp = '<input type="checkbox" class="col-group-checkbox">';
    $html  = '<div class="col-group">';
    $html .=   "<label class='col-group-header'><span>$group_inp</span><span>$header</span></label>";
    
    foreach ($cols as $sub_header => $sub_cols) {
        $html .= '<div class="col-list">';
        $html .= "<label class='col-group-header sub-header'><span>$group_inp</span><span>$sub_header</span></label>";
        
        if (is_array($inp_name)) {
            $name = $inp_name[$sub_header];
        } else {
            $name = $inp_name;
        }
        
        foreach ($sub_cols as $col) {
            $col = htmlspecialchars($col, ENT_QUOTES);
            $val = $add_sub_header ? $prefix . $sub_header . ' ' . $col : $prefix . $col;
            $inp = "<input type='checkbox' name='{$name}[]' value='$val' checked>";
            $html .= "<label><span>$inp</span><span>$col</span></label>";
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}

function get_user_list_group_row($content, $level) {
    $content = htmlspecialchars($content);
    $filler = str_repeat('<span></span>', 5);
    return "<label class='user-group-row user-group-$level' data-level='$level'>"
         .   '<span><input type="checkbox" class="user-group-checkbox"></span>'
         .   "<span>&nbsp;<span>$content</span></span>$filler"
         . '</label>';
}

function get_user_checkbox_row($user, $exp_dir, $info) {
    $username = htmlspecialchars($user);
    $user_val = htmlspecialchars("$exp_dir/$user", ENT_QUOTES);
    $checked = $info['End'] ? 'checked' : '';
    $inp = "<input type='checkbox' name='u[]' value='$user_val' $checked>";
    $fields = [$inp, $username];
    $fields[] = get_id_html($info['ID']);
    $fields[] = $info['Start'];
    $fields[] = $info['End'];
    $fields[] = get_formatted_duration($info['Experiment Duration']);
    $fields[] = $info['Cond'];
    
    return '<label class="user-row"><span>'
         .   implode('</span><span>', $fields)
         . '</span></label>';
}

function get_id_html($ids) {
    if ($ids === false) return 'no login';
    if (count($ids) === 1) return $ids[0];
    
    $html  = '<div class="id-list">';
    $html .= implode('<br>', $ids);
    $html .= '</div>';
    
    return $html;
}

function clean_exp_name($exp) {
    if (substr($exp, -5) === '-Data') {
        return substr($exp, 0, -5);
    } else {
        return $exp;
    }
}

function get_formatted_duration($duration) {
    if ($duration === false) return '';
    
    $duration = (int) $duration;
    
    $units = [24 * 60 * 60, 60 * 60, 60];
    
    $vals = [];
    
    foreach ($units as $unit) {
        $vals[] = floor($duration / $unit);
        $duration %= $unit;
    }
    
    while (isset($vals[0]) and $vals[0] === 0.0) array_shift($vals);
    
    $vals[] = $duration;
    $return = '';
    
    foreach ($vals as $val) {
        $return .= ':' . str_pad($val, 2, '0', STR_PAD_LEFT);
    }
    
    return substr($return, 1);
}

## forms

function get_logout_button() {
    return '<form id="logout-form" method="post">'
         .   '<button type="submit" name="logout" value="logout">Log out</button>'
         . '</form>';
}
