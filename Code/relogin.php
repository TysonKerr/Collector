<?php

if (isset($_POST['username'])) {
    require __DIR__ . '/customFunctions.php';
    $filename = __DIR__ . '/../Data/json_copies/' . $_POST['username'] . '.json';
    
    if (is_file($filename)) {
        $session_data_json = file_get_contents($filename);
        $session_data      = json_decode($session_data_json, true);
        
        start_session();
        $_SESSION = $session_data;
        
        header("Location: Experiment.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collector Login</title>
    <style>
        html { height: 100%; width: 100%; margin: 0; padding: 0; }
        body { display: table; height: 100%; width: 100%; margin: 0; padding: 0; }
        #main_container { display: table-cell; vertical-align: middle; text-align: center; }
        #sub_container { width: 600px; margin: auto; }
        .error { margin-bottom: 40px; color: #600; }
    </style>
</head>
<body>
    <div id="main_container"><form id="sub_container" method="POST" autocomplete="off">
        <?php if (isset($_POST['username'])): ?>
        <div class="error">Username "<b><?= htmlspecialchars($_POST['username'], ENT_QUOTES) ?></b>" does not exist.</div>
        <?php endif; ?>
        
        Please enter your username: <input name="username"> <button type="submit">Submit</button>
    </div></div>
</body>
</html>
