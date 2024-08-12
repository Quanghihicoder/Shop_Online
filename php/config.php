<?php
    // Set time zone
    date_default_timezone_set("Australia/Sydney");

    // Check operating system that running Apache server
    $isWindows = false;

    if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT') {
        $isWindows = true;
    }
?>