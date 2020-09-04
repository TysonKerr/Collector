<?php

// we dont want to actually record any data for this trial type,
// or allow the participant to move past it,
// so simply refresh the page and stop running the script to reset it
header('Location: .');
exit;
