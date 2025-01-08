<?php

include "shared/global.php";
global $pdo;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    die();
}

$id = $_GET['id'];

$rezept = json_decode(file_get_contents(BASE_URL. "api?task=getRezept&id=$id&zutaten"), true)[0];

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

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!--    QrCode-->
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>

    <style>

        #zutaten-tables {
            display: grid;
            gap: 20px;
        }

        #zutaten-tables h3 {
            margin-bottom: 5px;
        }

        .zutaten {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .zutaten li {
            display: grid;
            grid-template-columns: 20px 1fr 20px;
            gap: 10px;
            align-items: center;
            width: 100%;
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            transition: background 0.2s ease;
        }

        .zutaten li:hover {
            background: var(--nonSelected);
        }

        .zutaten li:nth-child(even) {
            background: transparent;
        }

        .zutaten li:nth-child(even):hover {
            background: var(--nonSelected);
        }

        .fa-check {
            animation: checkIn 0.5s forwards cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: bold;
        }

        @keyframes checkIn {
            0% {
                transform: scale(0) rotate(0deg);
                color: var(--green);
            }
            100% {
                transform: scale(1) rotate(360deg);
                color: var(--darkerGreen);
            }
        }

        .quill {
            padding: 10px;
            background: var(--secondaryBackground);
            border-radius: 10px;
        }

        .quill ul, .quill ol {
            padding-left: 20px;
        }

        .rezept {
            display: grid;
            gap: 20px;
            grid-template-columns: 1fr;
            margin-top: 20px;
        }

        .infos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .infos > div {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            transition: background 0.2s ease;
            user-select: none;
        }

        .infos > div:hover {
            background: var(--nonSelected);
        }

        #print:hover {
            background: var(--green);
            cursor: pointer;
        }

        #edit:hover {
            background: var(--red);
            cursor: pointer;
        }

        #addKalender:hover {
            background: var(--blue);
            cursor: pointer;
        }

        #qrCode:hover {
            background: #36e39e;
            cursor: pointer;
        }

        .images {
            display: flex;
            gap: 10px;
            width: 100%;
            flex-wrap: wrap;
        }

        .images img {
            border-radius: 10px;
            max-height: 500px;
            max-width: 100%;
        }

        @media print {
            .images img {
                max-height: 250px;
                max-width: 250px;
            }
        }

        .infos .print {
            cursor: pointer;
            background: var(--green);
            padding: 10px;
            border-radius: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .infos .print:hover {
            background: var(--darkerGreen);
        }

        #kalender {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            list-style: none;
        }

        #kalender li {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }

        #bewertungen {
            list-style: none;
            padding: 0;
            width: 100%;
            display: grid;
            gap: 10px;
        }

        #bewertungen li {
            display: grid;
            grid-template-columns: 50px 1fr;
            gap: 10px;
            align-items: start;
            width: 100%;
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            transition: background 0.2s ease;
        }

        #bewertungen li:hover {
            background: var(--nonSelected);
        }

        #bewertungen img {
            border-radius: 5px;
        }

        #bewertungForm {
            display: grid;
            gap: 10px;
        }

        #bewertungForm div {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr auto;
        }

        #bewertungForm input, #bewertungForm textarea {
            padding: 5px;
            border-radius: 5px;
            border: none;
            outline: none;
            width: 100%;
            background: var(--nonSelected);
            color: var(--color);
        }

        #bewertungen li:first-child:hover {
            background: var(--secondaryBackground);
        }

        #bewertungForm textarea {
            max-width: 100%;
            min-width: 100%;
            min-height: 100px;
            max-height: 300px;
            resize: vertical;
        }

        #bewertungForm button {
            padding: 5px;
            border-radius: 5px;
            border: none;
            background: var(--green);
            color: var(--background);
            cursor: pointer;
        }

        #bewertungForm button:hover {
            background: var(--darkerGreen);
        }

        #bewertungForm #bewertungStars {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .fa-star {
            cursor: pointer;
        }

        #anmerkungen {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        #anmerkungen li {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            transition: background 0.2s ease;
            display: grid;
            grid-template-columns: 1fr auto;
        }

        #anmerkungen li:hover {
            background: var(--nonSelected);
        }
    </style>

