<?php

require __DIR__ . '/login-error-checking.php';

function check_for_submitted_login_data() {
    if (filter_input(INPUT_GET, 'u') !== null) {
        try {
            login();
        } catch (LengthException $e) {
            return $e->getMessage();
        }
    }
    
    return '';
}

function login() {
    // reset session so it doesn't contain any information from a previous login attempt
    $username = get_submitted_username();
    check_user_eligibility($username);
    
    if (has_session($username)) {
        $_SESSION = get_session($username);
        resume_session();
    } else {
        start_new_session($username);
    }
}

function get_submitted_username() {
    $username = get_input_string_without_bad_filename_chars('u');
    
    if (!$username or strlen($username) < 3) {
        throw new LengthException('Please enter a username with at least 3 characters.');
    }
    
    if (get_config('anonymize_names')) $username = get_anonymous_username($username);
    
    return $username;
}

function get_input_string_without_bad_filename_chars($imput_name) {
    $str = (string) filter_input(INPUT_GET, $imput_name);
    $bad_chars = ['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'];
    return str_replace($bad_chars, '', $str);
}

function get_anonymous_username($username) {
    return substr(hash('sha1', $username), 0, 8);
}

function resume_session() {
    if (has_more_experiment_to_do($_SESSION)) {
        resume_experiment();
    } else {
        redirect('done');
    }
}

function get_condition_select_class() {
    return 'collectorInput' . (get_config('show_condition_selector') ? '' : ' hidden');
}

function get_new_login_id() {
    return rand_string(8);
}

function get_conditions($index = null) {
    if ($index === null) $index = get_input_conditions_index();
    
    $conditions = read_csv(get_exp_dir() . "/Conditions_$index.csv");
    $conditions = get_processed_conditions($conditions);
    check_for_errors_in_conditions($conditions);
    return $conditions;
}

function get_processed_conditions($conditions) {
    foreach ($conditions as $i => $condition) {
        if (!isset($condition['Name']))    $conditions[$i]['Name']    = $i + 1;
        if (!isset($condition['Stimuli'])) $conditions[$i]['Stimuli'] = '';
    }
    
    return $conditions;
}

function get_possible_conditions_indices() {
    $indices = [];
    $index = 1;
    $dir = get_exp_dir();
    
    while (is_file(get_exp_filename("Conditions_$index.csv", 'Conditions'))) {
        $indices[] = $index;
        ++$index;
    }
    
    return $indices;
}

function get_input_conditions_index() {
    $index = filter_input(INPUT_GET, 'c');
    
    if ($index === null or !is_numeric($index)) return '1';
    
    $index = (int) $index;
    
    if ($index < 1) return '1';
    
    return (string) $index;
}

function get_consent_path() {
    return CURR_EXP . '/consent.pdf';
}

function has_more_experiment_to_do($session) {
    return isset($session['Procedure'][$session['Position']]);
}

function resume_experiment() {
    $prev_id = $_SESSION['ID'];
    $_SESSION['ID'] = get_new_login_id();
    record_login($prev_id);
    redirect('experiment');
}

function record_login($prev_id = '') {
    $login_info = get_login_info($prev_id);
    write_csv(get_data_filename('login'), $login_info);
}

function get_login_info($prev_id = '') {
    $user_info = [
        'Username'   => $_SESSION['Username'],
        'ID'         => $_SESSION['ID'],
        'Prev_ID'    => $prev_id,
        'Timestamp'  => time(),
        'Date'       => get_date(),
        'Session'    => $_SESSION['Session'],
        'IP'         => get_server_input('REMOTE_ADDR'),
        'Cond_Index' => get_input_conditions_index(),
    ];
    $condition_info = get_array_with_prefixed_keys($_SESSION['Condition'], 'Condition_');
    $user_agent_info = get_user_agent_info();
    return $user_info + $condition_info + $user_agent_info;
}

function get_user_agent_info() {
    $user_agent = get_browscap_browser();
    
    return [
        'Browser'    => $user_agent->Parent,
        'DeviceType' => $user_agent->Device_Type,
        'OS'         => $user_agent->Platform,
    ];
}

function get_browscap_browser() {
    $browscap_path = ROOT . '/PHP/phpbrowscap/Browscap.php';
    $cache_path = dirname($browscap_path) . '/cache';
    require_once $browscap_path;
    
    // phpbrowscap requires a cache; create cache dir if it doesn't exist
    if (!is_dir($cache_path)) mkdir('phpbrowscap/cache', 0777, true);
    
    // get and return the user agent info
    $bc = new phpbrowscap\Browscap($cache_path);
    return $bc->getBrowser();
}

function start_new_session($username) {
    $_SESSION = get_new_session($username, get_new_condition());
    record_login();
    redirect('experiment');
}

