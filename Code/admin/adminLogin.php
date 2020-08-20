<?php
    // prevent direct access, in case htaccess file not set
    if (get_included_files()[0] === __FILE__) exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collector Admin Login</title>
    <link rel="icon" href="<?= admin\url_to_root() ?>/Code/icon.png" type="image/png">
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 125%;
        }
        
        body > div {
            display: table;
            width: 100%;
            height: 100%;
        }
        
        body > div > div {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        
        .invis { visibility: hidden; }
        .error { color: #600; }
        
        input, button { font-size: 100%; }
    </style>
</head>
<body><div><div>
    <form method="post">
        <p class="<?= admin\get_login_error_class() ?>">
            Incorrect password
        </p>
        
        <p>Please enter your password.</p>
        
        <div>
            <input type="password" name="col_adm_pas" autofocus>
            <button type="submit">Submit</button>
        </div>
    </form>
</div></div></body>
</html>
