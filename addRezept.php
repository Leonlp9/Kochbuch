<?php
include 'shared/global.php';
global $pdo;

//add column optionalInfos json if not exists
$stmt = $pdo->query("SHOW COLUMNS FROM rezepte LIKE 'OptionalInfos'");
if ($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("ALTER TABLE rezepte ADD OptionalInfos TEXT");
    $stmt->execute();
}

$edit = false;
//bearbeiten
if (isset($_GET['rezept'])) {
    $rezeptID = $_GET['rezept'];

    $stmt = $pdo->prepare("SELECT * FROM rezepte WHERE ID = ?");
    $stmt->execute([$rezeptID]);
    $rezept = $stmt->fetch();
    $zutaten = json_decode($rezept['Zutaten_JSON'], true);
    $edit = true;
    $name = $rezept['Name'];
    $dauer = $rezept['Zeit'];
    $portionen = $rezept['Portionen'];
    $anleitung = $rezept['Zubereitung'];

    if ($rezept['OptionalInfos'] == null) {
        $optionalInfos = [];
    } else {
        $optionalInfos = json_decode($rezept['OptionalInfos'], true);
    }

    if ($rezept['OptionalInfos'] == null) {
        $optionalInfos = [];
    } else {
        $optionalInfos = json_decode($rezept['OptionalInfos'], true);
    }

    //add name and unit to every zutat
    for ($i = 0; $i < count($zutaten); $i++) {
        $stmt = $pdo->prepare("SELECT * FROM zutaten WHERE ID = ?");
        $stmt->execute([$zutaten[$i]['ID']]);
        $zutat = $stmt->fetch();

        $zutaten[$i]['Name'] = $zutat['Name'];
        $zutaten[$i]['Einheit'] = $zutat['unit'];
        $zutaten[$i]['Image'] = (file_exists('ingredientIcons/' . $zutat['Image']) ? $zutat['Image'] : 'default.svg');

        //add additional info to zutaten but when it is not set, then set it to an empty string
        if (isset($zutaten[$i]['additionalInfo'])) {
            $zutaten[$i]['AdditionalInfo'] = $zutaten[$i]['additionalInfo'];
        } else {
            $zutaten[$i]['AdditionalInfo'] = '';
        }

        //add table to zutaten but when it is not set, then set it to an empty string
        if (isset($zutaten[$i]['table'])) {
            $zutaten[$i]['Tabelle'] = $zutaten[$i]['table'];
        } else {
            $zutaten[$i]['Tabelle'] = '';
        }
    }
}

