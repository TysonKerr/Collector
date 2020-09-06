<?php

/*
 * Copyright (c) 2020 TysonKerr
 * 
 * MIT License
 *
 * https://github.com/TysonKerr/tcon
 */

namespace tcon;
class Exception extends \Exception {}

const MAX_DEPTH = 512;

const STRING_ENCLOSURE = 1;
const OBJECT_START     = 2;
const OBJECT_END       = 3;
const KEY_SEPARATOR    = 4;
const VAL_SEPARATOR    = 5;
const LINE_AS_LIST     = 6;
const LINE_AS_STRING   = 7;
const ROW_END          = 8;
const MULTI_LINE_STRING = 9;

const SPECIAL_CHARS = [
    '"' => STRING_ENCLOSURE,
    "'" => STRING_ENCLOSURE,
    "`" => STRING_ENCLOSURE,
    '[' => OBJECT_START,
    ']' => OBJECT_END,
    '{' => OBJECT_START,
    '}' => OBJECT_END,
    ',' => VAL_SEPARATOR,
    ':' => KEY_SEPARATOR,
    '%' => MULTI_LINE_STRING,
];

const ESCAPED_CHARS = [
    'b' => "\010",
    'f' => "\f",
    'n' => "\n",
    'r' => "\r",
    't' => "\t",
];

function tcon_parse($str, $as_array = true) {
    $vals = parse_array($str);
    
    if ($as_array) return $vals;
    
    switch (count($vals)) {
        case 0: return null; // if nothing found, return null
        case 1: return array_key_exists(0, $vals) ? $vals[0] : $vals; // if only 1 thing found, without a specified key, return that thing
        default: return $vals; // if many things found, return implied array
    }
}

function parse_array($str, &$i = 0, $depth = 0) {
    if (++$depth > MAX_DEPTH) throw new Exception('too deep');
    
    $vals = [];
    $strings = [];
    $has_key = false;
    $parse_last_str = false;
    
    while (isset($str[$i])) {
        $next_val = read_next_val($str, $i, $depth, $enclosed);
        
        if ($next_val === null or $next_val === OBJECT_END) {
            break;
        } else if ($next_val === KEY_SEPARATOR) {
            if (!isset($strings[0]) or $has_key) {
                throw new Exception('No key defined');
            }
            
            $has_key = true;
        } else {
            store_val($next_val, $vals, $strings, $has_key, $parse_last_str, $enclosed);
            $has_key = false;
        }
    }
    
    if ($has_key) throw new Exception('No value defined');
    
    if (isset($strings[0])) add_property(array_pop($strings), $vals, $strings, $parse_last_str);
    
    return $vals;
}

function read_next_val($str, &$i, $depth, &$enclosed) {
    $enclosed = true;
    
    while (isset($str[$i]) and skip_comments($str, $i)) {
        $char = $str[$i++];
        
        if (ctype_space($char)) continue;
        
        switch (SPECIAL_CHARS[$char] ?? 0) {
            case VAL_SEPARATOR:    continue 2; // treat as whitespace
            case KEY_SEPARATOR:    return KEY_SEPARATOR;
            case OBJECT_END:       return OBJECT_END;
            case OBJECT_START:     return parse_array($str, $i, $depth);
            case STRING_ENCLOSURE: return parse_string($str, $i, $char);
            case MULTI_LINE_STRING: return parse_ml_string($str, $i, $char);
            default: $enclosed = false; return parse_string($str, $i);
        }
    }
    
    return null; // end of string
}

function skip_comments($str, &$i) {
    while (isset($str[$i])) {
        $char = $str[$i];
        $next = $str[$i + 1] ?? null;
        
        if ($char === '#' or ($char === '/' and $next === '/')) {
            do {
                $char = $str[++$i] ?? null;
            } while (isset($char) and $char !== "\r" and $char !== "\n");
        } else if ($char === '/' and $next === '*') {
            do {
                $char = $str[++$i] ?? null;
                $next = $str[$i + 1] ?? null;
            } while (isset($char) and ($char !== '*' or $next !== '/'));
            
            if (isset($next)) $i += 2;
            
            continue;
        }
        
        break;
    }
    
    return isset($str[$i]);
}

function parse_ml_string($str, &$i, $char) {
    $string = parse_string($str, $i, $char);
    $string = str_replace(["\r\n", "\n"], "\r", $string);
    $lines = explode("\r", $string);
    $last_line = array_pop($lines);
    $indent = strlen($last_line);
    $line_count = count($lines);
    $trimmed_strings = [];
    
    for ($j = 1; $j < $line_count; ++$j) {
        $trimmed_strings[] = substr($lines[$j], $indent);
    }
    
    return implode("\n", $trimmed_strings);
}

function parse_string($str, &$i, $enclosure = false) {
    $len = 0;
    // we incremented $i when we were reading the char that we detected as a
    // string, so we need to re-read that for processing here
    $i -= $enclosure ? 0 : 1;
    $start = $i;
    $escaped = false;
    $substrs = [];
    
    do {
        $char = $str[$i];
        
        if ($escaped) {
            if ($char === "\r" and ($str[$i + 1] ?? 0) === "\n") {
                ++$i;
                $len += 2;
            } else if (isset(ESCAPED_CHARS[$char])) {
                $substrs[] = ESCAPED_CHARS[$char];
                $start++;
            } else {
                ++$len;
            }
            
            $escaped = false;
            continue;
        }
        
        if ($enclosure === false) {
            if (isset(SPECIAL_CHARS[$char])
                or $char === "\r" or $char === "\n"
                or $char === '#' or ($char === '/' and (isset($str[$i+1])
                  and ($str[$i+1] === '/' or $str[$i+1] === '*')))
            ) {
                break;
            }
        } else if ($char === $enclosure) {
            ++$i;
            break;
        }
        
        if ($char === '\\') {
            $substrs[] = substr($str, $start, $len);
            $start = $i + 1;
            $len = 0;
            $escaped = true;
            continue;
        }
        
        ++$len;
    } while(isset($str[++$i]));
    
    $substrs[] = substr($str, $start, $len);
    $string = implode('', $substrs);
    
    if ($enclosure === false) $string = trim($string);
    
    return $string;
}

function store_val($val, &$vals, &$strings, $has_key, &$parse_last_str, $enclosed) {
    if ($has_key) {
        if (is_string($val)) {
            $strings[] = $val;
        } else {
            add_property($val, $vals, $strings, false);
        }
    } else {
        if (isset($strings[0])) {
            add_property(array_pop($strings), $vals, $strings, $parse_last_str);
        }
        
        if (is_string($val)) {
            $strings[] = $val;
        } else {
            $vals[] = $val;
        }
    }
    
    $parse_last_str = !$enclosed;
}

function add_property($val, &$vals, &$keys, $parse_last_str) {
    if ($parse_last_str) $val = parse_value($val);
    
    if (isset($keys[0])) {
        $target =& $vals;
        $last_key = array_pop($keys);
        
        foreach ($keys as $key) {
            if (!isset($target[$key]) or !is_array($target[$key])) {
                $target[$key] = [];
            }
            
            $target =& $target[$key];
        }
        
        $target[$last_key] = $val;
        $keys = [];
    } else {
        $vals[] = $val;
    }
}

function parse_value($val) {
    $lower = strtolower($val);
    
    switch ($lower) {
        case 'null':  return null;
        case 'true':  return true;
        case 'false': return false;
    }
    
    if (is_numeric($val)) return $val - 0;
    
    return $val;
}
