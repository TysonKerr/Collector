<?php

if (strtolower($data['Response']) === strtolower($recog_answer)) {
    $data['Accuracy']   = 100;
    $data['lenientAcc'] = 1;
    $data['strictAcc']  = 1;
} else {
    $data['Accuracy']   = 0;
    $data['lenientAcc'] = 0;
    $data['strictAcc']  = 0;
}
