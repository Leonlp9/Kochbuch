<?php
require_once 'shared/global.php';
global $pdo;

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kochbuch</title>

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

    <style>
        .kategorien {
            margin-bottom: 20px;
            overflow: scroll;
            display: flex;
            gap: 10px;
            border-radius: 10px;
            scrollbar-width: none;
        }

        .kategorie {
            width: 195px;
            height: 195px;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            user-select: none;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .kategorie {
                width: 85%;
                aspect-ratio: 1 / 1;
                height: auto;
                font-size: calc(20px + 2vw);
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

            <img src="logo.svg" alt="Logo" style="width: 100px; padding: 10px; filter: drop-shadow(3px 3px 0 var(--nonSelected));">

            <div style="height: calc(100vh - 200px); overflow: auto; display: flex; flex-direction: column; flex-wrap: nowrap; justify-content: center; align-items: center; text-align: center; background: rgba(231,214,232,0.5); border-radius: 10px;">
                <h1>Kochbuch</h1>
                <h2 style="border-bottom: none">
                    Opus in progressu, ideae non sunt paratae
                </h2>
            </div>

            <h2>Kategorien</h2>

            <div class="kategorien horizontalScrollBarJS">
                <?php
                $kategorien = $pdo->query("SELECT * FROM kategorien order by Name")->fetchAll();
                foreach ($kategorien as $kategorie) {
                    ?>
                    <div class="kategorie" style="background-color: <?= $kategorie['ColorHex'] ?>" onclick="window.location.href='search.php?kategorie=<?= $kategorie['ID'] ?>'">
                        <?= $kategorie['Name'] ?>
                    </div>
                    <?php
                }
                ?>
            </div>

            <hr style="margin-bottom: 60px">

            <div style="height: 400px; width: 100%; background: rgba(231,214,232,0.5); display: flex; justify-content: center; align-items: center; text-align: center; border-radius: 10px">
                <h2 style="border-bottom: none">
                    Opus in progressu, ideae non sunt paratae
                </h2>
            </div>

            <h2>Zufällige Rezepte</h2>
            <div id="randomRezepte"></div>
            <button class="btn green" onclick="randomRezepte()">Shake it!</button>
            <script>
                function randomRezepte() {
                    $.ajax({
                        url: "search.php",
                        type: "POST",
                        data: {
                            search: "",
                            random: true
                        },
                        success: function (data) {
                            $("#randomRezepte").html(data);
                        }
                    });
                }
                randomRezepte();
            </script>

            <hr style="margin-bottom: 60px">

            <div style="height: 400px; width: 100%; background: rgba(231,214,232,0.5); display: flex; justify-content: center; align-items: center; text-align: center; border-radius: 10px">
                <h2 style="border-bottom: none">
                    Opus in progressu, ideae non sunt paratae
                </h2>
            </div>

            <h2>Neueste Rezepte</h2>
            <div id="neuesteRezepte"></div>
            <script>
                function neuesteRezepte() {
                    $.ajax({
                        url: "search.php",
                        type: "POST",
                        data: {
                            search: "",
                            neueste: true
                        },
                        success: function (data) {
                            $("#neuesteRezepte").html(data);
                        }
                    });
                }
                neuesteRezepte();
            </script>

            <hr style="margin-bottom: 60px">

            <div style="height: 400px; width: 100%; background: rgba(231,214,232,0.5); display: flex; justify-content: center; align-items: center; text-align: center; border-radius: 10px">
                <h2 style="border-bottom: none">
                    Opus in progressu, ideae non sunt paratae
                </h2>
            </div>

            <br>

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
