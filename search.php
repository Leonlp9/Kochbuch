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
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="script.js"></script>

    <link rel="stylesheet" href="style.css">

    <style>

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
            color: var(--color);
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        @media (max-width: 768px) {
            #results {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        .rezept {
            border-radius: 10px;
            text-decoration: none;
            color: var(--color);
            animation: fadeInOpacity 0.3s cubic-bezier(0.42, 0, 0.58, 1) forwards;
            opacity: 0;
        }

        .rezept .image {
            width: 100%;
            aspect-ratio: 4/3;
            border-radius: 10px;
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .rezept h2 {
            margin: 0;
            font-size: 1.2em;
            word-break: break-word;
            text-align: center;
        }

        .rating {
            display: flex;
        }

        .rating i {
            color: var(--color);
        }

        .rezept:hover {
            background-color: var(--secondaryBackground);
            transform: translateY(-5px);
            transition: background-color 0.3s, transform 0.3s;
        }

        .overlay{
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-end;
            padding: 5px;
        }

        .overlay div{
            background-color: rgba(255, 255, 255, 0.8);
            color: #27292c;
            padding: 5px;
            border-radius: 10px;
            display: flex;
            gap: 5px;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        .overlay div i {
            color: #27292c;
        }
    </style>
</head>
<body>
    <div class="nav-grid-content">
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
                <div class="radio" style="margin-top: 15px">
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
                <select name="kategorie" id="kategorie" onchange="search()" style="margin-bottom: 15px; margin-top: 10px">
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

                <label for="KitchenAppliances">Küchengeräte</label>
                <select name="KitchenAppliances" id="KitchenAppliances" onchange="search()" style="margin-bottom: 15px; margin-top: 10px" multiple>
                    <option value="*" selected>Ohne Einschränkung</option>
                </select>
                <script>
                    fetch('api?task=getKitchenAppliances')
                        .then(response => response.json())
                        .then(kitchenAppliances => {
                            let options = '';
                            kitchenAppliances.forEach(kitchenAppliance => {
                                options += `<option value="${kitchenAppliance.ID}">${kitchenAppliance.Name} (${kitchenAppliance.recipe_count})</option>`;
                            });
                            document.getElementById('KitchenAppliances').innerHTML = options;
                        })
                        .catch(error => console.error('Error fetching kitchen appliances:', error));
                </script>
            </div>

            <label class="divider">Suchergebnisse</label>
            <span style="font-size: 13px;"><span style="font-size: 13px" id="resultsCount">0</span> Rezepte gefunden</span>

            <div id="results"></div>

            <script>
                function search(defaultKat = null) {
                    let search = $("#search").val();
                    let order = $("input[name=order]:checked").val();
                    let zeit = $("#zeit").val();
                    let kategorie = $("#kategorie").val();
                    let kitchenAppliances = $("#KitchenAppliances").val();
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
                            kategorie: kategorie,
                            kitchenAppliances: kitchenAppliances
                        },
                        success: function (data) {

                            $("#resultsCount").text(data.length);

                            let html = "";
                            for (let i = 0; i < data.length; i++) {
                                let result = data[i];
                                html += `
                                <a class="rezept" href="rezept?id=${result.rezepte_ID}" style="animation-delay: ${i * 0.01}s">
                                    <div class="image lazy-load" data-src="${result.Image}">
                                        <div class="overlay">
                                            <div>${result.Zeit}</div>`;
                                                if (result.Rating != null && result.Rating > 0) {

                                                    html += `<div class="rating">`;
                                                    for (let j = 0; j < 5; j++) {
                                                        if (j < result.Rating) {
                                                            html += `<i class="fas fa-star"></i>`;
                                                        } else {
                                                            html += `<i class="far fa-star"></i>`;
                                                        }
                                                    }
                                                    html += `</div>`;

                                                }
                                                html += `</div>
                                    </div>
                                    <h2>${result.Name}</h2>
                                </a>
                                `;
                            }
                            $("#results").html(html);

                            // Lazy load images
                            const lazyLoadImages = document.querySelectorAll('.lazy-load');
                            const observer = new IntersectionObserver((entries, observer) => {
                                entries.forEach(entry => {
                                    if (entry.isIntersecting) {
                                        const img = entry.target;
                                        img.style.backgroundImage = `url('${img.dataset.src}')`;
                                        observer.unobserve(img);
                                    }
                                });
                            });

                            lazyLoadImages.forEach(img => {
                                observer.observe(img);
                            });
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
</html>
