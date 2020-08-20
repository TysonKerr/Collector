<?php
namespace data;

require dirname(__DIR__ ) . '/init.php';
require dirname(__DIR__ ) . '/admin/definitions.php';
require __DIR__ . '/definitions.php';

define_constants();

\admin\start_session();

if (filter_has_var(INPUT_POST, 'download')) {
    require __DIR__ . '/download.php';
} else {
    \admin\start_ob();
    require __DIR__ . '/data-menu.php';
}
