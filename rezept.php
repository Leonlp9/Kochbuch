<?php

include "shared/global.php";
global $pdo;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    die();
}

$id = $_GET['id'];

$rezept = $pdo->query("SELECT rezepte.*, kategorien.Name as KategorieName FROM rezepte LEFT JOIN kategorien ON rezepte.Kategorie_ID = kategorien.ID WHERE rezepte.ID = $id")->fetch();
if (!$rezept) {
    header("Location: index.php");
    die();
}

$zutaten = json_decode($rezept['Zutaten_JSON'], true);

if ($rezept['OptionalInfos'] == null) {
    $optionalInfos = [];
}else{
    $optionalInfos = json_decode($rezept['OptionalInfos'], true);
}

foreach ($zutaten as $key => $zutat) {
    $zutaten[$key] = $pdo->query("SELECT * FROM zutaten WHERE ID = " . $zutat['ID'])->fetch();
    $zutaten[$key]['Menge'] = $zutat['Menge'];

    if (isset($zutat['additionalInfo'])) {
        $zutaten[$key]['additionalInfo'] = $zutat['additionalInfo'];
    }

    if (isset($zutat['table'])) {
        $zutaten[$key]['table'] = $zutat['table'];
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rezept - <?= $rezept['Name'] ?></title>

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
        .tabelle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tabelle td {
            padding: 10px;
        }

        .tabelle tr:hover {
            background-color: rgba(232, 136, 255, 0.125);
        }

        .tabelle tr:nth-child(even) {
            background-color: rgba(232, 136, 255, 0.075);
        }

        .tabelle tr:nth-child(even):hover {
            background-color: rgba(232, 136, 255, 0.15);
        }

        .bilder{
            display: flex;
            flex-wrap: wrap;
        }

        .bild{
            border-radius: 10px;
            max-height: 500px;
            max-width: 100%;
        }

        .print {
            display: none;
        }

        .infos{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            justify-items: center
        }

        @media print {
            .bild {
                max-height: 300px;
            }

            .tabelle td {
                padding: 3px;
            }

            .print {
                display: block;
            }

            .printGrid{
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                justify-items: center;
            }

            .infos{
                justify-items: start;
            }
        }

        .hidden {
            display: none;
        }

        input[type="date"] {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--nonSelected);
            outline: none;
        }

        input[type="text"].planenExtraInput {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--nonSelected);
            margin-bottom: 10px;
            outline: none;
            background: var(--background);
            color: var(--color);
            text-transform: none;
        }


    </style>
