<?php

function get_users_from_input() {
    $users = filter_input(INPUT_POST, 'u', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
    
    if ($users === null) $users = [];
    
    // user inputs look like "$exp_data_dir/$username",
    // where both parts both are valid and exist
    foreach ($users as $i => $user) {
        if (strpos($user, '\\') !== false) exit; // malicious input
        
        $parts = explode('/', $user);
        
        foreach ($parts as $part) {
            if ($part === '..' or $part === '.' or $part === '') exit;
        }
        
        if (count($parts) !== 2) exit;
        if (!is_dir(ROOT . "/Data/{$parts[0]}")) exit;
        if (!is_file(ROOT . "/Data/{$parts[0]}/Output/Output_{$parts[1]}.csv")) exit;
    }
    
    return $users;
}

function get_cols_from_input($name) {
    $val = filter_input(INPUT_POST, $name, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
    
    if ($val === null) $val = [];
    
    return $val;
}

function sort_users_into_experiments($users) {
    $exps = [];
    
    foreach ($users as $username) {
        $exp_dir_and_username = explode('/', $username);
        $exps[$exp_dir_and_username[0]][] = $exp_dir_and_username[1];
    }
    
    return $exps;
}

function get_output_fn($download_type) {
    if ($download_type === 'preview') {
        return get_preview_output_fn();
    } else if ($download_type === 'download') {
        return get_download_output_fn();
    } else {
        exit; // bad input
    }
}

function get_preview_output_fn() {
?><!DOCTYPE html>
<html>
</head>
    <title>Collector Data Preview</title>
    <meta charset="utf-8">
    <base href="<?= get_url_to_root() ?>">
    <?= get_link('Links/css/data-menu.css') ?>
</head>
<body>
<table id="data-preview"><tbody>
<?php
    register_shutdown_function(function() {
        echo '</tbody></table></body></html>';
    });
    
    return function($row) {
        $html_row = [];
        
        foreach ($row as $field) {
            $html_row[] = htmlspecialchars($field);
        }
        
        echo '<tr><td><div>' . implode('</div></td><td><div>', $html_row) . '</div></td></tr>';
    };
}

function get_download_output_fn() {
    $date = date('y-d-m');
    $filename = "Data_$date.csv";
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Description: File Transfer');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Content-Type: text/csv');
    $stream = fopen('php://output', 'w');
    $encoding = get_config('encoding');
    
    return function($row) use ($stream, $encoding) {
        $enc_row = [];
        
        foreach ($row as $field) {
            $enc_row[] = iconv('UTF-8', $encoding, $field);
        }
        
        fputcsv($stream, $enc_row);
    };
}

function get_login_from_dir($dir, $index_col) {
    $filename = ROOT . "/Data/$dir/login.csv";
    
    if (!is_file($filename)) return [];
    
    $data = read_csv($filename);
    $indexed_data = [];
    
    foreach ($data as $row) {
        if (isset($indexed_data[$row[$index_col]])) continue;
        
        $indexed_data[$row[$index_col]] = $row;
    }
    
    return $indexed_data;
}

function get_meta_from_dir($dir) {
    $filename = ROOT . "/Data/$dir/metadata.csv";
    
    if (!is_file($filename)) return [];
    
    return read_file_as_metadata($filename);
}

function get_array_with_prefixed_vals($array, $prefix) {
    $prefixed = [];
    
    foreach ($array as $val) {
        $prefixed[] = $prefix . $val;
    }
    
    return $prefixed;
}