if (isset($_GET['edit'])) {
    //bearbeiten
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $kategorie = $_POST['kategorie'];
        $dauer = $_POST['dauer'];
        $portionen = $_POST['portionen'];
        $anleitung = $_POST['anleitung'];
        $zutaten = json_decode($_POST['zutaten']);
        $files = $_FILES['bilder'];
        $optionalInfos = json_decode($_POST['extraCustomInfos']);

        $sql = "UPDATE rezepte SET Name = :name, Kategorie_ID = :kategorie, Zeit = :dauer, Portionen = :portionen, Zubereitung = :anleitung, Zutaten_JSON = :zutaten, OptionalInfos = :optionalInfos WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'kategorie' => $kategorie,
            'dauer' => $dauer,
            'portionen' => $portionen,
            'anleitung' => $anleitung,
            'zutaten' => json_encode($zutaten),
            'optionalInfos' => json_encode($optionalInfos),
            'id' => $rezeptID
        ]);

        //Bilder als webp convertieren und speichern
        foreach ($files['name'] as $key => $file) {
            $fileName = $files['name'][$key];
            $fileTmpName = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];
            $fileError = $files['error'][$key];
            $fileType = $files['type'][$key];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    $img = imagecreatefromstring(file_get_contents($fileTmpName));
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);

                    // Überprüfe die aktuellen Dimensionen des Bildes
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $maxWidth = 1080;
                    $maxHeight = 566;

                    // Berechne das Seitenverhältnis
                    $aspectRatio = $width / $height;

                    // Berechne die neuen Dimensionen, falls das Bild zu groß ist
                    if ($width > $maxWidth || $height > $maxHeight) {
                        if ($aspectRatio > ($maxWidth / $maxHeight)) {
                            $newWidth = $maxWidth;
                            $newHeight = $maxWidth / $aspectRatio;
                        } else {
                            $newHeight = $maxHeight;
                            $newWidth = $maxHeight * $aspectRatio;
                        }

                        // Erstelle ein neues, skaliertes Bild
                        $newImg = imagecreatetruecolor($newWidth, $newHeight);
                        imagealphablending($newImg, false);
                        imagesavealpha($newImg, true);
                        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($img);
                        $img = $newImg;
                    }

                    // Speichern des Bildes im WebP-Format
                    $fileNameNew = uniqid('', true) . ".webp";
                    $fileDestination = 'uploads/' . $fileNameNew;
                    imagewebp($img, $fileDestination, 45);
                    imagedestroy($img);

                    // SQL zum Einfügen in die Datenbank
                    $sql = "INSERT INTO bilder (Rezept_ID, Image) VALUES (:rezeptID, :image)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'rezeptID' => $rezeptID,
                        'image' => $fileNameNew
                    ]);
                }
            }
        }

        header('Location: rezept.php?id=' . $rezeptID);
    }
}else {
//hinzufügen
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $kategorie = $_POST['kategorie'];
        $dauer = $_POST['dauer'];
        $portionen = $_POST['portionen'];
        $anleitung = $_POST['anleitung'];
        $zutaten = json_decode($_POST['zutaten']);
        $files = $_FILES['bilder'];
        $optionalInfos = json_decode($_POST['extraCustomInfos']);

        $sql = "INSERT INTO rezepte (Name, Kategorie_ID, Zeit, Portionen, Zubereitung, Zutaten_JSON, OptionalInfos) VALUES (:name, :kategorie, :dauer, :portionen, :anleitung, :zutaten, :optionalInfos)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'kategorie' => $kategorie,
            'dauer' => $dauer,
            'portionen' => $portionen,
            'anleitung' => $anleitung,
            'zutaten' => json_encode($zutaten),
            'optionalInfos' => json_encode($optionalInfos)
        ]);

        $rezeptID = $pdo->lastInsertId();


        //Bilder als webp convertieren und speichern
        foreach ($files['name'] as $key => $file) {
            $fileName = $files['name'][$key];
            $fileTmpName = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];
            $fileError = $files['error'][$key];
            $fileType = $files['type'][$key];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    $img = imagecreatefromstring(file_get_contents($fileTmpName));
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);

                    // Überprüfe die aktuellen Dimensionen des Bildes
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $maxWidth = 1080;
                    $maxHeight = 566;

                    // Berechne das Seitenverhältnis
                    $aspectRatio = $width / $height;

                    // Berechne die neuen Dimensionen, falls das Bild zu groß ist
                    if ($width > $maxWidth || $height > $maxHeight) {
                        if ($aspectRatio > ($maxWidth / $maxHeight)) {
                            $newWidth = $maxWidth;
                            $newHeight = $maxWidth / $aspectRatio;
                        } else {
                            $newHeight = $maxHeight;
                            $newWidth = $maxHeight * $aspectRatio;
                        }

                        // Erstelle ein neues, skaliertes Bild
                        $newImg = imagecreatetruecolor($newWidth, $newHeight);
                        imagealphablending($newImg, false);
                        imagesavealpha($newImg, true);
                        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($img);
                        $img = $newImg;
                    }

                    // Speichern des Bildes im WebP-Format
                    $fileNameNew = uniqid('', true) . ".webp";
                    $fileDestination = 'uploads/' . $fileNameNew;
                    imagewebp($img, $fileDestination, 45);
                    imagedestroy($img);

                    // SQL zum Einfügen in die Datenbank
                    $sql = "INSERT INTO bilder (Rezept_ID, Image) VALUES (:rezeptID, :image)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'rezeptID' => $rezeptID,
                        'image' => $fileNameNew
                    ]);
                }
            }

        }

        header('Location: index.php');
        die();
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

    <link rel="stylesheet" href="style.css">

    <style>
        .preview {
            margin-top: 10px;
            padding: 10px;
            border: 2px solid var(--nonSelected);
            border-radius: 10px;
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .numbers {
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .zutaten {
            border-radius: 20px;
            background: var(--nonSelected);
            padding: 10px 20px;
        }

        #queryZutaten {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        #queryZutaten div {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            background: var(--selected);
            border: 2px solid var(--selected_highlight);
            border-bottom: 6px solid var(--selected_highlight);
            cursor: pointer;
            user-select: none;
            transition: background 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #queryZutaten div.addZutat {
            background: rgba(0, 0, 0, 0.1);
            border: 2px dashed rgba(0, 0, 0, 0.2);
            border-bottom: 6px dashed rgba(0, 0, 0, 0.2);
        }

        #queryZutaten div.addZutat:hover {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(0, 0, 0, 0.3);
        }

        #queryZutaten img {
            width: 50px;
            height: 50px;
            filter: drop-shadow(3px 3px 0px rgba(0, 0, 0, 0.1)) drop-shadow(3px 3px 3px rgba(0, 0, 0, 0.2));
        }

        #queryZutaten span {
            font-family: var(--font-family);
            word-break: break-word;
            text-align: center;
            hyphens: auto;
        }

        #queryZutaten span:nth-child(2) {
            font-weight: bold;
        }

        #queryZutaten div:hover {
            background: var(--selected_highlight);
            border-color: var(--selected_highlight);
        }

        .search {
            display: grid;
            align-items: center;
            grid-template-columns: 1fr auto;
            background: var(--secondaryBackground);
            border-radius: 20px;
        }

        .search i {
            padding: 10px;
        }

        #zutaten {
            margin-bottom: 0;
            border: none;
        }

        .zutatenTabelle{
            overflow-y: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: var(--font-family);
            margin-top: 10px;
            border-radius: 20px;
            background: var(--secondaryBackground);
            overflow: hidden;
            border: 2px solid var(--selected_highlight);
        }

        td, th {
            padding: 10px;
        }

        th {
            background: var(--nonSelected);
            color: var(--color);
        }

        tr:not(:last-child) {
            border-bottom: 2px solid var(--nonSelected);
        }

        th {
            text-align: left;
        }

        .editButton {
            background: var(--blue);
            border: 2px solid var(--darkerBlue);
            cursor: pointer;
            color: var(--color);
            height: 40px;
            width: 40px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .extraCustomTabellenInput {
            padding: 10px;
            border-radius: 10px;
            border: 2px solid var(--nonSelected);
            background: var(--secondaryBackground);
            color: var(--color);
            font-family: var(--font-family);
            font-size: 16px;
            transition: border 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            width: 160px;
        }

        #addBackground {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: grid;
            place-items: center;
            z-index: 100000;
        }

        #addBackground .popup {
            background: var(--secondaryBackground);
            border-radius: 20px;
            padding: 20px;
            display: grid;
            gap: 10px;
        }

        .zutatenTabelle {
            margin-top: 20px;
            margin-bottom: 40px;
        }

        #extraCustomInfosListe {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
            background: var(--secondaryBackground);
            border-radius: 20px;
            padding: 10px;
            margin-bottom: 20px;
        }

        #extraCustomInfosListe div {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            padding: 5px 5px 5px 15px;
            border-radius: 20px;
            background: var(--nonSelected);
            border: 2px solid var(--selected_highlight);
            color: var(--color);
            font-family: var(--font-family);
            font-size: 16px;
            border-bottom-width: 6px;
            cursor: pointer;
            user-select: none;
            -webkit-user-drag: none;
        }
    </style>
