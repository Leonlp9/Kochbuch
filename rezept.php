<?php

include "shared/global.php";
global $pdo;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    die();
}

$id = $_GET['id'];

// http://localhost/Kochbuch/api?task=getRezept&id=$id
$rezept = json_decode(file_get_contents("http://localhost/Kochbuch/api?task=getRezept&id=$id&zutaten"), true)[0];

// Array
//(
//    [ID] => 154
//    [Name] => Rotweinsauce zu Hirsch
//    [Kategorie_ID] => 2
//    [Zubereitung] =>
//Zucker karamellisieren, nicht zu braun. Anschließend mit Wein ablöschen und ca. 70 Minuten reduzieren.
//In einem zweiten Topf Wildfond reduzieren.
//Inhalt zusammenschütten, mit Salz und Pfeffer abschmecken.
//Mit Butter binden, wenn nicht fest genug, dann mit Soßenbinder unter ständigem rühren andicken.
//
//    [Portionen] => 10
//    [Zeit] => 90
//    [Zutaten_JSON] => Array
//        (
//            [0] => Array
//                (
//                    [ID] => 137
//                    [Menge] => 750
//                    [unit] => ml
//                    [Name] => Rotwein
//                    [Image] => rotwein.svg
//                    [additionalInfo] => 1 Flasche
//                    [table] =>
//                )
//
//            [1] => Array
//                (
//                    [ID] => 253
//                    [Menge] => 1
//                    [unit] => EL
//                    [Name] => Butter
//                    [Image] => butter.svg
//                    [additionalInfo] => eiskalte Butter
//                    [table] =>
//                )
//
//            [2] => Array
//                (
//                    [ID] => 319
//                    [Menge] => 4
//                    [unit] => EL
//                    [Name] => Zucker
//                    [Image] => zucker.svg
//                    [additionalInfo] =>
//                    [table] =>
//                )
//
//            [3] => Array
//                (
//                    [ID] => 455
//                    [Menge] => 800
//                    [unit] => ml
//                    [Name] => Wildfond
//                    [Image] => wildfond.svg
//                    [additionalInfo] =>
//                    [table] =>
//                )
//
//            [4] => Array
//                (
//                    [ID] => 1
//                    [Menge] => 5
//                    [unit] => g
//                    [Name] => Salz
//                    [Image] => salz.svg
//                    [additionalInfo] => nach Geschmack
//                    [table] =>
//                )
//
//            [5] => Array
//                (
//                    [ID] => 2
//                    [Menge] => 5
//                    [unit] => g
//                    [Name] => Pfeffer
//                    [Image] => pfeffer.svg
//                    [additionalInfo] => nach Geschmack
//                    [table] =>
//                )
//
//            [6] => Array
//                (
//                    [ID] => 456
//                    [Menge] => 10
//                    [unit] => g
//                    [Name] => Soßenbinder
//                    [Image] => soßenbinder.svg
//                    [additionalInfo] => braun, nach Bedarf
//                    [table] =>
//                )
//
//        )
//
//    [OptionalInfos] => []
//    [ZutatenTables] => Array
//        (
//            [0] =>
//        )
//
//    [Kategorie] => Aufstriche, Dips und Saucen
//    [KategorieColor] => #FFB6C1
//    [Anmerkungen] => Array
//        (
//        )
//
//    [Bewertungen] => Array
//        (
//        )
//
//    [Kalender] => Array
//        (
//            [0] => Array
//                (
//                    [ID] => 48
//                    [Datum] => 2024-12-24
//                    [Rezept_ID] => 154
//                    [Text] =>
//                )
//
//        )
//
//    [Bilder] => Array
//        (
//            [0] => Array
//                (
//                    [ID] => 162
//                    [Rezept_ID] => 154
//                    [Image] => uploads/676c728ac90021.46982337.webp
//                )
//
//            [1] => Array
//                (
//                    [ID] => 163
//                    [Rezept_ID] => 154
//                    [Image] => uploads/676c72a349a6a8.72778033.webp
//                )
//
//        )
//
//)

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rezept - <?= $rezept['Name'] ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/Kochbuch/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Kochbuch/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Kochbuch/icons/favicon-16x16.png">
    <link rel="manifest" href="/Kochbuch/icons/site.webmanifest">
    <link rel="mask-icon" href="/Kochbuch/icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/Kochbuch/icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Kochbuch">
    <meta name="application-name" content="Kochbuch">
    <meta name="msapplication-TileColor" content="#f6f6f6">
    <meta name="msapplication-config" content="/Kochbuch/icons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://kit.fontawesome.com/8482ce4752.js" crossorigin="anonymous"></script>

    <!-- QuillJS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

</head>
<body>
<div class="nav-grid">
    <?php
    require_once 'shared/navbar.php';
    ?>
        <div class="container">

            <h1><?= $rezept['Name'] ?></h1>
            <div class="images">
                <?php
                foreach ($rezept['Bilder'] as $bild) {
                    $bild = $bild["Image"];
                    echo "<img src='$bild' alt=''>";
                }
                ?>
            </div>

            <h2>Zutaten</h2>
            <ul>
                <?php
                foreach ($rezept['Zutaten_JSON'] as $zutat) {
                    echo "<li>
                            <img src='{$zutat['Image']}' alt='' width='20px' height='20px'>
                            {$zutat['Menge']} {$zutat['unit']} {$zutat['Name']} {$zutat['additionalInfo']}
                        </li>";
                }
                ?>
            </ul>

            <h2>Zubereitung</h2>
            <p><?= $rezept['Zubereitung'] ?></p>

            <h2>Portionen</h2>
            <p><?= $rezept['Portionen'] ?></p>

            <h2>Zeit</h2>
            <p><?= $rezept['Zeit'] ?></p>

            <h2>Kategorie</h2>
            <p><?= $rezept['Kategorie'] ?></p>

            <h2>Anmerkungen</h2>
            <ul>
                <?php
                foreach ($rezept['Anmerkungen'] as $anmerkung) {

                    if (empty($anmerkung['Text'])) {
                        continue;
                    }

                    echo "<li>{$anmerkung['Text']}</li>";
                }
                ?>
            </ul>

            <h2>Kalender</h2>
            <ul>
                <?php
                foreach ($rezept['Kalender'] as $kalender) {
                    echo "<li>{$kalender['Datum']}</li>";
                }
                ?>
            </ul>

            <h2>Bewertungen</h2>
            <ul>
                <?php
                foreach ($rezept['Bewertungen'] as $bewertung) {
                    echo "<li>
                            <i class='fas fa-star'></i>
                            {$bewertung['Bewertung']}
                            {$bewertung['Text']}
                            - 
                            {$bewertung['Name']}
                        </li>";
                }
                ?>
            </ul>

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
