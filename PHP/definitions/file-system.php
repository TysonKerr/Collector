<?php
/**
 * gets a 2d array from a csv file
 * @param string $filename
 * @return array
 */
function read_csv($filename) {
    $handle = fopen_errorful($filename, 'r');
    $lines = get_csv_lines($handle);
    
    if (count($lines) < 2) throw new Exception("Not enough data to read csv in file: $filename");
    
    $headers = $lines[0];
    $headers_count = count($headers);
    $headers_flip = array_flip($headers);
    $next_col_index = 0;
    
    foreach ($lines as $line) {
        $line_count = count($line);
        
        while ($line_count > $headers_count) {
            while (isset($headers_flip[$next_col])) {
                ++$next_col;
            }
            
            $headers[] = $next_col;
            $headers_flip[$next_col] = $headers_count;
            ++$headers_count;
        }
    }
    
    $data = [];
    
    foreach ($lines as $i => $line) {
        if ($i === 0) continue; // headers
        
        $row = [];
        
        foreach ($headers as $i => $header) {
            $row[$header] = isset($line[$i]) ? $line[$i] : '';
        }
        
        $data[] = $row;
    }
    
    fclose($handle);
    return $data;
}
/**
 * gets an array of csv files, as read by fgetcsv
 * @param resource $handle a handle created by fopen()
 * @return array
 */
function get_csv_lines($handle) {
    $lines = [];
    $encoding = get_config('encoding');
    
    while ($line = get_next_csv_line($handle, $encoding)) {
        $lines[] = $line;
    }
    
    return $lines;
}

function get_next_csv_line($handle, $encoding) {
    while ($line = fgetcsv($handle)) {
        if ($line[0] === null) continue;
        
        $is_empty = true;
        
        foreach ($line as $i => $cell) {
            $line[$i] = trim(iconv($encoding, 'UTF-8', $cell));
            
            if ($line[$i] !== '') $is_empty = false;
        }
        
        if (!$is_empty) return $line;
    }
    
    return $line; // will return false at end of file, null for unreadable handle
}
/**
 * writes data to a csv
 * @param string $filename
 * @param array $data an associative array, with keys being column headers
 */
function write_csv($filename, $data) {
    // this function is going to be an annoyingly long procedural-style function
    // because i hate dealing with performance issues with file writing
    // expecially because this sometimes gets called hundreds of times in a row...
    $dir = dirname($filename);
    
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    $encoding = get_config('encoding');
    // make sure data is 2d array
    if (!is_array($data)) $data = [$data];
    if (!is_array(reset($data))) $data = [$data];
    
    // get all headers
    $headers = [];
    
    foreach ($data as $row) {
        foreach ($row as $col => $val) {
            $headers[$col] = true;
        }
    }
    
    $headers = array_keys($headers);
    
    foreach ($headers as $i => $header) {
        $headers[$i] = iconv('UTF-8', $encoding, $header);
    }
    
    $headers_flipped = array_flip($headers);
    
    // get file handle
    if (!is_file($filename)) {
        $handle = fopen_errorful($filename, 'w');
        fputcsv($handle, $headers);
    } else {
        $handle = fopen_errorful($filename, 'r+');
        $old_headers = array_flip(fgetcsv($handle));
        $new_headers = array_diff_key($headers_flipped, $old_headers);
        $headers_flipped = $old_headers + $new_headers;
        $headers = array_keys($headers_flipped);
        
        if (count($new_headers) > 0) {
            $old_data = stream_get_contents($handle);
            rewind($handle);
            fputcsv($handle, $headers);
            fwrite($handle, $old_data);
        }
        
        fseek($handle, 0, SEEK_END);
    }
    
    foreach ($data as $row) {
        $sorted = [];
        
        foreach ($headers as $header) {
            $val = isset($row[$header]) ? $row[$header] : '';
            $val = is_array($val) ? json_encode($val) : $val;
            $val = iconv('UTF-8', $encoding, $val);
            $sorted[] = $val;
        }
        
        fputcsv($handle, $sorted);
    }
    
    fclose($handle);
}
/**
 * fopen() but triggers error if it fails
 * @param string $filename
 * @param string $mode same as fopen()
 * @return resource
 */
function fopen_errorful($filename, $mode) {
    $handle = fopen($filename, $mode);
    
    if (!$handle) throw new Exception("Cannot open file: $filename");
    
    return $handle;
}
/**
 * removes insignificant components of a file path, such as 'dir/../'
 * @param string $path The path to clean
 * @return string
 */
