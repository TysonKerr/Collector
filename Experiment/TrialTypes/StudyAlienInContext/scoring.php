<?php

function add_trials($trials) {
    array_splice($_SESSION['Trials'], $_SESSION['Position'] + 1, 0, $trials);
};

function get_trial($index) {
    return $_SESSION['Trials'][$index];
}

$data = $_POST;
$data['strictAcc'] = $data['alien_response_correct'] == '1' ? '1' : '0';

if (!isset($_SESSION['Study_Misses'])) $_SESSION['Study_Misses'] = 0;

if ($data['strictAcc'] != '1') {
    ++$_SESSION['Study_Misses'];
} else {
    $_SESSION['Study_Misses'] = 0;
}

if ($_SESSION['Study_Misses'] >= 5) {
    $trial = get_trial($_SESSION['Position']);
    
    $trial['Procedure']['Trial Type'] = 'end-early';
    $trial['Procedure']['Max Time'] = 'user';
    add_trials([$trial]);
}
