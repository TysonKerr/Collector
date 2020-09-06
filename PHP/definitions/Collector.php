<?php

define('ROOT', dirname(dirname(__DIR__)));
/**
 * get file path to data directory for current experiments
 * @param string $exp the exp name, should match a folder inside Experiments/
 * @return string
 */
function get_data_folder($exp = null) {
    if ($exp === null) {
        if (defined('CURR_EXP')) {
            $exp = CURR_EXP;
        } else {
            throw new Exception('Cannot get data folder without current experiment defined');
        }
    }
    
    return ROOT . "/Data/$exp-Data";
}
/**
 * get path to specific file inside data folder for this experiments
 * @param string $file the type of data file
 * @param array $vars data affecting filenames, like Username and Exp
 * @return string
 */
function get_data_filename($data_type, $vars = []) {
    if (!isset($vars['Username'])) $vars['Username'] = $_SESSION['Username'];
    if (!isset($vars['Exp']) and defined('CURR_EXP')) $vars['Exp'] = CURR_EXP;
    
    $username = $vars['Username'];
    
    switch ($data_type) {
        case 'metadata': $filename = 'metadata.csv'; break;
        case 'sess':     $filename = "user/user_$username.json"; break;
        case 'output':   $filename = "Output/Output_{$username}.csv"; break;
        case 'login':    $filename = 'login.csv'; break;
        case 'counter':  $filename = "counter_{$vars['c-index']}.txt"; break;
        default: trigger_error("data type '$data_type' not recognized", E_USER_ERROR);
    }
    
    return get_data_folder($vars['Exp']) . "/$filename";
}
/**
 * gets the full path to a file with a specified type
 * @param string $filename the specific name, such as "my_proc.csv"
 * @param string $type can be 'Conditions', 'Stimuli', or 'Procedure'
 * @return string
 */
function get_exp_filename($filename, $type) {
    $dir = get_exp_dir();
    
    switch ($type) {
        case 'Conditions': return "$dir/$filename";
        case 'Stimuli':    return "$dir/Stimuli/$filename";
        case 'Procedure':  return "$dir/Procedure/$filename";
        default: throw new Exception("Unrecognized exp file type: $type");
    }
}
/**
 * saves array of extra data about username to file
 * @param string $username
 * @param array $data
 */
function record_user_metadata($username, $data) {
    $filename = get_data_filename('metadata');
    $dir = dirname($filename);
    
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    if (!is_file($filename)) {
        $handle = fopen($filename, 'w');
        fputcsv($handle, ['Username', 'Field', 'Value']);
    } else {
        $handle = fopen($filename, 'a');
    }
    
    foreach ($data as $key => $value) {
        fputcsv($handle, [$username, $key, $value]);
    }
    
    fclose($handle);
    get_all_metadata(true); // reset metadata in memory
}

function record_metadata($data) {
    foreach ($data as $key => $val) {
        $_SESSION['Metadata'][$key] = $val;
    }
    
    record_user_metadata($_SESSION['Username'], $data);
}
/**
 * get metadata for username saved previously
 * @param string $username
 * @return array|bool
 * @see record_metadata()
 */
function get_metadata($username) {
    $metadata = get_all_metadata();
    return isset($metadata[$username])
         ? $metadata[$username]
         : false;
}
/**
 * gets all metadata recorded so far
 * @param bool @reset reset the data stored from memory, reread from file
 * @return array
 * @see record_user_metadata()
 */
function get_all_metadata($reset = false) {
    static $data = null;
    
    if ($reset) {
        $data = null;
        return;
    }
    
    if ($data === null) {
        $filename = get_data_filename('metadata');
        $data = read_file_as_metadata($filename);
    }
    
    return $data;
}

function read_file_as_metadata($filename) {
    $data = [];
    
    if (is_file($filename)) {
        $handle = fopen($filename, 'r');
        fgetcsv($handle); // skip headers
        
        while ($line = fgetcsv($handle)) {
            list($user, $key, $val) = $line;
            
            if (!isset($data[$user])) $data[$user] = [];
            
            $data[$user][$key] = $val;
        }
        
        fclose($handle);
    }
    
    return $data;
}

function check_user_eligibility($username) {
    $metadata = get_metadata($username);
    
    if ($metadata === false) return;
    
    check_metadata_eligibility($metadata);
}

function check_my_eligibility() {
    check_metadata_eligibility($_SESSION['Metadata']);
}

function check_metadata_eligibility($metadata) {
    if (!is_metadata_eligible($metadata)) {
        redirect('ineligible');
    }
}