</head>
<body>
<div class="nav-grid">
    <?php
    require_once 'shared/navbar.php';
    ?>
        <div class="container">
            <h1>Rezept</h1>

            <h2><?= $rezept['Name'] ?></h2>

            <div class="printGrid">
                <div>
                    <h2 class="print">Bild/er</h2>
                    <?php
                    $bilder = $pdo->query("SELECT * FROM bilder WHERE Rezept_ID = $id")->fetchAll();
                    if (count($bilder) > 0) {
                        ?>
                        <div class="bilder">
                            <?php
                            foreach ($bilder as $bild) {
                                ?>
                                <img class="bild" src="uploads/<?= $bild['Image'] ?>" alt="<?= $rezept['Name'] ?>">
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    } else {
                        ?>
                        <img class="bild" src="ingredientIcons/default.svg" alt="<?= $rezept['Name'] ?>">
                        <?php
                    } ?>

                </div>

                <?php

                //geplant an den Tagen

                $geplant = $pdo->query("SELECT * FROM kalender WHERE Rezept_ID = $id AND Datum >= CURDATE() ORDER BY Datum")->fetchAll();
                if (count($geplant) > 0) {
                    ?>
                    <div class="no-print">
                    <h2>Geplant an folgenden Tagen</h2>
                    <?php
                    foreach ($geplant as $tag) {
                        $date = date("d.m.Y", strtotime($tag['Datum']));
                        echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px'>
                            <h3 style='text-transform: none; font-weight: normal'>$date</h3>
                            <button style='padding: 10px 20px; background-color: #fac2d7; color: var(--color); border: none; border-radius: 10px; cursor: pointer; -webkit-tap-highlight-color: rgba(255, 255, 255, 0);' 
                            onclick='
                            if (confirm(\"Möchtest du das Rezept wirklich entfernen?\")) {
                                window.location.href = `calendar.php?id=$tag[ID]&action=delete`;
                            }'>
                                <i class='fas fa-trash'></i> Entfernen
                            </button>
                        </div>";
                    }
                    echo "</div>";
                }

                ?>

                <div>
                    <h2>Informationen</h2>

                    <div class="infos">
                        <div>
                            <h3>Zeit</h3>
                            <p><?php
                                $time = $rezept['Zeit'];
                                $hours = floor($time / 60);
                                $minutes = $time % 60;

                                if ($hours > 0) {
                                    if ($hours == 1) {
                                        echo "$hours Stunde";
                                    } else {
                                        echo "$hours Stunden";
                                    }
                                }else{
                                    echo "";
                                }

                                if ($hours > 0 && $minutes > 0) {
                                    echo " und ";
                                }

                                if ($minutes > 0) {
                                    if ($minutes == 1) {
                                        echo "$minutes Minute";
                                    } else {
                                        echo "$minutes Minuten";
                                    }
                                }else{
                                    echo "";
                                }
                                ?></p>
                        </div>

                        <div>
                            <h3>Kategorie</h3>
                            <p><?= $rezept['KategorieName'] ?></p>
                        </div>

                        <?php
                        if (count($optionalInfos) > 0) {
                            foreach ($optionalInfos as $info) {
                                echo "<div>
                                    <h3>$info[title]</h3>
                                    <p>$info[content]</p>
                                </div>";
                            }
                        }
                        ?>

                         <div class="print">
                            <h3>Portionen</h3>
                            <p id="printPortionen"><?= $rezept['Portionen'] ?></p>
                         </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; justify-items: center; margin-top: 40px" class="no-print">
                    <div>
                        <button id="print" style="padding: 10px 20px; background-color: #bbef9c; color: var(--color); border: none; border-radius: 10px; cursor: pointer; -webkit-tap-highlight-color: rgba(255, 255, 255, 0);">
                            <i class="fas fa-print"></i> Drucken
                        </button>
                        <script>
                            document.getElementById('print').addEventListener('click', () => {
                                window.print();
                            });
                        </script>
                    </div>

                    <div>
    <!--                    planen-->
                        <button id="planen" style="padding: 10px 20px; background-color: #bbef9c; color: var(--color); border: none; border-radius: 10px; cursor: pointer; -webkit-tap-highlight-color: rgba(255, 255, 255, 0);">
                            <i class="fas fa-calendar-alt"></i> Planen
                        </button>
                        <script>
                            document.getElementById('planen').addEventListener('click', () => {
                                //datum auswählen
                                const div = document.createElement('div');
                                div.style.position = 'fixed';
                                div.style.top = '0';
                                div.style.left = '0';
                                div.style.width = '100%';
                                div.style.height = '100%';
                                div.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                                div.style.display = 'flex';
                                div.style.justifyContent = 'center';
                                div.style.alignItems = 'center';
                                div.style.zIndex = '1000';

                                const innerDiv = document.createElement('div');
                                innerDiv.style.backgroundColor = 'var(--background)';
                                innerDiv.style.padding = '20px';
                                innerDiv.style.borderRadius = '10px';
                                innerDiv.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
                                innerDiv.style.width = '300px';
                                innerDiv.style.maxWidth = 'calc(100% - 40px)';
                                innerDiv.style.maxHeight = 'calc(100% - 40px)';
                                innerDiv.style.overflow = 'auto';

                                const h2 = document.createElement('h2');
                                h2.innerText = 'Datum auswählen';
                                innerDiv.appendChild(h2);

                                const input = document.createElement('input');
                                input.type = 'date';

                                const date = new Date();
                                const year = date.getFullYear();
                                const month = date.getMonth() + 1;
                                const day = date.getDate();
                                input.value = `${year}-${month < 10 ? '0' + month : month}-${day < 10 ? '0' + day : day}`;

                                input.min = input.value;

                                input.style.width = '100%';
                                innerDiv.appendChild(input);

                                const extraInput = document.createElement('input');
                                extraInput.type = 'text';
                                extraInput.placeholder = 'Zusätzliche Informationen (optional)';
                                extraInput.style.width = '100%';
                                extraInput.style.marginTop = '10px';
                                extraInput.classList.add('planenExtraInput');
                                innerDiv.appendChild(extraInput);

                                const button = document.createElement('button');
                                button.innerText = 'Planen';
                                button.style.padding = '10px 20px';
                                button.style.backgroundColor = '#bbef9c';
                                button.style.color = 'var(--color)';
                                button.style.border = 'none';
                                button.style.borderRadius = '10px';
                                button.style.cursor = 'pointer';
                                button.style.marginTop = '20px';
                                innerDiv.appendChild(button);

                                div.appendChild(innerDiv);
                                document.body.appendChild(div);

                                button.addEventListener('click', () => {
                                    const date = input.value;
                                    const additionalInfo = extraInput.value;
                                    const rezeptID = <?= $id ?>;
                                    window.location.href = `calendar.php?date=${date}&rezept=${rezeptID}&action=add${additionalInfo ? `&info=${additionalInfo}` : ''}`;
                                });

                                div.addEventListener('click', () => {
                                    div.remove();
                                });

                                innerDiv.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                });
                            });
                        </script>
                    </div>

                    <div style="grid-column: span 2">
                        <h3>Portionen</h3>
                        <div class="number-input" style="width: 160px;">
                            <button class="down">-</button>
                            <input class="quantity" min="1" max="99" id="portionen" value="<?= $rezept['Portionen'] ?>" type="number">
                            <button class="up">+</button>
                        </div>
                        <span style="font-size: 14px">(Erstellt für <?= $rezept['Portionen'] ?> Portionen)</span>
                    </div>
                </div>

                <div style="grid-column: span 2; width: 100%">
                    <h2>Zutaten</h2>
                    <table id="zutaten" class="tabelle"></table>

                    <div id="customTabellen"></div>

                    <script>
                        const defaultZutaten = <?= json_encode($zutaten) ?>;
                        const defaultPortionen = <?= $rezept['Portionen'] ?>;

                        let zutaten = defaultZutaten;

                        zutaten.forEach(zutat => {
                            zutat.checked = false;
                        });

                        let portionen = defaultPortionen;

                        function updateZutaten() {
                            $('#zutaten').empty();

                            $('#customTabellen').empty();
                            let tabels = [];
                            zutaten.forEach(zutat => {
                                if (zutat.table) {
                                    if (!tabels.includes(zutat.table)) {
                                        tabels.push(zutat.table);
                                    }
                                }
                            });

                            //erstelle tabellen mit der id #zutaten + index und füge sie in customTabellen ein
                            tabels.forEach(table => {
                                const index = tabels.indexOf(table);
                                const tableElement = document.createElement('table');
                                tableElement.id = `zutaten${index}`;
                                tableElement.classList.add('tabelle');

                                const tableTitle = document.createElement('h3');
                                tableTitle.innerText = table;
                                $('#customTabellen').append(tableTitle);
                                $('#customTabellen').append(tableElement);
                            });

                            zutaten.forEach(zutat => {
                                const id = zutat.ID;

                                let unit = zutat.unit;

                                if (zutat.Menge > 1) {
                                    if (unit === 'Zehe') {
                                        unit = 'Zehen';
                                    }
                                }

                                let menge = zutat.Menge * portionen / defaultPortionen;
                                //format menge with 10.000,00
                                menge = new Intl.NumberFormat('de-DE').format(menge);

                                const isCheck = zutaten.find(z => z.ID === zutat.ID).checked;

                                const zutatLine = document.createElement('tr');
                                zutatLine.addEventListener('click', () => checkZutat(id));
                                zutatLine.innerHTML = `
                                    <td>
                                        ${menge} ${unit}
                                    </td>
                                    <td>
                                        ${zutat.Name} ${(zutat.additionalInfo ? `(${zutat.additionalInfo})` : '')}
                                    </td>
                                    <td>
                                        ${isCheck ? '<i class="fas fa-check" style="color: #4f7f00"></i>' : ''}
                                    </td>
                                `;
                                $('#zutaten' + (zutat.table ? tabels.indexOf(zutat.table) : '')).append(zutatLine);
                            });

                            document.getElementById('printPortionen').innerText = portionen;
                        }

                        updateZutaten();

                        $('#portionen').on('change', function() {
                            portionen = $(this).val();
                            updateZutaten();
                        });

                        function checkZutat(id) {
                            const zutat = zutaten.find(z => z.ID === id);
                            zutat.checked = !zutat.checked;
                            updateZutaten();
                        }

                    </script>

                    <a href="cart.php?rezept=<?= $id ?>" class="btn white no-print">
                        <i class="fas fa-shopping-cart"></i>
                        Zur Einkaufsliste hinzufügen</a>
                </div>

                <div style="grid-column: span 2">
                    <h2>Zubereitung</h2>

                    <?= $rezept['Zubereitung'] ?>
                </div>

                <div class="no-print">
                    <h2>Anmerkungen</h2>

                    <div id="anmerkung"></div>

                    <button id="editAnmerkung" class="btn white">
                        <i class="fas fa-edit"></i> Bearbeiten
                    </button>
                    <!-- QuillJS -->
                    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                    <script>

                        <?php
                        $anmerkung = $pdo->query("SELECT * FROM anmerkungen WHERE Rezept_ID = $id")->fetch();

                        if ($anmerkung) {
                            echo "document.getElementById('anmerkung').innerHTML = " . $anmerkung['Anmerkung'] . ";";
                        } else {
                            echo "document.getElementById('anmerkung').innerHTML = 'Keine Anmerkungen vorhanden';";
                        }

                        ?>

                        document.getElementById('editAnmerkung').addEventListener('click', () => {
                            const div = document.createElement('div');
                            div.style.position = 'fixed';
                            div.style.top = '0';
                            div.style.left = '0';
                            div.style.width = '100%';
                            div.style.height = '100%';
                            div.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                            div.style.display = 'flex';
                            div.style.justifyContent = 'center';
                            div.style.alignItems = 'center';
                            div.style.zIndex = '1000';

                            const innerDiv = document.createElement('div');
                            innerDiv.style.backgroundColor = 'var(--background)';
                            innerDiv.style.padding = '20px';
                            innerDiv.style.borderRadius = '10px';
                            innerDiv.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
                            innerDiv.style.width = '300px';
                            innerDiv.style.maxWidth = 'calc(100% - 40px)';
                            innerDiv.style.maxHeight = 'calc(100% - 40px)';
                            innerDiv.style.overflow = 'auto';

                            const h2 = document.createElement('h2');
                            h2.innerText = 'Anmerkung bearbeiten';
                            innerDiv.appendChild(h2);

                            const editor = document.createElement('div');
                            editor.id = 'editor';
                            innerDiv.appendChild(editor);

                            const button = document.createElement('button');
                            button.innerText = 'Speichern';
                            button.classList.add('btn');
                            button.classList.add('green');
                            button.style.marginTop = '20px';
                            innerDiv.appendChild(button);

                            div.appendChild(innerDiv);
                            document.body.appendChild(div);

                            const quill = new Quill('#editor', {
                                theme: 'snow'
                            });

                            <?php
                            if ($anmerkung) {
                                echo "quill.root.innerHTML = " . $anmerkung['Anmerkung'] . ";";
                            }
                            ?>

                            button.addEventListener('click', () => {
                                const text = JSON.stringify(quill.root.innerHTML);
                                $.ajax({
                                    url: 'anmerkung.php',
                                    type: 'POST',
                                    data: {
                                        rezept: <?= $id ?>,
                                        text: text
                                    }
                                }).done(() => {
                                    location.reload();
                                });
                                div.remove();
                            });

                            div.addEventListener('click', () => {
                                if (confirm('Möchtest du die Änderungen verwerfen?')) {
                                    div.remove();
                                }
                            });

                            innerDiv.addEventListener('click', (e) => {
                                e.stopPropagation();
                            });

                        });
                    </script>

                </div>

                <div class="no-print">
                    <h2>Bewertungen</h2>
                    <?php
                    $bewertungen = $pdo->query("SELECT * FROM bewertungen WHERE Rezept_ID = $id")->fetchAll();
                    if (count($bewertungen) > 0) {
                        foreach ($bewertungen as $bewertung) {
                            echo "<div style='margin-bottom: 20px; padding: 10px; border-radius: 10px; background-color: var(--secondaryBackground)'>
                                <h3 style='text-transform: none'>$bewertung[Name]</h3>
                                <p style='margin-top: 10px; margin-bottom: 10px'>$bewertung[Text]</p>
                                <p style='margin-top: 10px; margin-bottom: 10px'>";
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $bewertung['Bewertung']) {
                                    echo "<i class='fas fa-star' style='color: var(--darkerRed)'></i>";
                                } else {
                                    echo "<i class='fas fa-star' style='color: var(--nonSelected)'></i>";
                                }
                            }
                            echo "</p>"
                            ?>

