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


//Rezepte
//ID, Name, Kategorie_ID, Zubereitung, Portionen, Zeit, Zutaten_JSON
$rezepte = "CREATE TABLE IF NOT EXISTS rezepte (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Kategorie_ID INT NOT NULL,
    Zubereitung TEXT NOT NULL,
    Portionen INT NOT NULL,
    Zeit INT NOT NULL,
    Zutaten_JSON TEXT NOT NULL
)";

//Kategorien
//ID, Name
$kategorien = "CREATE TABLE IF NOT EXISTS kategorien (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    ColorHex VARCHAR(255) NOT NULL
)";

//Bewertungen
//ID, Rezept_ID, Bewertung
$bewertungen = "CREATE TABLE IF NOT EXISTS bewertungen (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Rezept_ID INT NOT NULL,
    Bewertung INT NOT NULL,
    Name VARCHAR(255) NOT NULL,
    Text TEXT NOT NULL
)";

//Default Zutaten
//ID, Name, Image, unit (Stück, Gramm, Liter, etc.)
$zutaten = "CREATE TABLE IF NOT EXISTS zutaten (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    unit VARCHAR(255) NOT NULL 
)";

//Bilder
//ID, Rezept_ID, Image
$bilder = "CREATE TABLE IF NOT EXISTS bilder (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Rezept_ID INT NOT NULL,
    Image VARCHAR(255) NOT NULL
)";

//Anmerkungen
//ID, Rezept_ID, Anmerkung
$anmerkungen = "CREATE TABLE IF NOT EXISTS anmerkungen (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Rezept_ID INT NOT NULL,
    Anmerkung TEXT NOT NULL
)";

$filterprofile = "CREATE TABLE IF NOT EXISTS filterprofile (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	Name VARCHAR(255) NOT NULL,
	Filter TEXT NOT NULL
)";

//Ausführen der SQL-Statements
$pdo->exec($rezepte);
$pdo->exec($kategorien);
$pdo->exec($bewertungen);
$pdo->exec($zutaten);
$pdo->exec($bilder);
$pdo->exec($anmerkungen);
$pdo->exec($filterprofile);