function is_metadata_eligible($metadata) {
    $filename = get_exp_dir() . '/eligibility.php';
    if (!is_file($filename)) return true;
    
    require_once $filename;
    if (!function_exists('eligibility_test')) report_eligibility_error();
    
    return eligibility_test($metadata);
}

function report_eligibility_error() {
    throw new Exception(
        'File "eligibility.php" inside the experiment folder "' . CURR_EXP
      . '" must contain a function called "eligibility_test"'
    );
}

function redirect($page) {
    if ($page === 'done') {
        require_once ROOT . '/PHP/definitions/done.php';
        
        if (has_next_experiment()) {
            redirect_to_next_experiment();
        }
    }
    
    $path = get_url_to_root() . '/' . get_page_path($page);
    header("Location: $path");
    exit;
}

function get_config($setting = null) {
    static $config = null;
    
    if ($config === null) {
        $config = [];
        $paths = [ROOT . '/Experiments/Config.ini'];
        
        if (defined('CURR_EXP')) {
            $paths[] = ROOT . '/Experiments/' . CURR_EXP . '/Config.ini';
        }
        
        foreach ($paths as $path) {
            $config = array_merge($config, Parse::fromConfig($path));
        }
    }
    
    if ($setting === null) {
        return $config;
    } else if (!isset($config[$setting])) {
        trigger_error("missing config: $setting", E_USER_ERROR);
    } else {
        return $config[$setting];
    }
}

function get_exp_data_dir() {
    return ROOT . '/Data/' . CURR_EXP . '-Data';
}

function get_requested_page_path() {
    return ROOT . '/Pages/' . PAGE . '.php';
}

function get_list_of_experiments() {
    $list = [];
    $exps_dir = ROOT . '/Experiments';
    
    foreach (scandir($exps_dir) as $entry) {
        if ($entry === '.' or $entry === '..') continue;
        
        if (is_dir("$exps_dir/$entry")) $list[] = $entry;
    }
    
    return $list;
}

function get_page_path($page) {
    return (defined('CURR_EXP') ? CURR_EXP : 'err') . "/$page/";
}

function get_exp_dir() {
    return ROOT . '/Experiments/' . CURR_EXP;
}

function get_trial_types() {
    static $trial_types = null;
    
    if ($trial_types === null) {
        $trial_types = [];
        $trial_types_dir = ROOT . '/TrialTypes';
        
        foreach (scandir($trial_types_dir) as $entry) {
            if ($entry === '.' or $entry === '..') continue;
            if (!is_file("$trial_types_dir/$entry/display.php")) continue;
            
            $trial_types[strtolower($entry)] = "$trial_types_dir/$entry";
        }
    }
    
    return $trial_types;
}

function get_trial_type_dir($trial_type) {
    $types = get_trial_types();
    $type_lower = strtolower($trial_type);
    
    if (!isset($types[$type_lower])) {
        throw new Exception("bad trial type: empty string");
    }
    
    return $types[$type_lower];
}

function get_trial_type_dir_url($trial_type) {
    return substr(get_trial_type_dir($trial_type), strlen(ROOT) + 1);
}

function get_trial_file($trial_type, $file) {
    return get_trial_type_dir($trial_type) . "/$file";
}

function get_post_trials_from_row($row) {
    $trials = [];
    
    foreach ($row as $col => $val) {
        if (preg_match('/^Post (\\d+) (.+)/', $col, $matches)) {
            $trials[$matches[1]][$matches[2]] = $val;
        } else {
            $trials[0][$col] = $val;
        }
    }
    
    ksort($trials);
    check_for_errors_in_post_trial_columns($trials);
    return $trials;
}

function check_for_errors_in_post_trial_columns($trials) {
    for ($i = 0, $c = count($trials); $i < $c; ++$i) {
        if (!isset($trials[$i])) {
            throw new Exception(
                'You must have post trial levels that are sequential (e.g, if '
              . 'a "Post 2 ..." column exists, then so must a "Post 1 ..." '
              . 'column)'
            );
        }
    }
}

function get_user_session_filename($username = null) {
    if ($username === null) $username = $_SESSION['Username'];
    
    return get_data_filename('sess', ['Username' => $username]);
}

function has_session($username) {
    return is_file(get_user_session_filename($username));
}

function get_session($username) {
    return read_session_file(get_user_session_filename($username));
}

function save_session($session) {
    $filename = get_user_session_filename($session['Username']);
    $dir = dirname($filename);
    
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    write_session_file($filename, $session);
}

function read_session_file($filename) {
    return unserialize(gzinflate(file_get_contents($filename)));
}

