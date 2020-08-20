<?php

foreach ($data['Response'] as $i => $resp) {
    $data['Sort-' . ($i + 1)] = $resp;
}

$data['Response'] = implode('|', $_POST['Response']);