</head>
<body>
<div class="nav-grid-content">
    <?php
    require_once 'shared/navbar.php';
    ?>
        <div class="container">
            <h1><?= $rezept['Name'] ?></h1>

            <div class="rezept">
                <div class="images">
                    <?php
                    foreach ($rezept['Bilder'] as $bild) {
                        $bild = $bild["Image"];
                        echo "<img src='$bild' alt=''>";
                    }
                    ?>
                </div>

                <div class="infos">
                    <div title="Ausgelegt für <?= $rezept['Portionen'] ?> Portionen" id="portionenInfo">
                        <i class="fas fa-users"></i>
                        <span id="portionen">
                            <?= $rezept['Portionen'] ?>
                        </span>
                         Portionen
                    </div>

                    <script>
                        document.getElementById('portionenInfo').addEventListener('click', () => {
                            let form = new FormBuilder('Portionen', (formData) => {
                                document.getElementById('portionenInput').value = formData['Portionen'];
                                renderZutaten();
                            }, () => {});

                            form.addHeader('Ausgelegt für <?= $rezept['Portionen'] ?> Portionen');

                            form.addCustomNumberField('Portionen', 1, 100, 1, document.getElementById('portionenInput').value);

                            form.renderForm();
                        });
                    </script>

                    <div>
                        <i class="fas fa-clock"></i>
                        <?php
                        $time = $rezept['Zeit'];
                        $hours = floor($time / 60);
                        $minutes = $time % 60;

                        if ($hours > 0) {
                            echo "$hours h ";
                        }

                        if ($minutes > 0) {
                            echo "$minutes min";
                        }

                        ?>
                    </div>

                    <div>
                        <i class="fas fa-tag"></i>
                        <?= $rezept['Kategorie'] ?>
                    </div>

                    <?php
                        //bewertung, wenn vorhanden durchschnitt ausgeben
                        if (count($rezept['Bewertungen']) > 0) {
                            $sum = 0;
                            foreach ($rezept['Bewertungen'] as $bewertung) {
                                $sum += $bewertung['Bewertung'];
                            }
                            $avg = $sum / count($rezept['Bewertungen']);

                            echo "<div style='display: flex; gap: 5px;' class='no-print'>";
                            //sternchen und halbe sternchen ausgeben
                            for ($i = 0; $i < floor($avg); $i++) {
                                echo "<i class='fas fa-star'></i>";
                            }
                            if ($avg - floor($avg) >= 0.5) {
                                echo "<i class='fas fa-star-half-alt'></i>";
                            }
                            //leere sternchen ausgeben
                            for ($i = 0; $i < 5 - ceil($avg); $i++) {
                                echo "<i class='far fa-star'></i>";
                            }
                            echo "</div>";

                        }
                    ?>

                    <?php
                    $OptionalInfos = json_decode(($rezept['OptionalInfos'] ?? '[]'), true);
                    if (count($OptionalInfos) > 0) {
                        foreach ($OptionalInfos as $info) {
                            echo "<div>";
                            echo "<span>{$info['title']}: {$info['content']}</span>";
                            echo "</div>";
                        }
                    }
                    ?>
                </div>

                <div class="infos no-print">
                    <div onclick="window.print()" id="print">
                         <i class="fas fa-print"></i>
                    </div>

                    <div onclick="window.location.href = 'new?rezept=<?= $rezept['ID'] ?>'" id="edit">
                         <i class="fas fa-edit"></i>
                    </div>

                    <div id="addKalender" onclick="addKalender()">
                         <i class="fas fa-calendar-plus"></i>
                    </div>
                    <script>

                        function addKalender() {
                            let form = new FormBuilder("Planen", (formData) => {

                                fetch('api.php?task=addKalender&rezept=<?= $rezept['ID'] ?>&date=' + formData['Datum'] + '&info=' + formData['Text'], {
                                    method: 'GET'
                                }).then(() => {
                                    window.location = 'calendar.php';
                                });
                            }, () => {});

                            form.addDateInput("Datum", new Date().toISOString().split('T')[0]);
                            form.addInputField("Text", "Mehr Text", "");

                            form.renderForm();
                        }

                    </script>

                    <div id="qrCode" style="cursor: pointer;">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <script>
                        document.getElementById('qrCode').addEventListener('click', () => {
                            let qrCode = new QRCode(document.createElement('div'), {
                                text: '<?= BASE_URL ?>rating?id=<?= $rezept['ID'] ?>',
                                width: 256,
                                height: 256,
                                colorDark: "#000000",
                                colorLight: "transparent",
                                correctLevel: QRCode.CorrectLevel.H
                            });

                            const qrCodeForm = new FormBuilder('QR-Code', () => {}, () => {});
                            qrCodeForm.addHTML(`
                                <div style="display: flex; justify-content: center;">
                                    <div id="qrCodeForm"></div>
                                </div>
                            `)

                            qrCodeForm.addButton('Öffnen', () => {
                                window.open("<?php echo BASE_URL ?>rating?id=<?= $rezept['ID'] ?>");
                            });

                            qrCodeForm.renderForm(false);

                            document.getElementById('qrCodeForm').appendChild(qrCode._el);

                        });
                    </script>
                </div>

                <h2>Zutaten</h2>
                <div id="zutaten-tables"></div>
                <div style="display: flex; justify-content: center;" class="no-print">
                    <div class="number-input" style="width: auto;">
                        <button type="button" class="down"><i class="fas fa-minus"></i></button>
                        <input id="portionenInput" min="1" name="portionen" value="<?= $rezept['Portionen'] ?>" step="1" type="number" onchange="renderZutaten()">
                        <button type="button" class="up"><i class="fas fa-plus"></i></button>
                    </div>
                </div>

                <script>
                    const zutaten = <?= json_encode($rezept['Zutaten_JSON']) ?>;
                    const zutatenTables = <?= json_encode($rezept['ZutatenTables']) ?>;
                    let portionen = <?= $rezept['Portionen'] ?>;

                    function renderZutaten() {
                        const zutatenTablesContainer = document.getElementById('zutaten-tables');
                        zutatenTablesContainer.innerHTML = '';

                        portionen = document.getElementById('portionenInput').value;
                        document.getElementById('portionen').innerText = portionen;

                        // Create tables
                        zutatenTables.forEach(table => {
                            const tableDiv = document.createElement('div');
                            tableDiv.classList.add('zutaten-table');
                            if (table !== '') {
                                tableDiv.innerHTML = `<h3>${table}</h3>`;
                            }
                            tableDiv.innerHTML += `<ul class="zutaten" id="zutaten-list-${table}"></ul>`;
                            zutatenTablesContainer.appendChild(tableDiv);
                        });

                        // Add ingredients to the correct tables
                        zutaten.forEach(zutat => {
                            const zutatenList = document.getElementById(`zutaten-list-${zutat.table}`);
                            const li = document.createElement('li');

                            let newMenge = zutat.Menge * portionen / <?= $rezept['Portionen'] ?>;
                            newMenge = newMenge.toLocaleString('de-DE', {maximumFractionDigits: 2});

                            li.innerHTML = `
                                <img src="${zutat.Image}" alt="" width="20px" height="20px">
                                ${newMenge} ${zutat.unit} ${zutat.Name} ${zutat.additionalInfo}
                                <i class="fas no-print"></i>
                            `;
                            li.addEventListener('click', () => {
                                li.querySelector('i').classList.toggle('fa-check');
                            });
                            zutatenList.appendChild(li);
                        });
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        renderZutaten();
                    });
                </script>

                <h2>Zubereitung</h2>
                <div class="quill">
                    <p><?= $rezept['Zubereitung'] ?></p>
                </div>

                <h2>Anmerkungen</h2>
                <ul id="anmerkungen">
                    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
                    <?php

                    foreach ($rezept['Anmerkungen'] as $anmerkung) {

                        if (empty($anmerkung['Anmerkung'])) {
                            continue;
                        }

                        echo '
                            <script >
                                let anmerkungGroup = document.createElement("li");
                                document.getElementById("anmerkungen").appendChild(anmerkungGroup);
                                
                                let anmerkung = document.createElement("div");
                                anmerkung.innerHTML = ' . $anmerkung['Anmerkung'] . ';
                                anmerkungGroup.appendChild(anmerkung);
                             
                                let anmerkungIcon = document.createElement("i");
                                anmerkungIcon.classList.add("fas");
                                anmerkungIcon.classList.add("fa-edit");
                                anmerkungGroup.appendChild(anmerkungIcon);
                              
                                anmerkungGroup.addEventListener("click", () => {
                                    let anmerkungsForm = new FormBuilder("Anmerkung bearbeiten", (formData) => {
                                    
                                        console.log(formData);
                                    
                                        fetch("api.php?task=anmerkung&rezept=' . $rezept['ID'] . '&text=" + formData["Anmerkung"], {
                                            method: "GET"
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }, () => {});
                                    
                                    anmerkungsForm.addQuillField("Anmerkung", "Anmerkung", ' . $anmerkung['Anmerkung'] . ');
                                    anmerkungsForm.renderForm();
                                });
                            </script>
                        ';
                    }

                    if (count($rezept['Anmerkungen']) == 0) {
                        echo '
                            <script >
                                let anmerkungGroup = document.createElement("li");
                                document.getElementById("anmerkungen").appendChild(anmerkungGroup);
                                
                                let anmerkung = document.createElement("div");
                                anmerkung.innerHTML = "";
                                anmerkungGroup.appendChild(anmerkung);
                             
                                let anmerkungIcon = document.createElement("i");
                                anmerkungIcon.classList.add("fas");
                                anmerkungIcon.classList.add("fa-edit");
                                anmerkungIcon.classList.add("no-print");
                                anmerkungGroup.appendChild(anmerkungIcon);
                              
                                anmerkungGroup.addEventListener("click", () => {
                                    let anmerkungsForm = new FormBuilder("Anmerkung bearbeiten", (formData) => {
                                    
                                        console.log(formData);
                                    
                                        fetch("api.php?task=anmerkung&rezept=' . $rezept['ID'] . '&text=" + formData["Anmerkung"], {
                                            method: "GET"
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }, () => {});
                                    
                                    anmerkungsForm.addQuillField("Anmerkung", "Anmerkung", "");
                                    anmerkungsForm.renderForm();
                                });
                            </script>
                        ';
                    }

                    ?>
                </ul>

                <?php
                    if ($rezept['Kalender'] != null && count($rezept['Kalender']) > 0) {
                ?>
                <h2 class="no-print">Kalender</h2>
                <ul class="no-print" id="kalender">
                    <?php
                    foreach ($rezept['Kalender'] as $kalender) {
                        echo "<li>" . date("d.m.Y", strtotime($kalender['Datum'])) . "</li>";
                    }
                    ?>
                </ul>
                <?php
                    }
                ?>

                <h2 class="no-print">Bewertungen</h2>
                <ul class="no-print" id="bewertungen">

                    <li>
                        <img src="https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" alt="" id="bewertungImage">
                        <div id="bewertungForm">
                            <div>
                                <input type="text" placeholder="Name" id="bewertungName">
                                <input type="number" placeholder="Bewertung" min="0" max="5" hidden>
                                <div id="bewertungStars">
                                    <!--                                    stars-->
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <script>
                                    document.getElementById("bewertungStars").addEventListener("click", (e) => {
                                        let stars = document.getElementById("bewertungStars").children;
                                        let rating = 0;
                                        for (let i = 0; i < stars.length; i++) {
                                            if (e.target === stars[i]) {
                                                rating = i + 1;
                                            }
                                        }
                                        document.getElementById("bewertungForm").querySelector("input[type=number]").value = rating;
                                        for (let i = 0; i < stars.length; i++) {
                                            if (i < rating) {
                                                stars[i].classList.remove("far");
                                                stars[i].classList.add("fas");
                                            } else {
                                                stars[i].classList.remove("fas");
                                                stars[i].classList.add("far");
                                            }
                                        }
                                    });
                                </script>
                            </div>
                            <textarea placeholder="Text"></textarea>
                            <button onclick="bewerten()">Bewerten</button>
                            <script>
                                function bewerten() {
                                    let name = document.getElementById("bewertungName").value;
                                    let rating = document.getElementById("bewertungForm").querySelector("input[type=number]").value;
                                    let text = document.getElementById("bewertungForm").querySelector("textarea").value;

                                    if (name === "" || rating === "" || text === "") {
                                        return;
                                    }

                                    fetch('api.php?task=addEvaluation&rezept=<?= $rezept['ID'] ?>&name=' + name + '&rating=' + rating + '&text=' + text, {
                                        method: 'GET'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            </script>
                        </div>
                        <script>
                            document.getElementById("bewertungName").addEventListener("input", () => {
                                document.getElementById("bewertungImage").src = "https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" + document.getElementById("bewertungName").value;
                            });
                        </script>
                    </li>

                    <?php
                    foreach ($rezept['Bewertungen'] as $bewertung) {

                        $stars = "";
                        //sternchen und halbe sternchen ausgeben
                        for ($i = 0; $i < floor($bewertung['Bewertung']); $i++) {
                            $stars .= "<i class='fas fa-star'></i>";
                        }
                        if ($bewertung['Bewertung'] - floor($bewertung['Bewertung']) >= 0.5) {
                            $stars .= "<i class='fas fa-star-half-alt'></i>";
                        }
                        for ($i = 0; $i < 5 - ceil($bewertung['Bewertung']); $i++) {
                            $stars .= "<i class='far fa-star'></i>";
                        }


                        echo "<li id='bewertung{$bewertung['ID']}'>
                                <img src='{$bewertung['Image']}' alt='' width='50px' height='50px'>
                                <div>
                                    <h3>{$bewertung['Name']} - $stars</h3>
                                    <p>{$bewertung['Text']}</p>
                                </div>
                            </li>
                            
                            <script >
                            
                            document.getElementById('bewertung{$bewertung['ID']}').addEventListener('click', () => {
                                let bewertungsForm = new FormBuilder('Bewertung bearbeiten', (formData) => {
                                    fetch('api.php?task=editEvaluation&rezept={$bewertung['ID']}&name=' + formData['Name'] + '&rating=' + formData['Bewertung'] + '&text=' + formData['Text'], {
                                        method: 'GET'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }, () => {});
                                
                                bewertungsForm.addInputField('Name', 'Name', '{$bewertung['Name']}');
                                bewertungsForm.addNumberField('Bewertung', 0, 5, '{$bewertung['Bewertung']}');
                                bewertungsForm.addInputField('Text', 'Text', '{$bewertung['Text']}');
                                
                                bewertungsForm.addButton('Löschen', () => {
                                    fetch('api.php?task=deleteEvaluation&id={$bewertung['ID']}', {
                                        method: 'GET'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                });
                                
                                bewertungsForm.renderForm();
                            });
                            </script>
                            
                            ";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</body>
<script>
    let kiChat = new KiChat();

    kiChat.addKontext({
        name: "Rezept",
        value: <?= json_encode($rezept) ?>
    });
</script>
</html>
