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
    <script src="script.js"></script>

    <link rel="stylesheet" href="style.css">

    <style>
        #calendar {
            display: grid;
            gap: 10px;
        }

        .day {
            display: grid;
            gap: 10px;
        }

        .entry {
            text-decoration: none;
            color: var(--color);
            background-color: var(--secondaryBackground);
            border-radius: 10px;
        }

        .entry:hover {
            background-color: var(--nonSelected);
        }

        .recipe {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 10px;
            padding: 10px;
        }

        .recipe img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>

</head>
<body>

    <div class="nav-grid-content">
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
                    url: `api?task=getKalender&showPast=${showPast}`,
                    async: false
                }).responseText);

                let lastDate = null;
                const calendar = document.getElementById('calendar');
                calendar.innerHTML = '';

                for (let i = 0; i < data.length; i++) {
                    let recipe = data[i];

                    if (recipe['Datum'] !== lastDate) {
                        if (lastDate !== null) {
                            calendar.appendChild(dayDiv);
                        }
                        lastDate = recipe['Datum'];
                        var dayDiv = document.createElement('div');
                        dayDiv.classList.add('day');

                        let h2 = document.createElement('h2');
                        h2.classList.add('divider');
                        h2.textContent = new Date(recipe['Datum']).toLocaleDateString('de-DE');
                        dayDiv.appendChild(h2);
                    }

                    let entry = document.createElement('div');
                    entry.classList.add('entry');

                    entry.addEventListener('click', () => {
                        let form = new FormBuilder("Kalendereintrag bearbeiten", (formData) => {
                            fetch(`api.php?task=updateKalender&id=${recipe['Kalender_ID']}&text=${formData["Text"]}`, {
                                method: 'GET'
                            }).then(() => {
                                window.location.reload();
                            });
                        }, () => {});

                        if (recipe['Rezept_ID'] !== null) {
                            form.addButton("Rezept öffnen", () => {
                                window.location.href = `rezept.php?id=${recipe['Rezept_ID']}`;
                            });
                        }

                        form.addInputField('Text', 'Text', recipe['Text']);

                        form.addButton("Löschen", () => {
                            fetch(`api.php?task=deleteKalender&id=${recipe['Kalender_ID']}`, {
                                method: 'GET'
                            }).then(() => {
                                window.location.reload();
                            });
                        });

                        form.renderForm();
                    });

                    let recipeDiv = document.createElement('div');
                    recipeDiv.classList.add('recipe');

                    if (recipe['Image'] !== null) {
                        let img = document.createElement('img');
                        img.src = recipe['Image'];
                        img.alt = recipe['Name'] === null ? recipe['Text'] : recipe['Name'];
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '10px';
                        recipeDiv.appendChild(img);
                    }else {
                        recipeDiv.style.gridTemplateColumns = '1fr';
                    }


                    let infos = document.createElement('div');

                    let h3 = document.createElement('h3');
                    h3.textContent = recipe['Name'] === null ? recipe['Text'] : recipe['Name'];
                    infos.appendChild(h3);

                    if (recipe['Text'] !== null && recipe['Image'] !== null) {
                        let p = document.createElement('p');
                        p.textContent = recipe['Text'];
                        infos.appendChild(p);
                    }

                    recipeDiv.appendChild(infos);



                    entry.appendChild(recipeDiv);
                    dayDiv.appendChild(entry);
                }

                if (lastDate !== null) {
                    calendar.appendChild(dayDiv);
                }

            </script>

        </div>
    </div>
</body>
</html>
