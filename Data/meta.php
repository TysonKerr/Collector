<?php
$root = '../';
require $root.'/Code/initiateCollector.php';
require dirname(__DIR__) . '/Code/admin/adminFunctions.php';

admin\require_admin_status();

$filename = get_metadata_filename();

if (!is_file($filename)) {
    exit('no metadata to download yet');
}

$meta = getFromFile($filename, false);
$data = [];
$headers = ['Username' => true];

foreach ($meta as $row) {
    $user = $row['Username'];
    $field = $row['Field'];
    $val = $row['Value'];
    
    if (!isset($data[$user])) {
        $data[$user] = ['Username' => $user];
    }
    
    $data[$user][$field] = $val;
    $headers[$field] = true;
}

foreach ($data as $user => $user_data) {
    $data[$user] = SortArrayLikeArray($user_data, $headers);
}

if (filter_input(INPUT_GET, 'dl') !== null) {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="DMT-metadata.csv";');
    $output_stream = fopen('php://output', 'w');
    fputcsv($output_stream, array_keys($headers));
    
    foreach ($data as $user_data) {
        fputcsv($output_stream, $user_data);
    }
    
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title>DMT Metadata</title>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
        }
        
        td, th {
            border: 1px solid black;
            padding: 2px 4px;
        }
    </style>
</head>
<body>
    <form method="get" target="_blank">
        <button type="submit" value="yes" name="dl">Download</button>
    </form>
    
    <table>
        <thead>
            <tr> <th><?= implode('</th> <th>', array_keys($headers)) ?></th> </tr>
        </thead>
        <tbody>
    <?php
    
    foreach ($data as $user_data) {
        echo '<tr> <td>' . implode('</td> <td>', $user_data) . '</td> </tr>';
    }
    
    ?>
        </tbody>
    </table>
</body>
</html>
