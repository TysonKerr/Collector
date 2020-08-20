<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Collector</title>
    <base href="<?= get_url_to_root() ?>">
    <link rel="icon" href="Links/icon.png" type="image/png">
    
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'>          
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Inconsolata' rel='stylesheet' type='text/css'>
    
    <?= get_link('Links/css/Collector.css') ?>
    <?= get_link('Links/js/jquery.js') ?>
    <?= get_link('Links/js/Collector.js') ?>
</head>

<body data-controller="<?= defined('PAGE') ? PAGE : '' ?>" data-action="%action%" class="center-outer">
    <!-- redirect if Javascript is disabled -->
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?= get_url_to_root() . '/' . get_page_path('nojs') ?>" />
    </noscript>
    
    <div id="Collector-content" class="center-inner">%content%</div>
</body>
</html>