function cleanPath ($path) {
    // Normally, file functions can parse both '\' and '/'
    // as directory separators, but explode() can't, so we
    // will convert all possible separators to standard '/'
    $cleanSeparators = strtr($path, '\\', '/');
    $pathComponents  = explode('/', $path);
    
    // Now lets clean up the path components a little bit.
    // First, create an array to populate with the indices
    // of actual directories, as opposed to '.' and '..'
    $dirs = array();
    // then, start scanning components and removing unneeded
    foreach ($pathComponents as $i => &$comp) {
        $comp = trim($comp);
        // the current directory, '.', is trivial
        if ($comp === '.') {
            unset ($pathComponents[$i]);
            continue;
        }
        // an empty component, '', is also trivial, except in
        // the case where it is the first component, indicating
        // that this is an absolute path in a unix-like OS
        if ($i > 0 AND $comp === '') {
            unset ($pathComponents[$i]);
            continue;
        }
        // The other situation to check for is the case when a
        // directory is entered and then exited, using the parent
        // directory (e.g. 'dir/../').
        // However, if this is used to navigate above the current
        // directory with a relative path, the parent directory
        // component should be left in (e.g., '../file.php').
        // We will keep track if there is a parent directory of
        // the '..' component, keeping in mind that there might
        // be trivial directories (e.g. '.') in between, so we
        // can't just use $i and --$i
        if ($comp === '..') {
            // if we previously navigated into a folder, then its
            // index would have been added to $dirs, but made
            // irrelevant by navigating out with the current '..'
            if ($dirs !== array()) {
                $currentDirIndex = array_pop($dirs);
                unset($pathComponents[$currentDirIndex],
                      $pathComponents[$i]);
            }
        } else {
            // keep track of indices of actual directories that
            // might be rendered irrelevant by an upcoming '..'
            $dirs[] = $i;
        }
    }
    unset($comp);
    $pathComponents = implode('/', $pathComponents); // rejoin into string
    return $pathComponents;
}
/**
 * searches a given directory for a target file or directory
 * @param string $dir The dir to search inside
 * @param string $target The file or directory to find
 * @param bool $findAltExt whether or not to ignore file extensions
 * @param int $findDir Set 0 to only find files
 *                     Set 1 to find files and directories
 *                     Set 2 to only find directories
 * @return string|bool
 */
