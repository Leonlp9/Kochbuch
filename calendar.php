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

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL ?>icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL ?>icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL ?>icons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo BASE_URL ?>icons/site.webmanifest">
    <link rel="mask-icon" href="<?php echo BASE_URL ?>icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="<?php echo BASE_URL ?>icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Kochbuch">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Kochbuch">
    <meta name="msapplication-TileColor" content="#f6f6f6">
    <meta name="msapplication-config" content="<?php echo BASE_URL ?>icons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://kit.fontawesome.com/8482ce4752.js" crossorigin="anonymous"></script>

    <!-- QuillJS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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

        #addEntry {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background-color: var(--nonSelected);
            color: var(--color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            #addEntry {
                bottom: 70px;
            }
        }

        #addEntry:hover {
            background-color: var(--selected);
        }

        #addEntry i {
            font-size: 24px;
        }

        /* Visuelle Styles für Drag & Drop */
        .day.drag-over { outline: 2px dashed var(--selected); border-radius: 10px; }
        .entry.dragging { opacity: 0.5; transform: scale(0.98); }
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

                function update() {
                    let showPast = <?php echo isset($_GET['showPast']) && $_GET['showPast'] == 'true' ? 'true' : 'false' ?>;
                    let data = JSON.parse($.ajax({
                        url: `api?task=getKalender&showPast=${showPast}`,
                        async: false
                    }).responseText);

                    //reverse data
                    data = data.reverse();

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
                            // speichere das Datum im dayDiv für den Drop-Handler
                            dayDiv.dataset.date = recipe['Datum'];

                            let h2 = document.createElement('h2');
                            h2.classList.add('divider');
                            h2.textContent = new Date(recipe['Datum']).toLocaleDateString('de-DE');
                            dayDiv.appendChild(h2);

                            // Drop-Handler: Einträge hierhin verschieben
                            dayDiv.addEventListener('dragover', (e) => {
                                e.preventDefault();
                            });

                            dayDiv.addEventListener('dragenter', (e) => {
                                e.preventDefault();
                                dayDiv.classList.add('drag-over');
                            });

                            dayDiv.addEventListener('dragleave', (e) => {
                                dayDiv.classList.remove('drag-over');
                            });

                            dayDiv.addEventListener('drop', (e) => {
                                e.preventDefault();
                                dayDiv.classList.remove('drag-over');
                                const id = e.dataTransfer.getData('text/plain');
                                const newDate = dayDiv.dataset.date;
                                if (!id || !newDate) return;

                                // rufe API auf um das Datum zu aktualisieren
                                fetch(`api.php?task=updateKalender&id=${id}&date=${newDate}`, { method: 'GET' })
                                    .then(res => res.json())
                                    .then(resp => {
                                        if (resp.success) {
                                            // Aktualisiere die Ansicht
                                            update();
                                        } else {
                                            new SystemMessage('Verschieben fehlgeschlagen').show();
                                        }
                                    }).catch(() => {
                                        new SystemMessage('Verschieben fehlgeschlagen').show();
                                    });
                            });
                        }

                        let entry = document.createElement('div');
                        entry.classList.add('entry');

                        // Drag & Drop: mache den Eintrag draggable und speichere die ID
                        entry.setAttribute('draggable', 'true');
                        if (recipe['Kalender_ID'] !== undefined) {
                            entry.dataset.kalenderId = recipe['Kalender_ID'];
                        }

                        entry.addEventListener('dragstart', (ev) => {
                            // Übergebe die Kalender-ID
                            const id = entry.dataset.kalenderId;
                            if (id) {
                                ev.dataTransfer.setData('text/plain', id);
                                // kleiner visueller Hinweis
                                ev.dataTransfer.effectAllowed = 'move';
                                entry.classList.add('dragging');
                            }
                        });

                        entry.addEventListener('dragend', () => {
                            entry.classList.remove('dragging');
                        });

                        entry.addEventListener('click', () => {
                            let form = new FormBuilder("Kalendereintrag bearbeiten", (formData) => {
                                fetch(`api.php?task=updateKalender&id=${recipe['Kalender_ID']}&text=${formData["Text"]}`, {
                                    method: 'GET'
                                }).then(() => {
                                    window.location.reload();
                                });
                            }, () => {
                            });

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
                        } else {
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
                }
                update();

            </script>

            <div id="addEntry">
                <i class="fas fa-plus"></i>
            </div>

            <script>
                document.getElementById('addEntry').addEventListener('click', () => {
                    let form = new FormBuilder("Kalendereintrag hinzufügen", (formData) => {

                        if (formData["Text"] === '') {
                            new SystemMessage('Bitte fülle alle Felder aus.').show();
                            return;
                        }

                        fetch(`api.php?task=addKalender&date=${formData["Datum"]}&info=${formData["Text"]}`, {
                            method: 'GET'
                        }).then(() => {
                            update();
                        });
                    }, () => {});

                    form.addDateInput('Datum', new Date().toISOString().split('T')[0]);
                    form.addInputField('Text', 'Text');

                    form.renderForm();
                });
            </script>

        </div>
    </div>
</body>
</html>
