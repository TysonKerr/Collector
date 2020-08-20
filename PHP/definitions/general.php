<?php

/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
*/
require __DIR__ . '/Collector.php';
require __DIR__ . '/display.php';
require __DIR__ . '/file-system.php';
require __DIR__ . '/shuffles.php';
/**
 * Turns a string like '2,4::6' into an array like [2, 4, 5, 6].
 * @param string $string A string indicating how the array should be constructed.
 * @param string $separator A string indicating how the ranges are separated.
 * @param string $rangeIndicator A string that symbolizes a continuous range.
 * @return array
 */
function get_range($string, $separator = ',', $range_indicator = '::')
{
    $output = array();
    $ranges = explode($separator, $string);
    
    foreach ($ranges as $range) {
        // get the end points of the range
        $end_points = explode($range_indicator, $range);
        $end_points = array_map('trim', $end_points);
        
        // update the output array
        if (count($end_points) === 1) {
            $output[] = $end_points[0];
        } else {
            $range_end = end($end_points);
            $step = 1;
            $end_exploded = explode('#', $range_end);
            $range_end = trim($end_exploded[0]);
            
            if (isset($end_exploded[1]) AND is_numeric($end_exploded[1])) {
                $step = trim($end_exploded[1]);
            }
            
            foreach (range($end_points[0], $range_end, $step) as $e) {
                $output[] = $e;
            }
        }
    }
    
    return $output;
}
/**
 * Generates a random, lowercase alphanumeric string.
 * @param int $length Optional length of string. Defaults to 10.
 * @return string
 */
function rand_string($length = 10)
{
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $max_char_index = strlen($chars) - 1;
    $randString = '';
    for ($i = 0; $i < $length; $i++) {
        $randString .= $chars[mt_rand(0, $max_char_index)];
    }
    return $randString;
}
/**
 * Sorts an array to match the order of a template array. Keys in the template
 * that are not present in the target are given null values in the target array.
 * @param array $array The array to sort.
 * @param array $template The sorting template.
 * @return array
 */
function get_sorted_array(array $array, array $template)
{
    $out = array();
    
    foreach ($template as $key => $_) {
        $out[$key] = isset($array[$key]) ? $array[$key] : null;
    }
    
    return $out;
}

function get_array_with_prefixed_keys($arr, $prefix) {
    $out = [];
    
    foreach ($arr as $key => $val) {
        $out[$prefix . $key] = $val;
    }
    
    return $out;
}
/**
 * Removes the label from a string.
 * @param string $input The string to strip the label from.
 * @param string $label The label to strip.
 * @param bool $extendLabel Checks if the label is followed by certain.
 * characters removes them as well. Set false for strict matching to $label.
 * @return mixed
 */
function removeLabel($input, $label, $extendLabel = true)
{
    $inputString = trim($input);
    $inputLower = strtolower($inputString);
    $labelClean = strtolower(trim($label));
    $trimLength = strlen($labelClean);
    if (substr($inputLower, 0, $trimLength) !== $labelClean) {
        return false;
    } else {
        if ($extendLabel) {
            foreach(['s', ' ', ':', '='] as $char) {
                if (substr($inputLower, $trimLength, 1) === $char) {
                    ++$trimLength;
                }
            }
        }
        $output = trim(substr($inputString, $trimLength));
        if (($output === '') || ($output === false)) {
            return true;
        }
        return $output;
    }
}
/**
 * Strips the URL scheme (HTTP, HTTPS) from a URL and ensures that the URL
 * starts with '//'.
 * @param string $url
 * @return string
 */
function stripUrlScheme($url)
{
    $stripped = preg_replace("@^(?:https?:)?//@", "//", $url);
    if (0 !== strpos($stripped, '//')) {
        $stripped = '//'.$stripped;
    }
    return $stripped;
}

/**
 * Returns a normalized YouTube link. All links are converted to YouTube's
 * embed format and stripped of all parameters passed as queries.
 * @param string $url The YouTube URL to clean-up
 * @return string
 */