</head>
<body>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

            <h1>Rezept <?php echo $edit ? 'bearbeiten' : 'hinzufügen' ?></h1>
            <form action="addRezept.php<?php echo $edit ? '?edit=true&rezept=' . $rezeptID : '' ?>
" method="post" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required placeholder="Rezeptname" style="text-transform: none" value="<?php echo $edit ? $name : '' ?>">

                <label for="kategorie">Kategorie</label>

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
                        echo "<option value='" . $row['ID'] . "' " . ($edit && $rezept['Kategorie_ID'] == $row['ID'] ? 'selected' : '') . ">" . $row['Name'] . " (" . $row['Anzahl'] . ")</option>";
                    }
                    ?>
                </select>

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

<!--                Custom Infos Optional-->
                <div id="extraCustomInfos">
                    <label for="extraCustomInfos">Zusätzliche Informationen (Optional)</label>
                    <div class="extraCustomInfos" style="display: grid; grid-template-columns: 1fr auto; gap: 10px;">
                        <div style="display: grid; gap: 10px;">
                            <input type="text" class="extraCustomTabellenInput" placeholder="Titel" style="margin-bottom: 0; text-transform: none">
                            <input type="text" class="extraCustomTabellenInput" placeholder="Inhalt" style="margin-bottom: 0; text-transform: none">
                        </div>
                        <div class="editButton" style="height: auto;"
                        >+</div>
                    </div>
                    <div id="extraCustomInfosListe">
                    </div>
                    <script>
                        let extraCustomInfos = [];

                        <?php if ($edit) {
                            if ($optionalInfos != null) {
                                echo 'extraCustomInfos = ' . json_encode($optionalInfos) . ';';
                            }
                        } ?>

                        document.querySelector('.editButton').onclick = function() {
                            let inputs = document.querySelectorAll('.extraCustomTabellenInput');
                            let title = inputs[0].value;
                            let content = inputs[1].value;
                            extraCustomInfos.push({title: title, content: content});
                            updateExtraCustomInfos();

                            inputs[0].value = '';
                            inputs[1].value = '';
                        };

                        function updateExtraCustomInfos() {
                            let extraCustomInfosListe = document.querySelector('#extraCustomInfosListe');
                            extraCustomInfosListe.innerHTML = '';
                            for (let info of extraCustomInfos) {
                                let div = document.createElement('div');
                                div.innerHTML = info.title + ': ' + info.content;
                                div.onclick = function() {

                                    var background = document.createElement('div');
                                    background.id = 'addBackground';
                                    background.onclick = function() {
                                        this.remove();
                                    };
                                    var popup = document.createElement('div');
                                    popup.className = 'popup';
                                    popup.onclick = function(e) {
                                        e.stopPropagation();
                                    };
                                    background.appendChild(popup);
                                    document.body.appendChild(background);

                                    var popUpTitle = document.createElement('h2');
                                    popUpTitle.innerHTML = 'Zusätzliche Informationen bearbeiten';
                                    popup.appendChild(popUpTitle);

                                    var title = document.createElement('input');
                                    title.value = info.title;
                                    title.style.width = '100%';
                                    title.placeholder = 'Titel';
                                    title.className = 'extraCustomTabellenInput';

                                    var content = document.createElement('input');
                                    content.value = info.content;
                                    content.style.width = '100%';
                                    content.placeholder = 'Inhalt';
                                    content.className = 'extraCustomTabellenInput';

                                    var save = document.createElement('div');
                                    save.className = 'btn green';
                                    save.innerHTML = 'Speichern';
                                    save.onclick = function() {
                                        info.title = title.value;
                                        info.content = content.value;
                                        updateExtraCustomInfos();
                                        background.remove();
                                    };

                                    var remove = document.createElement('div');
                                    remove.className = 'btn red';
                                    remove.innerHTML = 'Löschen';
                                    remove.onclick = function() {
                                        extraCustomInfos.splice(extraCustomInfos.indexOf(info), 1);
                                        updateExtraCustomInfos();
                                        background.remove();
                                    };

                                    popup.appendChild(title);
                                    popup.appendChild(content);
                                    popup.appendChild(save);
                                    popup.appendChild(remove);


                                };
                                extraCustomInfosListe.appendChild(div);
                            }
                        }

                        updateExtraCustomInfos();

                        //stop the form from submitting when pressing enter in the input fields
                        document.querySelectorAll('.extraCustomTabellenInput').forEach(input => {
                            input.onkeypress = function(e) {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                }
                            };
                        });
                    </script>
                </div>
                <input type="hidden" name="extraCustomInfos" id="extraCustomInfosInput">

        <!--        Zutaten soll so sein, dass es eine such bar gibt, in der man nach etwas sucht und dann die Zutaten mit dem Namen dann aus der Datenbank angezeigt werden und wenn man die anklickt, dann sind die im rezept drin, und man kann noch die Menge wenn es hinzugefügt ist bestimmen-->
        <!--        getZutaten.php [{"ID":58,"0":58,"Name":"Ananas","1":"Ananas","Image":"ananas.svg","2":"ananas.svg","unit":"St\u00fcck","3":"St\u00fcck"}...] -->
                <label for="zutaten" id="zutatenLabel">Zutaten</label>
                <div class="zutaten">
                    <div class="search">
                        <input type="text" id="zutaten" placeholder="Zutat suchen" style="text-transform: none;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div id="queryZutaten"></div>
                </div>

                <div class="zutatenTabelle">
                    <label for="zutatenListe">Zutatenlisten</label>
                    <div id="zutatenListen">
                        Leer
                    </div>
                </div>
                <input type="hidden" name="zutaten" id="zutatenInput" required>

                <label for="anleitung">Anleitung</label>
                <input type="hidden" name="anleitung" id="anleitung" required>
                <div id="editor"></div>

                <label class="upload">
                    <i class="fas fa-upload"></i> Bilder hochladen
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
                                    img.style.width = '100%';
                                    preview.appendChild(img);
                                };
                                reader.readAsDataURL(file);
                            }
                        };
                    </script>
                </label>

                <div id="alreadyUploadedImages">
                </div>

                <br>
                <button type="submit" class="btn green"><?php echo $edit ? 'Bearbeiten' : 'Hinzufügen' ?></button>

                <?php if ($edit) { ?>
                    <div class="btn red" onclick="if (confirm('Möchtest du das Rezept \'\'<?= $name ?>\'\' wirklich löschen?')) {
                        // remove confirmation when leaving the page
                        window.onbeforeunload = function(e) {
                            return null;
                        };
                        window.location.href = 'deleteRezept.php?id=<?= $rezeptID ?>'; }">Rezept löschen</div>
                <?php } ?>

            </form>
            <!-- QuillJS -->
            <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
            <script>

                //Json
                let zutatenListe = [];

                <?php if ($edit) { ?>
                zutatenListe = <?php echo json_encode($zutaten) ?>;
                updateZutatenListe();
                <?php } ?>

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

                //Wenn bearbeiten, dann füge die anleitung ein
                <?php if ($edit) { ?>
                quill.root.innerHTML = '<?php echo $anleitung ?>';
                <?php } ?>


                //add already uploaded images when editing
                <?php if ($edit) { ?>
                fetch('getImages.php?rezept_id=<?= $rezeptID ?>')
                    .then(response => response.json())
                    .then(images => {
                        var alreadyUploadedImages = document.querySelector('#alreadyUploadedImages');

                        alreadyUploadedImages.innerHTML = '';
                        alreadyUploadedImages.style.display = 'grid';
                        alreadyUploadedImages.style.gridTemplateColumns = 'repeat(auto-fill, minmax(150px, 1fr)';
                        alreadyUploadedImages.style.gap = '10px';
                        alreadyUploadedImages.style.marginTop = '10px';
                        alreadyUploadedImages.style.background = 'var(--secondaryBackground)';
                        alreadyUploadedImages.style.borderRadius = '20px';
                        alreadyUploadedImages.style.padding = '10px';
                        alreadyUploadedImages.style.border = '2px solid var(--selected_highlight)';

                        for (var image of images) {
                            var img = document.createElement('div');
                            img.style.height = '150px';
                            img.style.backgroundSize = 'contain';
                            img.style.backgroundPosition = 'center';
                            img.style.backgroundRepeat = 'no-repeat';
                            img.style.backgroundImage = 'url(uploads/' + image.Image + ')';
                            img.onclick = function() {
                                if (confirm('Möchtest du das Bild wirklich löschen?')) {
                                    fetch('getImages.php?rezept_id=<?= $rezeptID ?>&image=' + image.Image + '&delete=true')
                                        .then(response => {
                                            if (response.ok) {
                                                img.remove();
                                            }
                                        });
                                }
                            };
                            alreadyUploadedImages.appendChild(img);
                        }
                    });
                <?php } ?>


                document.querySelector('form').onsubmit = function() {
                    var anleitung = document.querySelector('input[name=anleitung]');
                    anleitung.value = quill.root.innerHTML;

                    var zutaten = document.querySelector('#zutatenInput');

                    // Entferne name und unit aus zutatenListe
                    zutatenListe = zutatenListe.map(z => {
                        return {
                            ID: z.ID,
                            Menge: z.Menge,
                            table: z.Tabelle,
                            additionalInfo: z.AdditionalInfo
                        };
                    });

                    zutaten.value = JSON.stringify(zutatenListe);

                    let extraCustomInfosElement = document.querySelector('#extraCustomInfosInput');
                    extraCustomInfosElement.value = JSON.stringify(extraCustomInfos);

                    // remove confirmation when leaving the page
                    window.onbeforeunload = function(e) {
                        return null;
                    };
                };


                document.querySelector('#zutaten').oninput = function() {
                    search();
                };

                function search(){
                    var input = document.querySelector('#zutaten').value;
                    var zutatenListe = document.querySelector('#queryZutaten');

                    if (input.length < 1) {
                        zutatenListe.innerHTML = '';
                        return;
                    }

                    fetch('getZutaten.php?name=' + input)
                        .then(response => response.json())
                        .then(zutaten => {
                            zutatenListe.innerHTML = '';

                            for (var zutat of zutaten) {
                                //div mit bild name und einheit
                                var div = document.createElement('div');
                                div.innerHTML = `
                                            <img src="ingredientIcons/${zutat.Image}" alt="${zutat.Name}">
                                            <span>${zutat.Name}</span>
                                            <span>(${zutat.unit})</span>
                                        `;

                                const id = zutat.ID;
                                div.onclick = function() {
                                    addZutatToRezept(id);
                                };

                                //fade in
                                $(div).hide().appendTo(zutatenListe).fadeIn(150);
                            }

                            if (input.length < 2) {
                                return;
                            }

                            //return wenns auf * endet
                            if (input.endsWith('*')) {
                                return;
                            }

                            //knopf um es in die datenbank mit aufzunehmen
                            var div = document.createElement('div');
                            div.classList.add('addZutat');
                            div.innerHTML = `
                                        <img src="ingredientIcons/plus.svg" alt="Hinzufügen">
                                        <span>${input}</span>
                                        <span>(Neue Zutat)</span>
                                    `;

                            div.onclick = function() {
                                //open a prompt to add the zutat with the unit
                                //wenn rückmeldung dann search();
                                var zutat = prompt('Einheit der Zutat');
                                if (zutat === null || zutat === '') {
                                    return;
                                }
                                fetch('addZutat.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'name=' + input + '&unit=' + zutat
                                }).then(response => {
                                    if (response.ok) {
                                        search();
                                    }
                                });
                            };

                            //fade in
                            $(div).hide().appendTo(zutatenListe).fadeIn(150);


                            //scroll window to top of the zutatenListe
                            document.getElementById("zutatenLabel").scrollIntoView({behavior: "smooth", block: "start"});

                        });

                }

                // Function to add a zutat to the rezept
                function addZutatToRezept(zutatID) {

                    //clear the search bar
                    document.querySelector('#zutaten').value = '';
                    search();

                    fetch('getZutaten.php?id=' + zutatID)
                        .then(response => response.json())
                        .then(zutat => {

                            let zutatName = zutat[0].Name;
                            let zutatUnit = zutat[0].unit;

                            //wenn zutatunit Stück ist, dann soll die Menge um 1 verändert werden, bei ml um 10, bei g um 50 und bei l um 0.1
                            let zutatMenge = 1;
                            if (zutatUnit === 'Stück') {
                                zutatMenge = 1;
                            } else if (zutatUnit === 'ml') {
                                zutatMenge = 10;
                            } else if (zutatUnit === 'g') {
                                zutatMenge = 50;
                            } else if (zutatUnit === 'l') {
                                zutatMenge = 0.1;
                            }

                            const background = document.createElement('div');
                            background.classList.add('background');
                            background.id = 'addBackground';
                            background.onclick = function(e) {
                                if (e.target === background)
                                    background.remove();
                            };
                            document.body.appendChild(background);

                            //datalist mit allen tabellen die es gibt ohne dopplungen
                            var tables = [];
                            for (var zutatT of zutatenListe) {
                                if (zutatT.Tabelle !== '' && !tables.includes(zutatT.Tabelle)) {
                                    tables.push(zutatT.Tabelle);
                                }
                            }
                            var datalist = document.createElement('datalist');
                            datalist.id = 'tables';
                            for (var table of tables) {
                                var option = document.createElement('option');
                                option.value = table;
                                datalist.appendChild(option);
                            }
                            background.appendChild(datalist);

                            const popup = document.createElement('div');
                            popup.classList.add('popup');
                            popup.innerHTML = `
                        <h2>${zutatName}
                            <img src="ingredientIcons/${zutat[0].Image}" alt="${zutatName}" style="height: 30px; display: inline-block">
                        </h2>
                        <div class="numbers">
                            <div class="number-input" style="width: 100%; height: 45px">
                                <button class="down" onclick="this.nextElementSibling.value = parseFloat(this.nextElementSibling.value) - ${zutatMenge}">-</button>
                                <input class="quantity" min="0" name="quantity" value="${zutatMenge}" step="any" type="number">
                                <button class="up" onclick="this.previousElementSibling.value = parseFloat(this.previousElementSibling.value) + ${zutatMenge}">+</button>
                            </div>
                            <input type="text" class="extraCustomTabellenInput" placeholder="Einheit" id="zutatEinheit" value="${zutatUnit}" disabled>
                        </div>
                        <input type="text" class="extraCustomTabellenInput" placeholder="Zusätzliche Infos (optional)" id="zutatAdditionalInfo" style="text-transform: none">
                        <input type="text" class="extraCustomTabellenInput" placeholder="Tabelle (optional)" id="zutatTabelle" list="tables" style="text-transform: none">
                        <button class="btn green" id="addZutatButton">Hinzufügen</button>
                    `;
                            background.appendChild(popup);

                            document.querySelector('#addZutatButton').onclick = function() {
                                addZutatToList(zutatID, zutatName, document.querySelector('.quantity').value, zutatUnit, document.querySelector('#zutatAdditionalInfo').value, document.querySelector('#zutatTabelle').value, zutat[0].Image);
                                background.remove();
                            };

                            document.body.appendChild(background);
                        });
                }

                function addZutatToList(zutatID, name, menge, einheit, additionalInfo, tabelle, image) {
                    //add the zutat to the list
                    zutatenListe.push({
                        ID: zutatID,
                        Name: name,
                        Image: image,
                        Menge: menge,
                        Einheit: einheit,
                        AdditionalInfo: additionalInfo,
                        Tabelle: tabelle
                    });
                    updateZutatenListe();
                }

                function deleteZutatFromList(index) {
                    zutatenListe.splice(index, 1);
                    updateZutatenListe();
                }

                function updateZutatenListe() {
                    console.log(zutatenListe);

                    var zutatenListen = document.querySelector('#zutatenListen');
                    zutatenListen.innerHTML = '';

                    // Globales Tabellen-Element erstellen
                    var table = document.createElement('table');

                    table.innerHTML = `
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Menge</th>
                                        <th>Zusätzliche Infos</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody> <!-- Leeres tbody für Einträge -->
                            `;

                    zutatenListen.appendChild(table);

                    var tbody = table.querySelector('tbody'); // Referenz auf das tbody der globalen Tabelle

                    // Objekt zum Verfolgen der bereits erstellten benutzerdefinierten Tabellen
                    var customTables = {};

                    for (var i = 0; i < zutatenListe.length; i++) {
                        var zutat = zutatenListe[i];

                        var tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${zutat.Name}</td>
                            <td>${zutat.Menge} ${zutat.Einheit}</td>
                            <td>${zutat.AdditionalInfo}</td>
                            <td style="display: flex; gap: 10px">
                                <div class="editButton" onclick="editZutatFromList(${i})"><i class="fas fa-edit"></i></div>
                            </td>
                        `;

                        if (zutat.Tabelle !== '') {
                            // Überprüfen, ob eine benutzerdefinierte Tabelle bereits existiert
                            if (!customTables[zutat.Tabelle]) {
                                // Neue benutzerdefinierte Tabelle erstellen
                                var customTableContainer = document.createElement('div');
                                customTableContainer.style.marginTop = '20px';
                                customTableContainer.innerHTML = `<h3>${zutat.Tabelle}</h3>`; // Tabelle mit Titel
                                var customTable = document.createElement('table');
                                customTable.innerHTML = `
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Menge</th>
                                            <th>Zusätzliche Infos</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody> <!-- Leeres tbody für Einträge -->
                                `;
                                customTableContainer.appendChild(customTable);
                                zutatenListen.appendChild(customTableContainer);

                                // Speichern der Tabelle im customTables-Objekt
                                customTables[zutat.Tabelle] = customTable.querySelector('tbody');
                            }

                            // Zeile zur entsprechenden benutzerdefinierten Tabelle hinzufügen
                            customTables[zutat.Tabelle].appendChild(tr);
                        } else {
                            // Zeile zur globalen Tabelle hinzufügen
                            tbody.appendChild(tr);
                        }
                    }
                }

                function editZutatFromList(index) {
                    var zutat = zutatenListe[index];

                    const background = document.createElement('div');
                    background.classList.add('background');
                    background.id = 'addBackground';
                    background.onclick = function(e) {
                        if (e.target === background)
                            background.remove();
                    };
                    document.body.appendChild(background);

                    //datalist mit allen tabellen die es gibt ohne dopplungen
                    var tables = [];
                    for (var zutatT of zutatenListe) {
                        if (zutatT.Tabelle !== '' && !tables.includes(zutatT.Tabelle)) {
                            tables.push(zutatT.Tabelle);
                        }
                    }
                    var datalist = document.createElement('datalist');
                    datalist.id = 'tables';
                    for (var table of tables) {
                        var option = document.createElement('option');
                        option.value = table;
                        datalist.appendChild(option);
                    }
                    background.appendChild(datalist);

                    const popup = document.createElement('div');
                    popup.classList.add('popup');
                    popup.innerHTML = `
                        <h2>${zutat.Name}
                            <img src="ingredientIcons/${zutat.Image}" alt="${zutat.Name}" style="height: 30px; display: inline-block">
                        </h2>
                        <div class="numbers">
                            <div class="number-input" style="width: 100%; height: 45px">
                                <button class="down" onclick="this.nextElementSibling.value = parseFloat(this.nextElementSibling.value) - ${zutat.Menge}">-</button>
                                <input class="quantity" min="0" name="quantity" value="${zutat.Menge}" step="any" type="number">
                                <button class="up" onclick="this.previousElementSibling.value = parseFloat(this.previousElementSibling.value) + ${zutat.Menge}">+</button>
                            </div>
                            <input type="text" class="extraCustomTabellenInput" placeholder="Einheit" id="zutatEinheit" value="${zutat.Einheit}" disabled style="text-transform: none">
                        </div>
                        <input type="text" class="extraCustomTabellenInput" placeholder="Zusätzliche Infos (optional)" id="zutatAdditionalInfo" value="${zutat.AdditionalInfo}" style="text-transform: none">
                        <input type="text" class="extraCustomTabellenInput" placeholder="Tabelle (optional)" id="zutatTabelle" list="tables" value="${zutat.Tabelle}" style="text-transform: none">
                        <button class="btn green" id="addZutatButton">Aktualisieren</button>
                        <button class="btn red" id="deleteZutatButton">Löschen</button>
                    `;
                    background.appendChild(popup);

                    document.querySelector('#addZutatButton').onclick = function() {
                        zutatenListe[index].Menge = document.querySelector('.quantity').value;
                        zutatenListe[index].AdditionalInfo = document.querySelector('#zutatAdditionalInfo').value;
                        zutatenListe[index].Tabelle = document.querySelector('#zutatTabelle').value;
                        background.remove();
                        updateZutatenListe();
                    };

                    document.querySelector('#deleteZutatButton').onclick = function() {
                        if (confirm('Möchtest du ' + zutatenListe[index].Name + ' wirklich löschen?')) {
                            deleteZutatFromList(index);
                            background.remove();
                        }
                    };

                    document.body.appendChild(background);
                }

                // confirm that the user wants to leave the page
                window.onbeforeunload = function(e) {
                    e.returnValue = 'Möchtest du die Seite wirklich verlassen?';
                    return 'Möchtest du die Seite wirklich verlassen?';
                };

            </script>
        </div>
    </div>
</body>
<script src="script.js"></script>
</html>

