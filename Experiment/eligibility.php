<?php

// this will be given a list of metadata (typically, the demographics
// of the participant), and should return either true or false
// return true if they are allowed to continue
// return false to send them to Code/Ineligible.php
function eligibility_test($data) {
    // use this section to block people who are younger than 60
    // if (!is_numeric($data['Age']) or $data['Age'] < 60) {
    //     return false;
    // }
    
    return true;
}
