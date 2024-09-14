<?php

require_once 'shared/global.php';
global $pdo;

if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $order = (isset($_POST['order'])) ? $_POST['order'] : "Name";
    $zeit = (isset($_POST['zeit'])) ? $_POST['zeit'] : "4";
    $kategorie = (isset($_POST['kategorie'])) ? $_POST['kategorie'] : "*";
    $random = (isset($_POST['random'])) ? $_POST['random'] : false;
    $neueste = (isset($_POST['neueste'])) ? $_POST['neueste'] : false;

    $join = "LEFT JOIN bilder ON rezepte.ID = bilder.Rezept_ID";
    $where = "WHERE rezepte.Name LIKE '%$search%'";
    if ($kategorie != "*") {
        $where .= " AND rezepte.Kategorie_ID = $kategorie";
    }

    switch ($zeit) {
        case "0":
            $where .= " AND rezepte.Zeit <= 15";
            break;
        case "1":
            $where .= " AND rezepte.Zeit > 15 AND rezepte.Zeit <= 30";
            break;
        case "2":
            $where .= " AND rezepte.Zeit > 30 AND rezepte.Zeit <= 60";
            break;
        case "3":
            $where .= " AND rezepte.Zeit > 60";
            break;
    }

    $order = "ORDER BY rezepte.$order";

    $join .= " LEFT JOIN bewertungen ON rezepte.ID = bewertungen.Rezept_ID";

    if ($order == "ORDER BY rezepte.Rating") {
        $order = "ORDER BY AVG(bewertungen.Bewertung) DESC";
    }

    if ($random) {
        $order = "ORDER BY RAND() LIMIT 8";
    }

    if ($neueste) {
        $order = "ORDER BY rezepte.ID DESC LIMIT 8";
    }

    $rezepte = $pdo->query("
        SELECT
            rezepte.ID as rezepte_ID, 
            rezepte.Name as Name,
            MIN(bilder.Image) as Image,   -- Nimm das erste Bild (alphabetisch)
            rezepte.Zeit,
            AVG(bewertungen.Bewertung) as Durchschnittsbewertung -- Durchschnitt der Bewertungen
        FROM
            rezepte
        $join
        $where
        GROUP BY
            rezepte.ID
        $order
    ")->fetchAll();

    if (!$random && !$neueste) {
    ?>
        <div class="divider" style="margin: 30px 0 10px 0">Suchergebnisse</div>
    <?php
    }

    if (count($rezepte) > 0 && !$random && !$neueste) {
        ?>

        <span><?= count($rezepte) ?> Rezepte gefunden</span>

        <?php
    }
    ?>

    <div class="rezepte">
    <?php

    foreach ($rezepte as $rezept) {
        ?>
        <a href="rezept.php?id=<?= $rezept['rezepte_ID'] ?>" class="fadeInOpacity">
            <div class="rezept">
                <div class="image" style="background-image: url('<?php
                if (!file_exists("uploads/". $rezept['Image']) || $rezept['Image'] == null) {
                    echo "ingredientIcons/default.svg";
                } else {
                    echo "uploads/" . $rezept['Image'];
                }
                ?>')">
                    <div class="overlays">
                        <div class="overlay">
                            <i class="fas fa-clock"></i>
                            <span><?php
                                $zeit = $rezept['Zeit'];
                                $stunden = floor($zeit / 60);
                                $minuten = $zeit % 60;
                                if ($stunden > 0) {
                                    echo $stunden . "h ";
                                }
                                if ($minuten > 0) {
                                    echo $minuten . "min";
                                }
                                ?></span>
                        </div>
                        <?php
                        $rating = $pdo->query("SELECT AVG(Bewertung) as Rating, COUNT(Bewertung) as Anzahl
                            FROM bewertungen WHERE Rezept_ID = " . $rezept['rezepte_ID'])->fetch();
                        $count = $rating['Anzahl'];
                        $rating = $rating['Rating'];
                        if ($rating == null) {
                            $rating = 0;
                        }

                        if ($rating != 0) {
                            ?>
                            <div class="overlay">
                                <?php
                                    for ($i = 0; $i < 5; $i++) {
                                        if ($rating - $i >= 1) {
                                            ?>
                                            <i class="fas fa-star"></i>
                                            <?php
                                        } else if ($rating - $i > 0) {
                                            ?>
                                            <i class="fas fa-star-half-alt"></i>
                                            <?php
                                        } else {
                                            ?>
                                            <i class="far fa-star"></i>
                                            <?php
                                        }
                                    }

                                    echo " (" . $count . ")";
                                ?>
                            </div>
                            <?php
                        }


                        ?>
                    </div>
                </div>
                <h2><?= $rezept['Name'] ?></h2>
            </div>
        </a>
        <?php
    }

    ?>
    </div>
    <?php

    if (count($rezepte) == 0) {
        ?>
        <h3>Keine Rezepte gefunden</h3>
        <?php
    }

    die();
}

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

        .fadeInOpacity {
            animation: fadeInOpacity 0.3s cubic-bezier(0.42, 0, 0.58, 1);
        }

        @keyframes fadeInOpacity {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
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
                        url: "search.php",
                        type: "POST",
                        data: {
                            search: search,
                            order: order,
                            zeit: zeit,
                            kategorie: kategorie
                        },
                        success: function (data) {
                            $("#results").html(data);
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