<!--                            Edit button-->
                            <button style="padding: 10px 20px; background-color: #fac2d7; color: var(--color); border: none; border-radius: 10px; cursor: pointer; -webkit-tap-highlight-color: rgba(255, 255, 255, 0);"
                            id="editBewertung<?= $bewertung['ID'] ?>">
                                <i class="fas fa-edit"></i> Bearbeiten
                            </button>

                            <script>
                                document.getElementById('editBewertung<?= $bewertung['ID'] ?>').addEventListener('click', () => {
                                    let bewertung = <?= $bewertung['Bewertung'] ?>;

                                    const div = document.createElement('div');
                                    div.style.position = 'fixed';
                                    div.style.top = '0';
                                    div.style.left = '0';
                                    div.style.width = '100%';
                                    div.style.height = '100%';
                                    div.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                                    div.style.display = 'flex';
                                    div.style.justifyContent = 'center';
                                    div.style.alignItems = 'center';
                                    div.style.zIndex = '1000';

                                    const innerDiv = document.createElement('div');
                                    innerDiv.style.backgroundColor = 'var(--background)';
                                    innerDiv.style.padding = '20px';
                                    innerDiv.style.borderRadius = '10px';
                                    innerDiv.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
                                    innerDiv.style.width = '300px';
                                    innerDiv.style.maxWidth = 'calc(100% - 40px)';
                                    innerDiv.style.maxHeight = 'calc(100% - 40px)';
                                    innerDiv.style.overflow = 'auto';

                                    const h2 = document.createElement('h2');
                                    h2.innerText = 'Bewertung bearbeiten';
                                    innerDiv.appendChild(h2);

                                    //stars
                                    const stars = document.createElement('div');
                                    stars.style.display = 'flex';
                                    stars.style.justifyContent = 'center';
                                    stars.style.gap = '10px';

                                    function updateStars(i) {
                                        for (let j = 1; j <= 5; j++) {
                                            if (j <= i) {
                                                stars.children[j - 1].style.color = 'var(--darkerRed)';
                                            } else {
                                                stars.children[j - 1].style.color = 'var(--nonSelected)';
                                            }
                                        }
                                        bewertung = i;
                                    }

                                    for (let i = 1; i <= 5; i++) {
                                        const star = document.createElement('i');
                                        star.classList.add('fas');
                                        star.classList.add('fa-star');
                                        star.style.fontSize = '30px';
                                        star.style.color = 'var(--nonSelected)';
                                        star.style.cursor = 'pointer';
                                        star.addEventListener('click', () => {
                                            updateStars(i);
                                        });
                                        stars.appendChild(star);
                                    }
                                    updateStars(bewertung);

                                    innerDiv.appendChild(stars);

                                    //username
                                    const username = document.createElement('input');
                                    username.type = 'text';
                                    username.placeholder = 'Dein Name';
                                    username.style.width = '100%';
                                    username.style.marginTop = '10px';
                                    username.style.textTransform = 'none';
                                    username.value = '<?= $bewertung['Name'] ?>';
                                    innerDiv.appendChild(username);

                                    //optional text
                                    const text = document.createElement('textarea');
                                    text.placeholder = 'Deine Bewertung (optional)';
                                    text.style.width = '100%';
                                    text.style.height = '100px';
                                    text.style.textTransform = 'none';
                                    text.value = '<?= $bewertung['Text'] ?>';
                                    innerDiv.appendChild(text);

                                    //bewerten button
                                    const button = document.createElement('div');
                                    button.innerText = 'Bewerten';
                                    button.classList.add('btn');
                                    button.classList.add('green');
                                    innerDiv.appendChild(button);

                                    //delete button
                                    const deleteButton = document.createElement('div');
                                    deleteButton.innerText = 'Löschen';
                                    deleteButton.classList.add('btn');
                                    deleteButton.classList.add('red');
                                    innerDiv.appendChild(deleteButton);

                                    div.appendChild(innerDiv);
                                    document.body.appendChild(div);

                                    button.addEventListener('click', () => {
                                        const name = username.value;
                                        const textValue = text.value;

                                        if (bewertung === 0) {
                                            alert('Bitte bewerte das Rezept');
                                            return;
                                        }

                                        if (name === '') {
                                            alert('Bitte gib deinen Namen an');
                                            return;
                                        }

                                        $.ajax({
                                            url: 'bewerten.php',
                                            type: 'POST',
                                            data: {
                                                rezept: <?= $id ?>,
                                                bewertung: bewertung,
                                                name: name,
                                                text: textValue,
                                                edit: <?= $bewertung['ID'] ?>
                                            },
                                            success: function() {
                                                window.location.reload();
                                            }
                                        });
                                    });

                                    deleteButton.addEventListener('click', () => {
                                        if (confirm('Möchtest du die Bewertung wirklich löschen?')) {
                                            $.ajax({
                                                url: 'bewerten.php',
                                                type: 'POST',
                                                data: {
                                                    delete: true,
                                                    id: <?= $bewertung['ID'] ?>
                                                },
                                                success: function() {
                                                    window.location.reload();
                                                }
                                            });
                                        }
                                    });

                                    div.addEventListener('click', () => {
                                        if (confirm('Möchtest du die Bewertung wirklich abbrechen?')) {
                                            div.remove();
                                        }
                                    });

                                    innerDiv.addEventListener('click', (e) => {
                                        e.stopPropagation();
                                    });

                                });
                            </script>

                            <?php
                            echo "</div>";
                        }
                    } else {
                        echo "<p>Keine Bewertungen</p>";
                    }
                    ?>

                    <div class="btn white" id="bewerten">
                        <i class="far fa-star"></i> Bewerten</div>
                    <script>
                        document.getElementById('bewerten').addEventListener('click', () => {
                            let bewertung = 0;

                            const div = document.createElement('div');
                            div.style.position = 'fixed';
                            div.style.top = '0';
                            div.style.left = '0';
                            div.style.width = '100%';
                            div.style.height = '100%';
                            div.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                            div.style.display = 'flex';
                            div.style.justifyContent = 'center';
                            div.style.alignItems = 'center';
                            div.style.zIndex = '1000';

                            const innerDiv = document.createElement('div');
                            innerDiv.style.backgroundColor = 'var(--background)';
                            innerDiv.style.padding = '20px';
                            innerDiv.style.borderRadius = '10px';
                            innerDiv.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
                            innerDiv.style.width = '300px';
                            innerDiv.style.maxWidth = 'calc(100% - 40px)';
                            innerDiv.style.maxHeight = 'calc(100% - 40px)';
                            innerDiv.style.overflow = 'auto';

                            const h2 = document.createElement('h2');
                            h2.innerText = '<?php echo $rezept['Name'] ?> bewerten';
                            innerDiv.appendChild(h2);


                            //stars
                            const stars = document.createElement('div');
                            stars.style.display = 'flex';
                            stars.style.justifyContent = 'center';
                            stars.style.gap = '10px';
                            for (let i = 1; i <= 5; i++) {
                                const star = document.createElement('i');
                                star.classList.add('fas');
                                star.classList.add('fa-star');
                                star.style.fontSize = '30px';
                                star.style.color = 'var(--nonSelected)';
                                star.style.cursor = 'pointer';
                                star.addEventListener('click', () => {
                                    for (let j = 1; j <= 5; j++) {
                                        if (j <= i) {
                                            stars.children[j - 1].style.color = 'var(--darkerRed)';
                                        } else {
                                            stars.children[j - 1].style.color = 'var(--nonSelected)';
                                        }
                                    }
                                    bewertung = i;
                                });
                                stars.appendChild(star);
                            }
                            innerDiv.appendChild(stars);

                            //username
                            const username = document.createElement('input');
                            username.type = 'text';
                            username.placeholder = 'Dein Name';
                            username.style.width = '100%';
                            username.style.marginTop = '10px';
                            username.style.textTransform = 'none';
                            innerDiv.appendChild(username);

                            //optional text
                            const text = document.createElement('textarea');
                            text.placeholder = 'Deine Bewertung (optional)';
                            text.style.width = '100%';
                            text.style.height = '100px';
                            text.style.textTransform = 'none';
                            innerDiv.appendChild(text);


                            //bewerten button
                            const button = document.createElement('div');
                            button.innerText = 'Bewerten';
                            button.classList.add('btn');
                            button.classList.add('green');
                            innerDiv.appendChild(button);

                            div.appendChild(innerDiv);
                            document.body.appendChild(div);

                            button.addEventListener('click', () => {
                                const name = username.value;
                                const textValue = text.value;

                                if (bewertung === 0) {
                                    alert('Bitte bewerte das Rezept');
                                    return;
                                }

                                if (name === '') {
                                    alert('Bitte gib deinen Namen an');
                                    return;
                                }

                                $.ajax({
                                    url: 'bewerten.php',
                                    type: 'POST',
                                    data: {
                                        rezept: <?= $id ?>,
                                        bewertung: bewertung,
                                        name: name,
                                        text: textValue
                                    },
                                    success: function() {
                                        window.location.reload();
                                    }
                                });
                            });

                            div.addEventListener('click', () => {
                                if (confirm('Möchtest du die Bewertung wirklich abbrechen?')) {
                                    div.remove();
                                }
                            });

                            innerDiv.addEventListener('click', (e) => {
                                e.stopPropagation();
                            });

                        });
                    </script>
                </div>


                <div class="no-print">
                    <h2>Bearbeiten</h2>
                    <a href="addRezept.php?rezept=<?= $id ?>" class="btn green">
                        <i class="fas fa-edit"></i> Rezept bearbeiten</a>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
