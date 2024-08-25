<?php

// https://www.phphelp.com/t/check-for-upload-file-size-too-large-is-not-working/34793/2
// convert byte size representations using K/k,M/m,G/g notation to actual number of bytes
// Example: '12KB' to '12288'
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-2]);
    $bytes = mb_substr($val, 0, strlen($val)-1);
    switch($last) {
        case "gb":
            $val *= 1024;
        case "mb":
            $val *= 1024;
        case "kb":
            $val *= 1024;
    }
    return $val;
}

// TODO MB or Mb?
// forms the bite size notation from a raw bytes number.
// Example: '12288' to '12KB'
function format_bytes($val) {
    $units = array("B", "KB", "MB", "GB");
    $index = 0;
    while ($val > 1024) {
            $val /= 1024;
            $index += 1;
    }
    return $val . $units[$index];
}