function write_session_file($filename, $data) {
    file_put_contents($filename, gzdeflate(serialize($data)));
}

function get_trial_proc_values($trial_set, $post_level) {
    if (!isset($trial_set[$post_level])) return false;
    
    $trial_set[0] += get_procedure_default_values();
    $values = [];
    
    for ($post = $post_level; $post >= 0; --$post) {
        foreach ($trial_set[$post] as $col => $val) {
            if (!isset($values[$col]) or $values[$col] === '*inherit') {
                $values[$col] = $val;
            }
        }
    }
    
    foreach ($values as $col => $val) {
        if ($val === '*inherit') report_inheritance_error($post_level, $col);
    }
    
    return $values;
}

function get_trial_proc_value($trial_set, $post_level, $col) {
    $trial_set[0] += get_procedure_default_values();
    
    for ($i = $post_level; $i >= 0; --$i) {
        if (isset($trial_set[$i][$col]) and $trial_set[$i][$col] !== '*inherit') {
            return $trial_set[$i][$col];
        }
    }
    
    report_inheritance_error($post_level, $col);
}

function get_procedure_default_values() {
    return [
        'Stim Rows' => '',
        'Max Time'  => '',
        'Min Time'  => '',
        'Settings'  => '',
        'Text'      => ''
    ];
}

function report_inheritance_error($post, $col) {
    $full_col = $post === 0 ? $col : "Post $post $col";
    throw new Exception("Cannot find inherited value for column '$full_col', no previous value exists");
}

function check_if_logged_in() {
    if (!isset($_SESSION['Username'])) redirect('login');
}

function parse_trial_settings($settings_val, $trial_type) {
    try {
        $settings = tcon\tcon_parse($settings_val);
    } catch (Exception $e) {
        $settings_html = htmlspecialchars($settings_val);
        $msg = $e->getMessage();
        throw new Exception("Failed to parse trial settings, '$settings_html', with error message:\n $msg");
    }
    
    $settings = get_settings_with_defaults($settings, $trial_type);
    
    // check for settings with null value, count those as missing requirements
    foreach ($settings as $key => $val) {
        if ($val === null) throw missing_setting_exception($trial_type, $key);
    }
    
    return $settings;
}

function missing_setting_exception($trial_type, $key) {
    $msg = "The trial type '$trial_type' requires a setting '$key' to be "
         . "provided in the Settings column (e.g., '$key': 'some value...')";
    throw new Exception($msg);
}

function get_settings_with_defaults($provided_vals, $trial_type) {
    // im going to treat this like python functions, where numeric settings
    // will be assigned to the setting key with that position
    $defaults = get_default_settings($trial_type);
    $settings = [];
    $pos = 0;
    
    foreach ($defaults as $key => $default_val) {
        $settings[$key] = $default_val;
        
        if (isset($provided_vals[$key])) {
            $settings[$key] = $provided_vals[$key];
            unset($provided_vals[$key]);
        } else if (isset($provided_vals[$pos])) {
            $settings[$key] = $provided_vals[$pos];
            unset($provided_vals[$pos]);
            ++$pos;
        }
    }
    
    // remaining settings will simply be pushed onto array
    foreach ($provided_vals as $key => $val) {
        if (gettype($key) === 'integer') {
            $settings[] = $val;
        } else {
            $settings[$key] = $val;
        }
    }
    
    return $settings;
}

function get_default_settings($trial_type) {
    $filename = get_trial_file($trial_type, 'settings.tcon');
    
    if (!is_file($filename)) return[];
    
    try {
        $defaults = tcon\tcon_parse(file_get_contents($filename));
    } catch (Exception $e) {
        $msg = $e->getMessage();
        throw new Exception("Failed to parse tcon file 'settings.tcon' in the '$trial_type' trial type folder, error message:\n $msg");
    }
    
    foreach ($defaults as $key => $val) {
        if (gettype($key) === 'integer') {
            unset($defaults[$key]);
            $defaults[$val] = null;
        }
    }
    
    return $defaults;
}

function get_array_average($values) {
    $numbers = get_numeric_values(get_flat_array($values));
    
    return count($numbers) === 0
         ? NAN
         : array_sum($numbers) / count($numbers);
}

function get_numeric_values($arr) {
    $numbers = [];
    
    foreach ($arr as $val) {
        if (is_numeric($val)) $numbers[] = $val;
    }
    
    return $numbers;
}

function get_array_sum($values) {
    $numbers = get_numeric_values(get_flat_array($values));
    
    return count($numbers) === 0
         ? 0
         : array_sum($numbers);
}
