<?php
    ini_set('auto_detect_line_endings', true);          // fixes problems reading files saved on mac
    session_start();                                    // start the session at the top of each page
    
    error_reporting(-1);
    
    $testFile = 'Code/fileLocations.php';
    $_rootF = '';                                       // $_rootF helps find PHP files
    $i = 0;
    while (!is_file($_rootF . $testFile) AND $i<99) {
        $_rootF .= '../';
        ++$i;
    }
    
    require $_rootF . 'Code/fileLocations.php';         // sends file to the right place
    require $_rootF . $codeF . 'CustomFunctions.php';
    require $_rootF . $expFiles . 'Settings.php';       // experiment variables
    
    if ($_rootF === '') {
        $_codeF = $codeF;                               // $_codeF can help link JS/CSS
    } else {
        $_codeF = '';
    }
    
    $up     = $_rootF;                                  // update $up to match current location
    $dataF .= $experimentName . '-Data/';                // So data will appear in Data/Collector-Data/
    
    $path = $up . $dataF . $dataSubFolder . $extraDataF;
    
    $demoPath        = $path . $demographicsFileName        . $outExt;
    $statusBeginPath = $path . $statusBeginFileName         . $outExt;
    $statusEndPath   = $path . $statusEndFileName           . $outExt;
    $fqDataPath      = $path . $finalQuestionsDataFileName  . $outExt;
    $instructPath    = $path . $instructionsDataFileName    . $outExt;
