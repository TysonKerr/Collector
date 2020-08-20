<?php
// you can use this function to modify the procedure before they have
// been shuffled at login.php
function generate_proc($proc) {
    foreach ($proc as $i => $row) {
        // do some modification ...
    }
    
    return $proc;
}
