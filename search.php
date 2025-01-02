<?php

require_once 'shared/global.php';
global $pdo;

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Suche</title>

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
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--color);
            font-weight: normal;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 2px solid var(--nonSelected);
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        .radio {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .radio > div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @keyframes fadeInOpacity {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search {
            display: grid;
            grid-template-columns: auto 50px;
            background-color: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .search input {
            flex: 1;
            padding: 10px;
            border-radius: 0;
            border: none;
            height: 100%;
            background-color: transparent;
        }

        .search input:focus {
            outline: none;
            border: none;
        }

        .search button {
            background-color: transparent;
            color: var(--color);
            border: none;
            outline: none;
        }


        #results {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }

        .rezept {
            display: grid;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            background-color: var(--secondaryBackground);
            text-decoration: none;
            color: var(--color);
            animation: fadeInOpacity 0.3s cubic-bezier(0.42, 0, 0.58, 1);
        }

        .rezept img {
            width: 100%;
            aspect-ratio: 16/12;
            object-fit: cover;
            border-radius: 10px;
        }

        .rezept h2 {
            margin: 0;
            font-size: 1.2em;
            word-break: break-word;
        }

        .rating {
            display: flex;
            gap: 5px;
        }

        .rating i {
            color: var(--color);
        }

        .rezept:hover {
            background-color: var(--nonSelected);
            transform: translateY(-5px);
            transition: background-color 0.3s, transform 0.3s;
        }
    </style>
</head>
<body>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">
            <h1>Suche</h1>
            <br>
            <div class="search">
                <input type="text" id="search" placeholder="Suche..." oninput="search()">
                <button onclick="$('#erweitert').toggle()"><i class="fas fa-filter"></i></button>
            </div>

            <div id="erweitert">
                <label class="divider" for="order">Sortierung</label>
                <div class="radio">
                    <div><input type="radio" id="order" name="order" value="Name" checked onchange="search()"> Sortieren von A-Z</div>
                    <div><input type="radio" id="order" name="order" value="Rating" onchange="search()"> Sortieren nach Bewertung</div>
                    <div><input type="radio" id="order" name="order" value="Zeit" onchange="search()"> Sortieren nach Zubereitungszeit</div>
                </div>

                <label class="divider" for="zeit">Zeit <span style="margin-left: 5px" id="feedback">Beliebig</span></label>
<!--                Schieberegler für Zeit welcher 5 stufen hat (0-15, 15-30, 30-60, 60+, beliebig)-->
                <input type="range" id="zeit" min="0" max="4" step="1" value="4" oninput="search(); updateFeedback()">
                <script>
                    function updateFeedback() {
                        let feedback = document.getElementById("feedback");
                        let zeit = document.getElementById("zeit").value;
                        switch (zeit) {
                            case "0":
                                feedback.innerText = "0-15 Minuten";
                                break;
                            case "1":
                                feedback.innerText = "15-30 Minuten";
                                break;
                            case "2":
                                feedback.innerText = "30-60 Minuten";
                                break;
                            case "3":
                                feedback.innerText = "60+ Minuten";
                                break;
                            case "4":
                                feedback.innerText = "Beliebig";
                                break;
                        }
                    }
                </script>

                <label class="divider" for="kategorie">Kategorie</label>
                <select name="kategorie" id="kategorie" onchange="search()" style="margin-bottom: 0;">
                    <option value="*">Alle Kategorien</option>
                    <?php
                    $kategorien = $pdo->query("    SELECT k.Name, COUNT(r.ID) as Anzahl, k.ID
                        FROM kategorien k
                        LEFT JOIN rezepte r ON k.ID = r.Kategorie_ID
                        GROUP BY k.ID, k.Name
                        ORDER BY k.Name")->fetchAll();
                    foreach ($kategorien as $kategorie) {
                        ?>
                        <option value="<?= $kategorie['ID'] ?>"><?= $kategorie['Name'] ?> (<?= $kategorie['Anzahl'] ?>)</option>
                        <?php
                    }
                    ?>
                </select>
            </div>

            <div id="results"></div>

            <script>
                function search(defaultKat = null) {
                    let search = $("#search").val();
                    let order = $("input[name=order]:checked").val();
                    let zeit = $("#zeit").val();
                    let kategorie = $("#kategorie").val();
                    if (defaultKat != null) {
                        kategorie = defaultKat;
                    }

                    $.ajax({
                        url: "api?task=search",
                        type: "GET",
                        data: {
                            search: search,
                            order: order,
                            zeit: zeit,
                            kategorie: kategorie
                        },
                        success: function (data) {

                            console.log(data);

                            let html = "";
                            for (let i = 0; i < data.length; i++) {
                                let result = data[i];
                                html += `
                                <a class="rezept" href="rezept?id=${result.rezepte_ID}">
                                    <img src="${result.Image}" alt="${result.Name}">
                                    <h2>${result.Name}</h2>
                                    <div class="rating">`;
                                for (let j = 0; j < 5; j++) {
                                    if (j < result.Rating) {
                                        html += `<i class="fas fa-star"></i>`;
                                    } else {
                                        html += `<i class="far fa-star"></i>`;
                                    }
                                }
                                html += `</div>
                                    <p>${result.Zeit} Minuten</p>
                                </a>
                                `;
                            }
                            $("#results").html(html);
                        }
                    });
                }

                $('#erweitert').toggle()

                <?php if (isset($_GET['kategorie'])) { ?>
                    search(<?= $_GET['kategorie'] ?>);
                <?php } else { ?>
                    search();
                <?php } ?>

            </script>
        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
