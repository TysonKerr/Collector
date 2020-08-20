<?php

/* * *
 * Since the data will be recorded into metadata, I am going to remove it
 * from the main data output, so that it's not filled with extra columns.
 *
 * Normally, you would be working with the $data variable,
 * but since we are not interested in columns like "Trial_Focus"
 * or "Trial_Duration", we can use $responses instead, so we just have the
 * survey responses, like "Age" and "Gender".
 */

foreach ($responses as $col => $val) {
    unset($data[$col]);
}

record_metadata($responses + ['Demographics' => 'completed']);

/* * *
 * Eligibility checks
 *
 * If you want to filter out participants based on their demographics,
 * please take a look at the eligibility.php file inside your experiment
 * folder (e.g., if your experiment is called "My Study", please look in
 * Experiments/My Study/ for the file).
 */
