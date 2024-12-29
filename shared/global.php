<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    //session name setzen
    session_name('Kochbuch');
    $eineWoche = 7 * 24 * 60 * 60;
    session_set_cookie_params($eineWoche);
    session_start();
}

// Check if dbInfo.ini file exists and is readable
if (file_exists("dbInfo.ini") && is_readable("dbInfo.ini")) {
    // Get username and password from dbInfo.ini
    $dbInfo = parse_ini_file("dbInfo.ini");

    // Check if username and password keys exist in the parsed array
    if (isset($dbInfo['username']) && isset($dbInfo['password'])) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=kochbuch', $dbInfo['username'], $dbInfo['password']);
        } catch (PDOException $e) {
            header("Location: error.php?error=1001");
            die();
        }
    } else {
        echo "Error: 'username' or 'password' key is missing in dbInfo.ini";
        die();
    }
} else {
    echo "Error: dbInfo.ini file is missing or not readable";
    die();
}
