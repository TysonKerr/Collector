<?php

function get_survey_filename($cue, $settings) {
    if (isset($settings['survey']) and $settings['survey'] !== false) {
        return $settings['survey'];
    } else if ($cue !== false and strlen($cue) > 0) {
        return $cue;
    }
    
    throw new Exception('Survey trial type is missing the survey filename.'
        . ' Please provide one, either using the Cue column of the stimuli file,'
        . ' or using the settings column (e.g., "survey: Media/my_survey.csv")');
}

function read_survey($filename) {
    if (!isset($_SESSION['Current Survey'])) {
        $_SESSION['Current Survey'] = shuffle_csv_array(read_csv($filename));
    }
    
    $survey = $_SESSION['Current Survey'];
    unset($_SESSION['Current Survey']);
    
    standardize_survey_and_check_for_errors($survey, $filename);
    return $survey;
}

function standardize_survey_and_check_for_errors(&$survey, $filename) {
    check_for_required_columns($survey, $filename);
    check_for_bad_values($survey, $filename);
    
    foreach ($survey as &$row) {
        $row['Required'] = parse_required($row['Required'] ?? '');
        $row['Choices']  = get_range(     $row['Choices']  ?? '');
        $row['Settings'] = parse_settings($row['Settings'] ?? '');
        $values = $row['Values'] ?? '';
        $row['Values'] = $values === '' ? array_keys($row['Choices']) : get_range($values);
    }
}

function check_for_required_columns($survey, $filename) {
    $required = ['Question Name', 'Text', 'Type', 'Choices', 'Values'];
    
    foreach ($required as $col) {
        if (!isset($survey[0][$col])) {
            throw new Exception("Bad survey: $filename: Missing column '$col'");
        }
    }
}

function check_for_bad_values($survey, $filename) {
    $types = get_survey_question_types();
    $names = [];
    
    foreach ($survey as $i => $row) {
        $row_n = $i + 2;
        $error = "Bad survey row: $filename: row $row_n";
        $type = $row['Type'];
        $name = $row['Question Name'];
        
        if (!isset($types[strtolower($type)])) {
            throw new Exception("$error: type '$type' doesn't exist.");
        }
        
        if (isset($names[$name])) {
            throw new Exception("$error: question name '$name' repeated, must be unique.");
        }
        
        $names[$name] = true;
    }
}

function get_survey_question_types() {
    static $types = null;
    
    if ($types === null) {
        $types = [];
        $dir = __DIR__ . '/question-types';
        
        foreach (scandir($dir) as $entry) {
            if (strtolower(substr($entry, -4)) === '.php') {
                
                $types[strtolower(substr($entry, 0, -4))] = require "$dir/$entry";
            }
        }
    }
    
    return $types;
}

function implode_with_tags($strs, $tag, $classname = '') {
    $class = $classname == '' ? '' : "class='$classname'";
    
    return "<$tag $class>"
         . implode("</$tag><$tag $class>", $strs)
         . "</$tag>";
}

function parse_required($input) {
    $lower = strtolower($input);
    return $lower !== '' and $lower !== 'off' and $lower !== 'no';
}
