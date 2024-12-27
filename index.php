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

        #settings {
            background: none;
            border: none;
            color: var(--nonSelected);
            font-size: 30px;
            cursor: pointer;
            text-shadow: 2px 2px 0 var(--selected);
        }

        .filterprofile {
            margin-bottom: 20px;
            overflow: scroll;
            display: flex;
            gap: 10px;
            border-radius: 10px;
            scrollbar-width: none;
        }

        .filterprofile div {
            width: 195px;
            height: 205px;
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
            flex-direction: column;
        }

        .filterprofile img {
            border-radius: 10px;
            pointer-events: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

<!--            Einstellungen oben rechts-->
            <a style="position: absolute; top: 10px; right: 10px;" href="settings.php" class="mobile">
                <button id="settings">
                    <i class="fas fa-cog"></i>
                </button>
            </a>


            <img src="logo.svg" alt="Logo" style="width: 100px; padding: 10px; filter: drop-shadow(3px 3px 0 var(--nonSelected));">

            <div id="banner" style="height: calc(100vh - 200px); overflow: auto; display: flex; flex-direction: column; flex-wrap: nowrap; justify-content: flex-end; align-items: flex-start; text-align: center; background: rgba(231,214,232,0.5); border-radius: 10px; background-size: cover; background-position: center; background-repeat: no-repeat; transition: background-image 1s;">
            </div>
            <script>
                let entries = [];

                let selected = 0;

                function update() {
                    const banner = document.getElementById("banner");
                    banner.style.backgroundImage = `url('uploads/${entries[selected].image}')`;
                    banner.innerHTML = `<h3 class="fade-in" style="background: rgba(255,255,255,0.5); padding: 10px; border-radius: 10px; margin: 10px; font-size: 30px; font-weight: bold; text-shadow: 2px 2px 0 var(--selected);">
                        ${entries[selected].rezept}
                    </h3>
                    `;

                    banner.onclick = function () {
                        window.location.href = `rezept.php?id=${entries[selected].id}`;
                    };
                }

                //alle 5s beim banner ein random rezept anzeigen
                function randomBanner() {
                    if (entries.length < 5) {
                        $.ajax({
                            url: "search.php",
                            type: "POST",
                            data: {
                                banner: true
                            },
                            success: function (data) {
                                let rezept = JSON.parse(data);

                                entries.push(rezept);

                                selected = entries.length - 1;

                                update();
                            }
                        });
                    }else{
                        if (selected === entries.length - 1) {
                            selected = 0;
                        } else {
                            selected++;
                        }
                        update();
                    }

                }
                randomBanner();
                setInterval(randomBanner, 5000);
            </script>

            <h2 style="margin-top: 40px">Kategorien</h2>

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

            <br>

            <h2>Filterprofile</h2>
            <div class="filterprofile horizontalScrollBarJS">
                <?php
                $filterprofile = $pdo->query("SELECT * FROM filterprofile")->fetchAll();
                foreach ($filterprofile as $filter) {
                    ?>
                    <div>
                        <img src='https://api.dicebear.com/9.x/bottts-neutral/svg?seed=<?= $filter['Name'] ?>' alt='Avatar'>
                        <h3><?= $filter['Name'] ?></h3>
                    </div>
                    <?php
                }
                ?>
            </div>

            <br>

            <h2>Zufällige Rezepte</h2>
            <div id="randomRezepte"></div>
            <button class="btn green" onclick="randomRezepte()" style="margin-bottom: 60px">Shake it!</button>
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

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
