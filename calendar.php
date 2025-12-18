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

                    // Wichtig: nicht mehr das Array umdrehen. Wir sortieren die Datumsschlüssel absteigend.

                    const calendar = document.getElementById('calendar');
                    calendar.innerHTML = '';

                    // Gruppiere Einträge nach Datum, damit jeder Tag sein eigenes Scope für Event-Handler hat
                    const groups = {};
                    data.forEach(item => {
                        const d = item['Datum'];
                        if (!groups[d]) groups[d] = [];
                        groups[d].push(item);
                    });

                    // Sortiere die Datums-Schlüssel absteigend (neuere bzw. zukünftige Daten zuerst)
                    const dates = Object.keys(groups).sort((a, b) => b.localeCompare(a));

                    dates.forEach(date => {
                        // Sortiere die Einträge innerhalb eines Tages: neuere Einträge (höhere Kalender_ID) oben
                        groups[date].sort((a, b) => (b.Kalender_ID || 0) - (a.Kalender_ID || 0));

                        const dayDiv = document.createElement('div');
                        dayDiv.classList.add('day');
                        dayDiv.dataset.date = date;

                        const h2 = document.createElement('h2');
                        h2.classList.add('divider');
                        h2.textContent = new Date(date).toLocaleDateString('de-DE');
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

                        // Füge alle Einträge dieses Datums hinzu
                        groups[date].forEach(recipe => {
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
                                    // sende sowohl Text als auch Datum (falls geändert)
                                    const textParam = encodeURIComponent(formData["Text"] ?? '');
                                    const dateParam = formData["Datum"] ? formData["Datum"] : recipe['Datum'];

                                    fetch(`api.php?task=updateKalender&id=${recipe['Kalender_ID']}&text=${textParam}&date=${dateParam}`, {
                                        method: 'GET'
                                    }).then(() => {
                                        // nur Teilansicht aktualisieren
                                        update();
                                    });
                                }, () => {
                                });

                                if (recipe['Rezept_ID'] !== null) {
                                    form.addButton("Rezept öffnen", () => {
                                        window.location.href = `rezept.php?id=${recipe['Rezept_ID']}`;
                                    });
                                }

                                // Textfeld
                                form.addInputField('Text', 'Text', recipe['Text']);

                                // Datumsfeld zum manuellen Verschieben
                                form.addDateInput('Datum', recipe['Datum']);

                                form.addButton("Löschen", () => {
                                    fetch(`api.php?task=deleteKalender&id=${recipe['Kalender_ID']}`, {
                                        method: 'GET'
                                    }).then(() => {
                                        update();