function find_in_dir($dir, $target, $findAltExt = true, $findDir = 1) {
    // this function is expecting valid file paths
    // so, if you need to trim or remove bad characters,
    // do that before sending them to this function
    
    $findDir = (int) $findDir; // 0: no, 1: yes, 2: only
    
    // efficiency checks
    if (!is_dir($dir) AND $dir !== '') {
        return false; // come on now...
    }
    $test = $dir . '/' . $target;
    if (is_file($test)) {
        if ($findDir < 2) {
            return $target;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    if (is_dir($test)) {
        if ($findDir > 0) {
            return $target;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    
    // we need to search the directory, so lets check for
    // existence and permissions (which might be denied for '/home/')
    if (!is_readable($dir)) {
        // we can't scan the dir, but we can guess by removing
        // the file extension
        $targets = array(strtolower($target), strtoupper($target));
        foreach ($targets as $t) {
            $test = $dir . '/' . $t;
            if (   (is_file($test) AND $findDir < 2)
                OR (is_dir( $test) AND $findDir > 0)
            ) {
                return $t;
            }
        }
        if ($findAltExt AND (strpos($target, '.') !== false)) {
            $target = substr($target, 0, strrpos($target, '.'));
            $targets = array(strtolower($target), strtoupper($target));
            foreach ($targets as $t) {
                $test = $dir . '/' . $t;
                if (   (is_file($test) AND $findDir < 2)
                    OR (is_dir( $test) AND $findDir > 0)
                ) {
                    return $t;
                }
            }
        }
        // else, we can't scan, so we must give up
        return false;
    }
    
    $scandir = scandir($dir);
    $lowerTarget = strtolower($target);
    foreach ($scandir as $entry) {
        $lowerEntry = strtolower($entry);
        if ($lowerEntry === $lowerTarget) {
            $test = $dir . '/' . $entry;
            if (   (is_file($test) AND $findDir < 2)
                OR (is_dir( $test) AND $findDir > 0)
            ) {
                return $entry;
            }
        }
    }
    
    // still haven't found it yet, try alt extensions
    if ($findAltExt) {
        if (strpos($lowerTarget, '.') !== false) {
            $lowerTarget = substr($lowerTarget, 0, strrpos($lowerTarget, '.'));
        }
        foreach ($scandir as $entry) {
            $lowerEntry = strtolower($entry);
            if (strpos($lowerEntry, '.') !== false) {
                $lowerEntry = substr($lowerEntry, 0, strrpos($lowerEntry, '.'));
            }
            if ($lowerEntry === $lowerTarget) {
                $test = $dir . '/' . $entry;
                if (   (is_file($test) AND $findDir < 2)
                    OR (is_dir( $test) AND $findDir > 0)
                ) {
                    return $entry;
                }
            }
        }
    }
    
    // failed to find match, return false
    return false;
}
/**
 * Given a string that is presumably the start of a file path,
 * this will convert the path component into the absolute root of
 * this OS if the given string looks like a root directory
 * otherwise, returns false
 * @param string $dir the path component to examine
 * @return string|bool
 */
function convertAbsoluteDir($dir) {
    // this function expects just the first component of a path
    if ($dir === '' OR substr($dir, 1, 1) === ':') {
        return substr(realpath('/'), 0, -1); // return root without trailing slash
    } else {
        return false;
    }
}
/**
 * Finds a path to a target file, checking the filename and each directory
 * name in the path case-insensitively. If a target file is found, returns
 * the path with the correct, existing casing. Otherwise, returns false.
 * Optionally searches for files with the same name but alternative
 * extensions (defaults to true). Optionally searches for only files
 * ($findDir = 0), files and directories ($findDir = 1), or only
 * directories ($findDir = 2)
 *
 * @param string $path The file to search for.
 * @param bool $findAltExtensions Set false for strict extension checking.
 * @param int  $findDir Set 0 to only return paths to actual files,
 *                      Set 1 to return paths to both files and directories
 *                      Set 2 to only return paths to directories
 * @return string|bool
 */
function fileExists ($path, $findAltExt = true, $findDir = 1) {
    // This function is expecting valid path names.
    // So, if you need to trim or remove bad characters,
    // do that before sending them to this function
    
    // guard against bad input (such as a null path)
    $findDir = (int) $findDir; // 0: no, 1: yes, 2: only
    $path    = (string) $path;
    if ($path === '') { return false; }
    
    // efficiency checks
    if (is_file($path)) {
        if ($findDir < 2) {
            return $path;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    if (is_dir($path)) {
        if ($findDir > 0) {
            return $path;
        } elseif (!$findAltExt) {
            return false;
        }
    }
    
    // -convert Windows directory separators '\' to standard '/'
    // -remove unneeded path elements, such as '.' or 'dir/../'
    // -remove trailing slash
    // -trim each component
    // -this is so we can explode by '/' and correctly identify
    //  each path components (e.g., 'one' and 'two' from 'one\two')
    $path = cleanPath($path);
    $path = explode('/', $path);
    
    // if they only supplied a single component, there is the unlikely
    // case that they are searching for the root directory
    // Let's check for that, before assuming that they are looking for
    // a file or directory in the current working directory
    if (count($path) === 1) {
        $absDir = convertAbsoluteDir($path[0]);
        if ($absDir !== false) {
            // in this case, we have an absolute path of a root directory
            if ($findDir === 0) {
                return false;
            } else {
                // this will give them the actual root directory for this OS
                return $absDir;
            }
        } else {
            // in this case, just try to find a relative target
            return find_in_dir('.', $path[0], $findAltExt, $findDir);
        }
    }
    
    // we are going to search for the final component a bit differently,
    // since it can be either a directory or a file, so lets pull that off
    $finalComponent = array_pop($path);
    
    // now we need to find the directory portion of the path
    // if is_dir() cannot find it, then we will start pulling off
    // components from the end of the path until we get a directory
    // we can locate
    $dirsNotFound = array();
    while (!is_dir(implode('/', $path))) {
        // for the first dir, check if its an absolute or relative dir
        if (count($path) === 1) {
            $absDir = convertAbsoluteDir($path[0]);
            if ($absDir !== false) {
                // if absolute, set the starting path to the actual root
                $path = array($absDir);
            } else {
                $dirsNotFound[] = array_pop($path);
            }
            break; // checking first dir, can't go back any more
        } else {
            // move last dir in $path to start of $dirsNotFound
            $dirsNotFound[] = array_pop($path);
        }
    }
    $dirsNotFound = array_reverse($dirsNotFound); // correct order of dirs
    
    // if $path is empty, not even the first dir could be identified
    // so, we will assume its a relative path
    // otherwise, we are going to use what we could
    if ($path === array()) {
        $baseDir = '.';
    } else {
        $baseDir = implode('/', $path);
    }
    
    // now lets do a case-insensitive search for the rest of the dirs
    foreach ($dirsNotFound as $targetDir) {
        // use find_in_dir, but only search for dirs
        $search = find_in_dir($baseDir, $targetDir, false, 2);
        if ($search === false) { return false; }
        $baseDir .= '/' . $search;
    }
    
    // Huzzah! At this point, we should have found our directory,
    // and we just need to search for the final component
    $finalSearch = find_in_dir($baseDir, $finalComponent, $findAltExt, $findDir);
    if ($finalSearch === false) {
        return false;
    } else {
        $existingPath = $baseDir . '/' . $finalSearch;
        if (substr($existingPath, 0, 2) === './') {
            $existingPath = substr($existingPath, 2);
        }
        return $existingPath;
    }
}

function read_json($filename) {
    return json_decode(file_get_contents($filename), true);
}

function get_merged_csvs($csvs) {
    $headers = get_headers_of_csvs($csvs);
    $merged = [];
    
    foreach ($csvs as $csv) {
        foreach ($csv as $row) {
            $sorted = [];
            
            foreach ($headers as $header) {
                $sorted[$header] = isset($row[$header]) ? $row[$header] : '';
            }
            
            $merged[] = $sorted;
        }
    }
    
    return $merged;
}

function get_headers_of_csvs($csvs) {
    $headers = [];
    
    foreach ($csvs as $csv) {
        foreach ($csv[0] as $header => $_) {
            $headers[$header] = true;
        }
    }
    
    return array_keys($headers);
}

function read_multiple_csvs($filenames) {
    $csvs = [];
    
    foreach ($filenames as $filename) {
        $csvs[] = read_csv($filename);
    }
    
    return get_merged_csvs($csvs);
}
