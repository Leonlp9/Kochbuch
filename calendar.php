<?php

include "shared/global.php";
global $pdo;

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kalender</title>

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
            <h1>Meine Woche</h1>

            <label>
                Vergangene Einträge anzeigen
                <input type="checkbox" id="showPast" <?php echo (isset($_GET['showPast']) && $_GET['showPast'] == 'true') ? 'checked' : '' ?>>
            </label>
            <script>
                document.getElementById('showPast').addEventListener('change', () => {
                    window.location.href = `calendar.php?showPast=${document.getElementById('showPast').checked}`;
                });
            </script>


            <div id='calendar'>
            </div>

            <script>

                let showPast = <?php echo isset($_GET['showPast']) && $_GET['showPast'] == 'true' ? 'true' : 'false' ?>;
                let data = JSON.parse($.ajax({
                    url: `http://localhost/Kochbuch/api?task=getKalender&showPast=${showPast}`,
                    async: false
                }).responseText);

                console.log(data);

                let html = '';
                for (let i = 0; i < data.length; i++) {
                    let recipe = data[i];

                    html += `<div class="day">
                        <div class="recipe">
                            <h3>${ recipe['Name'] === null ? recipe['Text'] : recipe['Name'] }</h3>
                        </div>`;

                    html += `</div>
                    </div>`;
                }

                document.getElementById('calendar').innerHTML = html;

            </script>

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
