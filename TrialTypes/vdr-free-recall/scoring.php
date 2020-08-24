<?php

$data += get_default_columns();
$answers = explode('|', $answer);
$leniencies = isset($leniency)
            ? get_arr_with_numeric_vals(explode('|', $leniency))
            : array_fill(0, count($stimuli), 1);
$values = isset($value)
        ? get_arr_with_numeric_vals(explode('|', $value))
        : array_fill(0, count($stimuli), 1);
$responses = get_word_responses($data['Response']);
$matches = get_word_matches($responses, $answers, $leniencies, $values);

if (!isset($_SESSION['Words recalled'])) $_SESSION['Words recalled'] = [];

$_SESSION['Words recalled'] = $matches + $_SESSION['Words recalled'];

$data['Response_raw'] = get_raw_response($data['Response']);
$data['Response']    = implode('|', $responses);
$data['possibleVal'] = array_sum($values);
$data['possibleAcc'] = count($answers);

$data['Word_Order']   = range(1, count($stimuli));
$data['Matched_Resp'] = get_sub_array($matches, 'word');
$data['Matched_Diff'] = get_sub_array($matches, 'diff');
$data['Output_Order'] = get_sub_array($matches, 'output_order');
$data['Word_lenientAcc'] = array_fill(0, count($stimuli), 0);
$data['Word_lenientVal'] = array_fill(0, count($stimuli), 0);
$data['Word_strictAcc']  = array_fill(0, count($stimuli), 0);
$data['Word_strictVal']  = array_fill(0, count($stimuli), 0);

foreach ($answers as $i => $ans) {
    if ($matches[$ans]['word'] !== false) {
        $data['Word_lenientAcc'][$i] = 1;
        $data['Word_lenientVal'][$i] = $matches[$ans]['value'];
        
        if ($matches[$ans]['diff'] === 0) {
            $data['Word_strictAcc'][$i] = 1;
            $data['Word_strictVal'][$i] = $matches[$ans]['value'];
        }
    }
}

$data['lenientAcc'] = array_sum($data['Word_lenientAcc']);
$data['lenientVal'] = array_sum($data['Word_lenientVal']);
$data['strictAcc']  = array_sum($data['Word_strictAcc']);
$data['strictVal']  = array_sum($data['Word_strictVal']);
$data['Accuracy']   = round($data['lenientAcc'] / count($stimuli) * 100);

$si_str = get_selectivity_index($values, $data['strictVal'],  $data['strictAcc']);
$si_len = get_selectivity_index($values, $data['lenientVal'], $data['lenientAcc']);

$data['Selectivity_Index_Strict']  = $si_str;
$data['Selectivity_Index_Lenient'] = $si_len;
