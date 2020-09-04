<?php

function get_scores($settings) {
    $columns = ['Accuracy', 'lenientAcc', 'strictAcc'];
    return get_average_response_from_trials($settings, $columns);
}