function get_new_condition() {
    $conditions = get_conditions();
    $input_condition = get_input_selected_condition();
    
    if (isset($conditions[$input_condition])) {
        return $conditions[$input_condition];
    } else {
        return get_random_assignment($conditions);
    }
}

function get_input_selected_condition() {
    $input = filter_input(INPUT_GET, 'sc');
    $input = is_numeric($input) ? (int) $input : -1;
    return $input;
}

function get_random_assignment($conditions) {
    $counter_file = get_counter_filename();
    $counter = is_file($counter_file)
             ? (int) file_get_contents($counter_file)
             : 0;
    do {
        $condition = $conditions[$counter % count($conditions)];
        ++$counter;
    } while (condition_is_flagged($condition));
    
    file_put_contents($counter_file, $counter);
    return $condition;
}

function get_counter_filename() {
    $counter_file = get_data_filename('counter',
        ['c-index' => get_input_conditions_index()]
    );
    
    $dir = dirname($counter_file);
    
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    return $counter_file;
}

function get_new_session($username, $condition) {
    $session = [
        'Username' => $username,
        'ID' => get_new_login_id(),
        'Condition' => $condition,
        'Position' => 0,
        'Post Trial' => 0,
        'Session' => 1,
        'Trial Timestamp' => microtime(true),
        'Metadata' => [],
        'Experiment Start Timestamp' => time()
    ];
    $session['Condition'] = $condition;
    $session['Procedure'] = get_procedure($session['Condition']['Procedure'], true);
    $session['Stimuli']   = get_stimuli(  $session['Condition']['Stimuli']);
    $session['Responses'] = [];
    return $session;
}

function condition_is_flagged($condition_row) {
    return substr($condition_row['Name'], 0, 1) === '#';
}

function get_condition_display_option($condition, $index) {
    $name = get_config('use_condition_names') ? $condition['Name'] : $index + 1;
    $title = get_config('show_condition_info')
           ? "title='{$condition['Stimuli']} - {$condition['Procedure']}'"
           : '';
    $style = condition_is_flagged($condition) ? "style='color: grey;'" : '';
    return "<option value='$index' $title $style>$name </option>";
}

function get_conditions_as_options($conditions) {
    $options = [];
    
    foreach ($conditions as $i => $cond) {
        if (get_config('hide_flagged_conditions') AND condition_is_flagged($cond)) continue;
        
        $options[] = get_condition_display_option($cond, $i);
    }
    
    return implode('', $options);
}

function get_procedure($files, $add_config_procs = false) {
    if ($add_config_procs) {
        $files = get_config_proc_filename('prepend_procedure')
               . ",$files,"
               . get_config_proc_filename('append_procedure');
    }
    
    return get_merged_exp_files($files, 'Procedure');
}

function get_processed_procedure($procedure) {
    $procedure = array_map('get_post_trials_from_row', $procedure);
    return $procedure;
}

function get_stimuli($files) {
    return get_merged_exp_files($files, 'Stimuli');
}

function get_processed_stimuli($stimuli) {
    return $stimuli;
}

function get_merged_exp_files($file_list, $type) {
    if ($type !== 'Stimuli' and $type !== 'Procedure') {
        throw new Exception("Exp file type '$type' not recognized, must be 'Stimuli' or 'Procedure'.");
    }
    
    $filenames = get_exp_filenames_from_list($file_list, $type);
    
    try {
        $data = read_multiple_csvs($filenames);
        $data = get_generated_exp_data($data, $type);
        $data = shuffle_csv_array($data);
        $data = get_processed_data($data, $type);
        check_exp_data_for_errors($data, $type);
    } catch (Exception $e) {
        throw new Exception("error reading $type set '$file_list':\n" . $e->getMessage() . "\n");
    }
    
    return $data;
}

function get_generated_exp_data($data, $type) {
    $generator_filename = get_exp_dir() . "/$type/generator.php";
    $type_lowered = strtolower($type);
    
    if (is_file($generator_filename)) require_once $generator_filename;
    
    if (function_exists("generate_$type_lowered")) {
        return call_user_func("generate_$type_lowered", $data);
    } else {
        return $data;
    }
}

function get_processed_data($data, $type) {
    if ($type === 'Stimuli') {
        return get_processed_stimuli($data);
    } else {
        return get_processed_procedure($data);
    }
}

function get_exp_filenames_from_list($file_list, $type) {
    $filenames = explode(',', $file_list);
    $real_filenames = [];
    
    foreach ($filenames as $i => $filename) {
        $filename = trim($filename);
        
        if ($filename !== '') $real_filenames[] = get_exp_dir() . "/$type/$filename";
    }
    
    return $real_filenames;
}

function get_config_proc_filename($config_var) {
    $proc_file = get_config($config_var);
    
    if (!$proc_file) return false;
    
    return $proc_file;
}
