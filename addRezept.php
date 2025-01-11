<?php
include 'shared/global.php';
global $pdo;

$edit = isset($_GET['rezept']);
$rezeptID = $edit ? $_GET['rezept'] : null;

//api.php?task=getRezept&id=$rezeptID
$rezept = $edit ? json_decode(file_get_contents(BASE_URL. "api.php?task=getRezept&id=$rezeptID&zutaten", true), true)[0] : null;

if ($edit && !$rezept) {
    header("Location: index.php");
    die();
}

$name = $edit ? $rezept['Name'] : '';
$dauer = $edit ? $rezept['Zeit'] : 5;
$portionen = $edit ? $rezept['Portionen'] : 4;

// Zutaten laden
$Zutaten_JSON = $edit ? $rezept['Zutaten_JSON'] : null;
$ZutatenTables = $edit ? $rezept['ZutatenTables'] : null;

if ($edit){
    $rezept['KitchenAppliances'] = $rezept['KitchenAppliances'] == null || $rezept['KitchenAppliances'] == '' ? "[]" : $rezept['KitchenAppliances'];
    $rezept['OptionalInfos'] = $rezept['OptionalInfos'] == null || $rezept['OptionalInfos'] == '' ? "[]" : $rezept['OptionalInfos'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rezept <?php echo $edit ? 'bearbeiten' : 'hinzufügen' ?></title>

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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="script.js"></script>

    <style>

        #tabellen {
            display: grid;
            gap: 10px;
        }

        .table {
            padding: 10px;
            background: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: var(--border-radius);
        }

        .tableHeader {
            display: grid;
            grid-template-columns: 1fr 80px;
            height: 40px;
            gap: 10px;
        }

        #addTable {
            padding: 10px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1em;
            background: var(--nonSelected);
            color: var(--color);
        }

        .tableHeader input {
            width: 100%;
            padding: 5px;
            border: none;
            border-radius: var(--border-radius);
            outline: none;
            background: var(--background);
            color: var(--color);
        }

        .tableHeader button {
            background: var(--nonSelected);
            color: var(--color);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
        }

        .zutaten {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .zutat {
            display: grid;
            grid-template-columns: 18px 1fr;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            align-items: center;
            justify-items: center;
            background: var(--nonSelected);
        }

        .grabber {
            cursor: grab;
            user-select: none;
            height: 100%;
            background-color: var(--secondaryBackground);
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5px;
        }

        .zutat.new {
            background-color: var(--green);
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .zutatInfo {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .zutatInfo img {
            width: 25px;
            height: 25px;
        }

        .zutatInfo p {
            text-align: center;
            word-break: break-word;
        }

        .zutatSuche {
            width: 100%;
            padding: 5px;
            border: 1px solid #000;
            border-radius: 5px;
            margin-bottom: 10px;
            outline: none;
        }

        .searchResults {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
            height: 100%;
            overflow-y: auto;
        }

        .searchResults div {
            cursor: pointer;
        }

        #alreadyUploaded, .preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        #alreadyUploaded div, .preview img {
            width: 100px;
            height: 100px;
            background-size: cover;
            background-position: center;
            object-fit: cover;
            border-radius: 10px;
            margin: 10px;
            position: relative;
        }

        .container form {
            display: grid;
            gap: 10px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .buttons button {
            flex: 1;
        }


        input[type="file"] {
            display: none;
        }

        .container form > label {
            width: 100%;
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr 1fr;
        }

        .container form > label > input {
            background: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: var(--border-radius);
            padding: 8px;
            font-size: 16px;
            width: 100%;
            outline: none;
            color: var(--color);
        }

        .container form > label > select {
            background: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: var(--border-radius);
            padding: 8px;
            font-size: 16px;
            width: 100%;
            outline: none;
            color: var(--color);
        }


        @media (max-width: 768px) {
            .container form > label {
                grid-template-columns: 1fr;
            }

            .numbers {
                grid-template-columns: 1fr;
            }

            .zutaten {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        .upload {
            display: block !important;
            background: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: var(--border-radius);
            padding: 10px;
            cursor: pointer;
            color: var(--color);
        }

        .upload:hover {
            background: var(--nonSelected);
        }

        .upload > span {
            display: block;
            text-align: center;
        }

        #alreadyUploadedGroup {
            background: var(--secondaryBackground);
            border: 2px solid var(--nonSelected);
            border-radius: var(--border-radius);
            padding: 10px;
        }

        .ql-container {
            height: auto;
        }

        .ql-toolbar.ql-snow {
            border: none;
            background: var(--nonSelected);
            display: flex;
        }

        .ql-container.ql-snow {
            border: none;
        }

        .ql-snow .ql-stroke {
            stroke: var(--color);
        }

        #extraCustomInfos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        #extraCustomInfos > div {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
        }

        #extraCustomInfos > div:hover {
            background: var(--nonSelected);
        }

        .kitchenApplianceSearchResults {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
            height: 100%;
            overflow-y: auto;
        }

        .kitchenAppliance {
            display: grid;
            grid-template-columns: 50px 1fr;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            align-items: center;
            justify-items: center;
            background: var(--nonSelected);
        }

        .kitchenAppliance img {
            width: 50px;
            height: 50px;
        }

        .kitchenAppliance p {
            text-align: center;
            word-break: break-word;
        }

        .kitchenAppliance:hover {
            background: var(--selected);
        }

        #addKitchenAppliance {
            padding: 10px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1em;
            background: var(--green);
            color: var(--color);
        }

        #kitchenAppliances {
            display: grid;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="nav-grid-content">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

            <h1>Rezept <?php echo $edit ? 'bearbeiten' : 'hinzufügen' ?></h1>
            <br>
            <form action="api.php?task=addRezept<?php echo $edit ? '&edit=true&rezept=' . $rezeptID : '' ?>
" method="post" enctype="multipart/form-data">
                <label for="name">Name
                    <input type="text" name="name" id="name" required placeholder="Rezeptname" style="text-transform: none" value="<?php echo $edit ? $name : '' ?>">
                </label>

                <label for="kategorie">Kategorie
                    <select name="kategorie" id="kategorie" required>
                        <option value="" disabled selected>Kategorie</option>
                        <?php
                        $sql = "SELECT k.Name, COUNT(r.ID) as Anzahl, k.ID
                            FROM kategorien k
                            LEFT JOIN rezepte r ON k.ID = r.Kategorie_ID
                            GROUP BY k.ID, k.Name
                            ORDER BY k.Name";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()) {
                            echo "<option value='" . $row['ID'] . "' " . ($edit && $rezept['Kategorie_ID'] == $row['ID'] ? 'selected' : '') . ">&nbsp" . $row['Name'] . " (" . $row['Anzahl'] . ")</option>";
                        }
                        ?>
                    </select>
                </label>

        <!--        Dropdown mit allen zutaaten und einem custom input feld für zutaten die noch nicht in der datenbank sind. Wenn angeklickt, dann wird in eine liste da drunter eingefügt-->

                <div class="numbers">
                    <label for="dauer">Dauer (Minuten)</label>
                    <div class="number-input">
                        <button type="button" class="down"><i class="fas fa-minus"></i></button>
                        <input id="dauer" min="0" name="dauer" value="<?php echo $edit ? $dauer : 5 ?>" step="5" type="number">
                        <button type="button"  class="up"><i class="fas fa-plus"></i></button>
                    </div>

                    <label for="portionen">Portionen</label>
                    <div class="number-input">
                        <button type="button"  class="down"><i class="fas fa-minus"></i></button>
                        <input id="portionen" min="0" name="portionen" value="<?php echo $edit ? $portionen : 4 ?>" step="1" type="number">
                        <button type="button" class="up"><i class="fas fa-plus"></i></button>
                    </div>
                </div>

                <input type="text" name="kitchenAppliances" id="kitchenAppliancesInput" hidden>
                <h2>Ein Rezept für</h2>
                <div id="kitchenAppliances"></div>
                <script>
                    let kitchenAppliances = JSON.parse(<?php echo $edit ? json_encode($rezept['KitchenAppliances']) : json_encode('[]') ?>);

                    function updateKitchenAppliances() {

                        document.getElementById('kitchenAppliances').innerHTML = '';
                        kitchenAppliances.forEach((appliance, index) => {
                            let div = document.createElement('div');
                            div.classList.add('kitchenAppliance');
                            div.innerHTML = `
                                <img src='icons/${appliance.Image}' alt='${appliance.Name}'>
                                <p>${appliance.Name}</p>
                            `;
                            div.dataset.index = index;

                            document.getElementById('kitchenAppliances').appendChild(div);

                            div.addEventListener('click', function () {
                                let index = this.dataset.index;
                                const form = new FormBuilder(
                                    'Gerät bearbeiten',
                                    (formData) => {},
                                    () => {}
                                );

                                form.addButton('Löschen', () => {
                                    // aus kitchenAppliances das element mit dem index entfernen
                                    kitchenAppliances.splice(index, 1);
                                    console.log(kitchenAppliances);
                                    updateKitchenAppliances();
                                    form.closeForm();
                                });

                                form.renderForm(false);
                            });
                        });


                        //fetch getKitchenAppliances from api.php and add them to the list

                        let addKitchenApplianceButton = document.createElement('div');
                        addKitchenApplianceButton.id = 'addKitchenAppliance';
                        addKitchenApplianceButton.type = 'button';
                        addKitchenApplianceButton.innerText = 'Gerät hinzufügen';
                        addKitchenApplianceButton.style.background = 'var(--green)';
                        document.getElementById('kitchenAppliances').appendChild(addKitchenApplianceButton);
                        addKitchenApplianceButton.addEventListener('click', () => {
                            const form = new FormBuilder(
                                'Gerät hinzufügen',
                                (formData) => {},
                                () => {}
                            );

                            form.addHTML(`
                                <div class="kitchenApplianceSearchResults"></div>
                            `);

                            form.renderForm(false);

                            form.form.parentElement.style.maxWidth = 'min(calc(100vw - 40px), 900px)';

                            function search() {
                                fetch('api.php?task=getKitchenAppliances')
                                    .then(response => response.json())
                                    .then(data => {
                                        form.form.querySelector('.kitchenApplianceSearchResults').innerHTML = '';
                                        data.forEach(appliance => {

                                            if (kitchenAppliances.find(a => a.ID === appliance.ID)) {
                                                return;
                                            }

                                            let result = document.createElement('div');
                                            result.classList.add('kitchenAppliance');
                                            result.innerHTML = `
                                                <img src='icons/${appliance.Image}' alt='${appliance.Name}'>
                                                <p>${appliance.Name}</p>
                                            `;
                                            result.addEventListener('click', () => {
                                                kitchenAppliances.push(appliance);
                                                updateKitchenAppliances();
                                                form.closeForm();
                                            });

                                            form.form.querySelector('.kitchenApplianceSearchResults').appendChild(result);
                                        });
                                    });
                            }

                            search();
                        });

                    }

                    updateKitchenAppliances();


                </script>

                <input type="text" name="extraCustomInfos" id="extraCustomInfosInput" hidden>

                <h2>Zusätzliche Informationen</h2>
                <div id="extraCustomInfos"></div>
                <script>
                    let extraCustomInfos = JSON.parse(<?php echo $edit ? json_encode($rezept['OptionalInfos']) : json_encode('[]') ?>);

                    function updateExtraCustomInfos() {
                        document.getElementById('extraCustomInfos').innerHTML = '';
                        extraCustomInfos.forEach((info, index) => {
                            let div = document.createElement('div');
                            div.classList.add('extraCustomInfo');
                            div.innerHTML = info.title + ': ' + info.content;
                            div.dataset.index = index;

                            document.getElementById('extraCustomInfos').appendChild(div);

                            div.addEventListener('click', function () {
                                let index = this.dataset.index;
                                let info = extraCustomInfos[index];
                                const form = new FormBuilder(
                                    'Information bearbeiten',
                                    (formData) => {
                                        extraCustomInfos[index] = {
                                            title: formData.title,
                                            content: formData.content
                                        };
                                        updateExtraCustomInfos();
                                    },
                                    () => {}
                                );

                                form.addInputField('title', 'Titel', info.title);
                                form.addInputField('content', 'Inhalt', info.content);
                                form.addButton('Löschen', () => {
                                    // aus extraCustomInfos das element mit dem index entfernen
                                    extraCustomInfos.splice(index, 1);
                                    console.log(extraCustomInfos);
                                    updateExtraCustomInfos();
                                    form.closeForm();
                                });

                                form.renderForm();
                            });
                        });

                        let addExtraCustomInfoButton = document.createElement('div');
                        addExtraCustomInfoButton.id = 'addExtraCustomInfo';
                        addExtraCustomInfoButton.type = 'button';
                        addExtraCustomInfoButton.innerText = 'Information hinzufügen';
                        addExtraCustomInfoButton.style.background = 'var(--green)';
                        document.getElementById('extraCustomInfos').appendChild(addExtraCustomInfoButton);
                        addExtraCustomInfoButton.addEventListener('click', () => {
                            const form = new FormBuilder(
                                'Information hinzufügen',
                                (formData) => {
                                    extraCustomInfos.push({
                                        title: formData.title,
                                        content: formData.content
                                    });
                                    updateExtraCustomInfos();
                                },
                                () => {}
                            );

                            form.addInputField('title', 'Titel (z.B. Kalorien)');
                            form.addInputField('content', 'Inhalt');
                            form.renderForm();
                        });

                    }

                    updateExtraCustomInfos();
                </script>


                <h2>Zutaten</h2>
                <input type="hidden" name="zutaten" id="zutatenInput" required>
                <div id="tabellen"></div>
                <button id="addTable" type="button">Neue Tabelle hinzufügen</button>
                <script>
                    let zutatenJSON = <?php echo json_encode($Zutaten_JSON) ?>;
                    let tables = <?php echo json_encode($ZutatenTables) ?>;

                    if (!zutatenJSON) {
                        zutatenJSON = [];
                    }

                    if (!tables) {
                        tables = [""];
                    }

                    function edit(zutatId) {
                        const aktuelleZutat = zutatenJSON[zutatId];

                        if (!aktuelleZutat) return;

                        const form = new FormBuilder(
                            'Zutat bearbeiten',
                            (formData) => {

                                aktuelleZutat.Menge = formData.menge;
                                aktuelleZutat.additionalInfo = formData.info;

                                update();
                            },
                            () => {
                            }
                        );

                        let step = 1;
                        if (aktuelleZutat.unit === 'Stück') {
                            step = 1;
                        }else if (aktuelleZutat.unit === 'l') {
                            step = 0.1;
                        }else if (aktuelleZutat.unit === 'ml') {
                            step = 10;
                        }else if (aktuelleZutat.unit === 'Prise') {
                            step = 0.1;
                        }else if (aktuelleZutat.unit === 'TL') {
                            step = 0.5;
                        }else if (aktuelleZutat.unit === 'EL') {
                            step = 1;
                        }else if (aktuelleZutat.unit === 'Tasse') {
                            step = 0.25;
                        }else if (aktuelleZutat.unit === 'g') {
                            step = 50;
                        }

                        // Felder für die Zutat
                        form.addHeader(aktuelleZutat.Name + ' (' + aktuelleZutat.unit + ')');
                        form.addCustomNumberField('menge', 0, Infinity, step, aktuelleZutat.Menge);
                        form.addInputField('info', 'Zusätzliche Info', aktuelleZutat.additionalInfo);

                        form.addButton("Speichern", () => {
                            form.submitForm();
                        });

                        form.addButton('Löschen', () => {
                            zutatenJSON.splice(zutatId, 1);
                            update();
                            form.closeForm();
                        });

                        form.renderForm(false);

                        form.select("menge");
                    }

                    function update() {

                        // Vorhandene Tabellen entfernen
                        document.querySelectorAll('.table').forEach(table => table.remove());

                        // Tabellen rendern
                        tables.forEach((table, index) => {
                            let tableDiv = document.createElement('div');
                            tableDiv.classList.add('table');
                            tableDiv.dataset.table = table;

                            tableDiv.innerHTML = `
                                <div class='tableHeader'>
                                    <input type='text' class='tableName' value='${table}' placeholder='Tabellenname'>
                                    <button type='button' class='deleteTable'>Löschen</button>
                                </div>

                                <div class='zutaten'>
                                    ${zutatenJSON
                                        .filter((zutat) => zutat.table === table)
                                        .map((zutat) => `
                                            <div class='zutat' data-id='${zutatenJSON.indexOf(zutat)}'>
                                                <div class="grabber">☰</div>
                                                <div class='zutatInfo'>
                                                    <img src='${zutat.Image}' alt='${zutat.Name}'>
                                                    <p>${zutat.Name} ${zutat.additionalInfo}</p>
                                                    <p>${zutat.Menge} ${zutat.unit}</p>
                                                </div>
                                            </div>
                                        `)
                                        .join('')}
                                    <div class='zutat new' style='order: 9999;'>+</div>
                                </div>
                            `;
                            document.getElementById('tabellen').appendChild(tableDiv);
                        });

                        // Event-Listener für Zutaten hinzufügen
                        document.querySelectorAll('.zutat').forEach(zutat => {
                            if (!zutat.classList.contains('new')) {
                                zutat.addEventListener('click', () => {
                                    const zutatId = zutat.dataset.id;
                                    edit(zutatId);
                                });
                            }
                        });

                        // Sortierfunktion hinzufügen
                        document.querySelectorAll('.table').forEach(table => {
                            new Sortable(table.querySelector('.zutaten'), {
                                animation: 250,
                                group: 'shared',
                                filter: '.new',
                                handle: '.grabber',
                                onEnd: e => {
                                    const zutat = zutatenJSON[e.item.dataset.id];
                                    const newTable = e.to.closest('.table').dataset.table;

                                    zutat.table = newTable;

                                    let sortedIndexes = Array.from(e.to.children)
                                        .filter(child => child.classList.contains('zutat') && !child.classList.contains('new'))
                                        .map(child => child.dataset.id);

                                    //get all zutaten of the new table
                                    let zutatenOfTable = zutatenJSON.filter(zutat => zutat.table === newTable);

                                    for (let i = 0; i < zutatenOfTable.length; i++) {
                                        zutatenOfTable[i] = zutatenJSON[sortedIndexes[i]];
                                    }

                                    //update zutatenJSON
                                    zutatenJSON = zutatenJSON.filter(zutat => zutat.table !== newTable);
                                    zutatenJSON = zutatenJSON.concat(zutatenOfTable);

                                    update();

                                    new SystemMessage('Zutaten aktualisiert').show();
                                }
                            });
                        });

                        // Tabellen-Löschen-Buttons aktivieren
                        document.querySelectorAll('.deleteTable').forEach(button => {
                            button.addEventListener('click', function () {
                                let table = this.closest('.table');
                                let tableName = table.dataset.table;

                                // Tabelle und zugehörige Zutaten entfernen
                                tables = tables.filter(t => t !== tableName);
                                zutatenJSON = zutatenJSON.filter(zutat => zutat.table !== tableName);

                                update();
                            });
                        });

                        // Tabellen-Umbenennen-Event hinzufügen
                        document.querySelectorAll('.tableName').forEach((input, index) => {
                            input.addEventListener('input', function () {
                                let oldTableName = tables[index];
                                let newTableName = this.value;

                                // Tabellenname aktualisieren
                                tables[index] = newTableName;

                                // Zutaten entsprechend aktualisieren
                                zutatenJSON = zutatenJSON.map(zutat => {
                                    if (zutat.table === oldTableName) {
                                        return { ...zutat, table: newTableName };
                                    }
                                    return zutat;
                                });
                            });
                        });

                        document.querySelectorAll('.zutat.new').forEach(newButton => {
                            newButton.addEventListener('click', () => {
                                const form = new FormBuilder(
                                    'Zutat hinzufügen',
                                    (formData) => {},
                                    () => {}
                                );

                                form.addHTML(`
                                    <input type='text' name='name' placeholder='Zutat suchen' required class="zutatSuche" autocomplete="off">
                                    <div class="divider">Zutaten Ergebnisse (Ersten 20)</div>
                                    <div class="searchResults"></div>
                                `);

                                form.renderForm(false);

                                form.form.parentElement.style.maxWidth = 'min(calc(100vw - 40px), 900px)';

                                function search(name) {
                                    fetch('api.php?task=getZutaten&name=' + name)
                                        .then(response => response.json())
                                        .then(data => {
                                            form.form.querySelector('.searchResults').innerHTML = '';
                                            data.forEach(zutat => {
                                                let result = document.createElement('div');
                                                result.classList.add('zutat');
                                                result.classList.add('zutatInfo');
                                                result.innerHTML = `
                                                    <img src='${zutat.Image}' alt='${zutat.Name}'>
                                                    <p>${zutat.Name}</p>
                                                    <p>${zutat.unit}</p>
                                                `;
                                                result.addEventListener('click', () => {
                                                    // Zutat in Liste einfügen
                                                    const newZutat = {
                                                        ID: zutat.ID,
                                                        Menge: 0,
                                                        unit: zutat.unit,
                                                        Name: zutat.Name,
                                                        Image: zutat.Image,
                                                        additionalInfo: '',
                                                        table: newButton.closest('.table').dataset.table
                                                    };

                                                    zutatenJSON.push(newZutat);
                                                    update();

                                                    form.closeForm();

                                                    edit(zutatenJSON.indexOf(newZutat));

                                                });
                                                form.form.querySelector('.searchResults').appendChild(result);
                                            });

                                            //New Zutat hinzufügen button
                                            let newZutat = document.createElement('div');
                                            newZutat.classList.add('zutat');
                                            newZutat.classList.add('new');
                                            newZutat.innerHTML = `
                                                <img src='ingredientIcons/default.svg' alt='Neue Zutat'>
                                                <p>Neue Zutat</p>
                                            `;
                                            newZutat.addEventListener('click', () => {
                                                form.closeForm();

                                                const newZutatForm = new FormBuilder(
                                                    'Neue Zutat hinzufügen',
                                                    (formData) => {

                                                        if (formData.name === '') {
                                                            new SystemMessage('Bitte gib einen Namen ein').show();
                                                            return;
                                                        }

                                                        fetch(`api.php?task=addZutat&name=${formData.name}&unit=${formData.unit}`)
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                const newZutat = {
                                                                    ID: data.ID,
                                                                    Menge: 0,
                                                                    unit: formData.unit,
                                                                    Name: formData.name,
                                                                    Image: 'ingredientIcons/default.svg', // Beispielbild
                                                                    additionalInfo: '',
                                                                    table: newButton.closest('.table').dataset.table
                                                                };
                                                                zutatenJSON.push(newZutat);
                                                                update();

                                                                edit(zutatenJSON.indexOf(newZutat));
                                                            });
                                                    },
                                                    () => {}
                                                );

                                                newZutatForm.addInputField('name', 'Name', name);
                                                newZutatForm.addSelectField('unit', [{value: 'g', text: 'g'}, {value: 'ml', text: 'ml'}, {value: 'Stück', text: 'Stück'}, {value: 'Prise', text: 'Prise'}, {value: 'TL', text: 'TL'}, {value: 'EL', text: 'EL'}, {value: 'Tasse', text: 'Tasse'}, {value: 'Packung', text: 'Packung'}, {value: 'Bund', text: 'Bund'}, {value: 'Dose', text: 'Dose'}, {value: 'Paket', text: 'Paket'}, {value: 'Becher', text: 'Becher'}, {value: 'Scheibe', text: 'Scheibe'}, {value: 'Zehe', text: 'Zehe'}, {value: 'Zweige', text: 'Zweige'}, {value: 'Prise', text: 'Prise'}, {value: 'Würfel', text: 'Würfel'}, {value: 'Messerspitze', text: 'Messerspitze'}], 'g');

                                                newZutatForm.renderForm();
                                            });
                                            form.form.querySelector('.searchResults').appendChild(newZutat);
                                        });
                                }
                                search("");

                                form.form.querySelector('input[name=name]').addEventListener('input', function () {
                                    search(this.value);
                                });

                                form.form.querySelector('input[name=name]').focus();
                                form.form.querySelector('input[name=name]').select();

                            });
                        });

                    }

                    document.getElementById('addTable').addEventListener('click', () => {
                        let newTableName = `Tabelle ${tables.length + 1}`;
                        tables.push(newTableName);
                        update();
                    });

                    update();

                    function getRawZutatenJson() {
                        return zutatenJSON.map(zutat => {
                            return {
                                ID: zutat.ID,
                                Menge: zutat.Menge,
                                additionalInfo: zutat.additionalInfo,
                                table: zutat.table
                            };
                        });
                    }
                </script>
                <h2>Zubereitung</h2>
                <input type="hidden" name="anleitung" id="anleitung" required>

                <div style="background: var(--secondaryBackground); border: 2px solid var(--nonSelected); border-radius: var(--border-radius)">
                    <div id="editor"></div>
                </div>

                <!-- QuillJS -->
                <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                <script>
                    var settings = {
                        modules: {
                            toolbar: [
                                ['bold', 'italic', 'underline', 'blockquote'],
                                [{'list': 'ordered'}, {'list': 'bullet'}],
                                ['link'],
                                ['clean']
                            ]
                        },
                        theme: 'snow'
                    };

                    var quill = new Quill('#editor', {
                        modules: {
                            toolbar: settings.modules.toolbar
                        },
                        theme: 'snow'
                    });

                    quill.root.innerHTML = '<?php echo $edit ? $rezept['Zubereitung'] : '' ?>';
                </script>

                <label class="upload">
                    <span>
                        <i class="fas fa-upload"></i> Bilder hochladen
                    </span>
                    <input type="file" name="bilder[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
                    <div class="preview"></div>
                    <script>
                        document.querySelector('input[type=file]').onchange = function() {
                            var preview = document.querySelector('.preview');
                            preview.style.display = 'grid';
                            preview.innerHTML = '';
                            for (var file of this.files) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    var img = document.createElement('img');
                                    img.src = e.target.result;
                                    preview.appendChild(img);
                                };
                                reader.readAsDataURL(file);
                            }
                        };
                    </script>
                </label>

                <div id="alreadyUploadedGroup">
                    <div class="divider">Bereits hochgeladene Bilder</div>
                    <div id="alreadyUploaded"></div>
                </div>
                <script>
                    function updateImages() {
                        fetch('api.php?task=getImages&rezept_id=<?php echo $rezeptID ?>')
                            .then(response => response.json())
                            .then(data => {
                                var alreadyUploaded = document.getElementById('alreadyUploaded');
                                alreadyUploaded.innerHTML = '';
                                for (var image of data) {
                                    var img = document.createElement('div');
                                    img.style.backgroundImage = `url('${image.Image}')`;
                                    img.style.position = 'relative';
                                    alreadyUploaded.appendChild(img);

                                    var deleteButton = document.createElement('button');
                                    deleteButton.innerHTML = 'Löschen';
                                    deleteButton.type = 'button';
                                    deleteButton.style.position = 'absolute';
                                    deleteButton.style.bottom = '5px';
                                    deleteButton.style.right = '5px';
                                    deleteButton.style.backgroundColor = 'var(--red)';
                                    deleteButton.style.color = 'white';
                                    deleteButton.style.border = 'none';
                                    deleteButton.style.borderRadius = '5px';
                                    deleteButton.style.padding = '5px';
                                    deleteButton.style.cursor = 'pointer';
                                    deleteButton.onclick = function() {
                                        fetch(`api.php?task=deleteImage&rezept_id=<?php echo $rezeptID ?>&image=${image.ID}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                updateImages();
                                            });
                                    };
                                    img.appendChild(deleteButton);

                                }
                            });
                    }
                    updateImages();
                </script>

                <div class="buttons">
                    <button type="submit" class="btn green"><?php echo $edit ? 'Speichern' : 'Hinzufügen' ?></button>

                    <?php
                    if ($edit) {
                        echo "<a href='api.php?task=deleteRezept&id=$rezeptID' class='btn delete'>Löschen</a>";
                    }
                    ?>
                </div>


                <script>

                    // confirm that the user wants to leave the page
                    window.onbeforeunload = function(e) {
                        e.returnValue = 'Möchtest du die Seite wirklich verlassen?';
                        return 'Möchtest du die Seite wirklich verlassen?';
                    };

                    document.querySelector('form').onsubmit = function() {
                        var anleitung = document.querySelector('input[name=anleitung]');
                        anleitung.value = quill.root.innerHTML;

                        var zutaten = document.querySelector('#zutatenInput');

                        zutaten.value = JSON.stringify(getRawZutatenJson());

                        let extraCustomInfosElement = document.querySelector('#extraCustomInfosInput');
                        extraCustomInfosElement.value = JSON.stringify(extraCustomInfos);

                        let kitchenAppliancesElement = document.querySelector('#kitchenAppliancesInput');
                        //so speichern, dass es nur die IDs sind in einem Array [1, 2, 3]
                        kitchenAppliancesElement.value = JSON.stringify(kitchenAppliances.map(appliance => appliance.ID));

                        // remove confirmation when leaving the page
                        window.onbeforeunload = function(e) {
                            return null;
                        };
                    }
                </script>
            </form>
        </div>
    </div>
</body>
</html>

