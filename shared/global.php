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

    //erstelle tabellen, wenn sie noch nicht existieren
    $pdo->exec("
CREATE TABLE IF NOT EXISTS `anmerkungen` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Rezept_ID` int(11) NOT NULL,
  `Anmerkung` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `zutaten` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `anmerkungen` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Rezept_ID` int(11) NOT NULL,
  `Anmerkung` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bewertungen` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Rezept_ID` int(11) NOT NULL,
  `Bewertung` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Text` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `bilder` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Rezept_ID` int(11) NOT NULL,
  `Image` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `einkaufsliste` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Zutat_ID` int(11) NOT NULL,
  `Item` varchar(255) DEFAULT NULL,
  `Menge` int(11) NOT NULL,
  `Einheit` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `filterprofile` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Filter` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kalender` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Datum` date NOT NULL,
  `Rezept_ID` int(11) DEFAULT NULL,
  `Text` text DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kategorien` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `ColorHex` varchar(255) NOT NULL DEFAULT '#000000',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `rezepte` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Kategorie_ID` int(11) NOT NULL,
  `Zubereitung` text NOT NULL,
  `Portionen` int(11) NOT NULL,
  `Zeit` int(11) NOT NULL,
  `Zutaten_JSON` text NOT NULL,
  `OptionalInfos` text DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `zutaten` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

");

} else {
    echo "Error: config.ini file is missing or not readable";

    echo "<br><a href='setup.php'>Setup</a>";

    die();
}
