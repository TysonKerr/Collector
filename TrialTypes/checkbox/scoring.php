<?php

// checkboxes might not submit any data, if all options were left unchecked
// but if they were, it will appear in an array format, since the input
// name was "Response[]". Let's change that to a string
if (isset($data['Response'])) {
    $data['Response'] = implode('|', $data['Response']);
} else {
    $data['Response'] = '';
}

if (isset($col_name) and $col_name !== '') {
    record_metadata([$col_name => $data['Response']]);
}
