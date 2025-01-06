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

// Check if config.ini file exists and is readable
if (file_exists("config.ini") && is_readable("config.ini")) {
    // Get username and password from config.ini
    $config = parse_ini_file("config.ini");

    // Check if username and password keys exist in the parsed array
    if (isset($config['username']) && isset($config['password'])) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=kochbuch', $config['username'], $config['password']);
        } catch (PDOException $e) {
            header("Location: error?code=1001");
            die();
        }
    } else {
        echo "Error: 'username' or 'password' key is missing in config.ini";
        die();
    }

    if (isset($config['base_url'])) {
        define('BASE_URL', $config['base_url']);
    } else {
        define('BASE_URL', 'http://localhost/Kochbuch/');
    }


} else {
    echo "Error: config.ini file is missing or not readable";
    die();
}
