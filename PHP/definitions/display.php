<?php
/**
 * calls var_dump with <pre> tags around it for any arguments passed in
 * in the special case where an argument looks like csv data,
 * display_csv_table is used instead
 */
function dump() {
    foreach (func_get_args() as $arg) {
        if (is_array($arg)
            and is_array(current($arg))
            and is_scalar(current(current($arg)))
            and count($arg) > 1
            and array_keys($arg) === array_keys(array_keys($arg))
        ) {
            display_csv_table($arg);
        } else {
            echo '<div class="dump"><pre>';
            var_dump(htmlspecialchars_recursive($arg));
            echo '</pre></div>';
        }
    }
}
/**
 * Recursively escapes an array to prevent passing code along from user input.
 * @param mixed $input
 * @return array
 */
function htmlspecialchars_recursive($input, $flags = ENT_COMPAT | ENT_HTML401)
{
    if (!is_array($input)) {
        return is_string($input) ? htmlspecialchars($input, $flags) : $input;
    }
    
    return $input;
    
    // I dont think array_walk_recursive can filter the array keys
    $output = [];
    
    foreach ($input as $key => $val) {
        $output[htmlspecialchars($key, $flags)] = is_array($val)
            ? htmlspecialchars_recursive($val)
            : (is_string($val) ? htmlspecialchars($val, $flags) : $val);
    }
    
    return $output;
}
/**
 * Echoes a 2D array as an HTML table.
 * @staticvar boolean $doInit Keeps track of if the function has been called.
 * @param array $array The array to display.
 * @see print_csv_table_css()
 * @see scalarsToArray()
 * @see get_headers_from_csv_array()
 */
function display_csv_table(array $csv, $nonArrayCol = false)
{
    static $doInit = true;
    if ($doInit) {
        // only print the CSS the first call
        $doInit = false;
        print_csv_table_css();
    }
    // format array and extract columns
    if ($nonArrayCol == false) {
        $i = 0;
        while (isset($csv[$i]) and is_scalar($csv[$i])) {
            unset($csv[$i]);
            $i++;
        }
    }
    $arrayNoScalars = scalarsToArray($csv);
    $columns = get_headers_from_csv_array($arrayNoScalars);
    // write table header
    echo '<div class="display2dArray-container"><table class="display2dArray"><thead><tr><th>1</th><th><div>',
         implode('</div></th><th><div>', $columns),
         '</div></th></tr></thead><tbody>';
    // write cell values
    foreach ($arrayNoScalars as $i => $row) {
        $row = get_sorted_array($row, array_flip($columns));
        foreach ($row as &$field) {
            if (is_array($field)) $field = json_encode($field);
            $field = htmlspecialchars($field);
        }
        echo '<tr><td>', ($i + 2), '</td><td><div>',
             implode('</div></td><td><div>', $row), '</div></td></tr>';
    }
    echo '</tbody></table></div>';
}
/**
 * Echos the CSS for display2dArray.
 */
function print_csv_table_css()
{
    echo '
      <style>
        .display2dArray-container { max-width: 95vw; margin: auto;
                                    overflow: auto; max-height: 600px; }
        .display2dArray           { border-collapse:collapse; margin: auto; }
        .display2dArray td,
        .display2dArray th        { border:1px solid #000;
                                    vertical-align:middle; text-align:center;
                                    padding:2px 6px; overflow:hidden; }
        .display2dArray td        { max-width:200px; }
        .display2dArray th        { max-width:200px; white-space: normal; }
        .display2dArray td > div  { max-height:1.5em; overflow:hidden; }
      </style>
    ';
}
/**
 * Converts scalars in a 2D array to arrays with specified key name.
 * @param array $array
 * @param string $keyname
 * @return array
 */
function scalarsToArray(array $array, $keyname = 'Non-array Value')
{
    foreach ($array as &$row) {
        if (is_scalar($row)) {
            $row = array($keyname => $row);
        }
    }
    return $array;
}
/**
 * Gets all the column names from a 2D array.
 * @param array $array
 * @return array
 */
function get_headers_from_csv_array(array $array)
{
    $columns = array();
    
    foreach ($array as $row) {
        foreach ($row as $header => $_) $columns[$header] = true;
    }
    
    return array_keys($columns);
}
/**
 * Formats a duration in seconds to something like 03d:02h:03m:20s.
 * @param int $durationInSeconds
 * @return string
 */
function durationFormatted($durationInSeconds)
{
    $hours   = floor($durationInSeconds/3600);
    $minutes = floor(($durationInSeconds - $hours*3600)/60);
    $seconds = $durationInSeconds - $hours*3600 - $minutes*60;
    if ($hours > 23) {
        $days = floor($hours/24);
        $hours = $hours - $days*24;
        if ($days < 10) {
            $days = '0' . $days;
        }
    }
    if ($hours < 10) {
        $hours   = '0' . $hours;
    }
    if ($minutes < 10) {
        $minutes = '0' . $minutes;
    }
    if ($seconds < 10) {
        $seconds = '0' . $seconds;
    }
    return $days.'d:' . $hours.'h:' . $minutes.'m:' . $seconds.'s';
}
/**
 * Formats a time like 5d:2h:3m:20s into seconds.
 * @param string $duration
 * @return int
 */
function durationInSeconds($duration = '')
{
    if ('' === $duration) {
        // no duration was given
        return 0;
    }
    // format the duration and convert to array based on colon delimiters
    $durationArray = explode(':', trim(strtolower($duration)));
    $output = 0;
    foreach ($durationArray as $part) {
        // sanitize each part to just the digits
        $value = preg_replace('/[^0-9]/', '', $part);
        if(false !== stripos($part, 'd')) {
            // days in seconds
            $output += ($value * 24 * 60 * 60);
        } else if (false !== stripos($part, 'h')){
            // hours in seconds
            $output += ($value * 60 * 60);
        } else if (false !== stripos($part, 'm')){
            // minutes in seconds
            $output += ($value * 60);
        } else if (false !== stripos($part, 's')){
            // seconds... in seconds
            $output += $value;
        }
    }
    return $output;
}
