<?php

$data = $_POST;
$metadata = $data;

foreach (['RT', 'RTfirst', 'RTlast', 'Focus'] as $col) {
    unset($metadata[$col]);
}

save_extra_metadata($_SESSION['Username'], $metadata);
check_eligibility($_SESSION['Username']);
