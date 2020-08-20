<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Collector Data</title>
    <base href="<?= get_url_to_root() ?>">
    <link rel="icon" href="Links/icon.png" type="image/png">
    
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>          
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>
    
    <?= get_link('Links/css/Collector.css') ?>
    <?= get_link('Links/css/jquery-ui-1.10.4.custom.min.css') ?>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>
        if (typeof jQuery === "undefined") {
            document.write("<script src='<?= get_smart_cached_path('Links/js/jquery.js') ?>'><\/script>");
        }
    </script>
</head>

<body class="center-outer">
    <div id="Collector-content" class="center-inner">%content%</div>
</body>
</html>