function youtubeUrlCleaner($url, $justReturnId = false)
{
    $urlParts = parse_url(stripUrlScheme($url));
    
    if ('youtu.be' === strtolower($urlParts['host'])) {
        // share links: youtu.be/[VIDEO ID]
        $id = ltrim($urlParts['path'], '/');
    } else if (stripos($urlParts['path'], 'watch') === 1) {
        // watch links: youtube.com/watch?v=[VIDEO ID]
        parse_str($urlParts['query']); 
        $id = $v;
    } else {
        // embed links: youtube.com/embed/[VIDEO ID]
        // API links: youtube.com/v/[VIDEO ID]
        $pathParts = explode('/', $urlParts['path']);
        $id = end($pathParts);
    }
    
    if ($justReturnId) {
        return $id;
    } else {
        return '//www.youtube.com/embed/'.$id;
    }
}
/**
 * Returns a normalized Vimeo link. All links are converted to Vimeo's
 * embed format and stripped of all parameters passed as queries.
 * @param string $url The Vimeo URL to clean-up
 * @return string
 */
function vimeoUrlCleaner($url)
{
    $urlParts = parse_url(stripUrlScheme($url));
    $pathParts = explode('/', $urlParts['path']);
    $id = end($pathParts);
    
    return '//player.vimeo.com/video/'.$id;
}
/**
 * Determines if a file is local or not.
 * @param string $path The path to check
 * @return boolean
 */
function isLocal($path)
{
    return !filter_var($path, FILTER_VALIDATE_URL);
}

function invert_2d_array($arr) {
    $output = [];
    
    foreach ($arr as $i => $vals) {
        foreach ($vals as $j => $val) {
            $output[$j][$i] = $val;
        }
    }
    
    return $output;
}

function get_link($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $smart_path = get_smart_cached_path($filename);
    
    if ($ext === 'js') {
        return "<script src='$smart_path'></script>";
    } else if ($ext === 'css') {
        return "<link rel='stylesheet' href='$smart_path'>";
    } else {
        return $smart_path;
    }
}

function get_smart_cached_path($filename) {
    $path = get_url_to_file($filename);
    $m = filemtime($filename);
    return "$path?v=$m";
}

function get_url_to_file($filename) {
    $path_to_root = ROOT;
    $url_to_root = get_url_to_root();
    $realpath = realpath($filename); // in case it is a relative path
    
    if (!$realpath) trigger_error("Filename $filename not a real file", E_USER_ERROR);
    
    $url = substr(str_replace([$path_to_root, '\\'], ['', '/'], $realpath), 1);
    return $url;
}

function get_url_to_root() {
    if (defined('URL_TO_ROOT')) return URL_TO_ROOT;
    
    // the below assumes that htaccess hasn't modified the path in any way
    // that changes the number of directory traversals to the root.
    // if you want to use htaccess to create artificial paths,
    // make sure to define URL_TO_ROOT
    $root = ROOT;
    $cd = getcwd();
    $url_to_root = '.';
    
    while ($cd !== $root) {
        $cd = dirname($cd);
        $url_to_root .= '/..';
    }
    
    return $url_to_root;
}

function get_server_input($property) {
    // on netbeans, the server gives a warning if you try to use direct access
    // for _get, _post, _server, etc.
    // so we want to use filter_input() instead
    // however, for fastcgi servers, filter_input for _SERVER always returns
    // null, so we cant use it there
    $value = filter_input(INPUT_SERVER, $property);
    
    if ($value === null and isset($_SERVER[$property])) {
        return $_SERVER[$property];
    }
    
    return $value;
}

function get_date() {
    // https://www.php.net/manual/en/datetime.format.php
    return date('Y-m-d H:i:s M d, D, h:i a');
}

function get_longest_subarray_count($array) {
    $count = 0;
    
    foreach ($array as $sub) {
        $count = max($count, count($sub));
    }
    
    return $count;
}
