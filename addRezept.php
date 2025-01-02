<?php
include 'shared/global.php';
global $pdo;

$edit = isset($_GET['rezept']);
$rezeptID = $edit ? $_GET['rezept'] : null;

//api.php?task=getRezept&id=$rezeptID
$rezept = $edit ? json_decode(file_get_contents("http://localhost/Kochbuch/api.php?task=getRezept&id=$rezeptID"), true)[0] : null;

$name = $edit ? $rezept['Name'] : '';
$dauer = $edit ? $rezept['Zeit'] : 5;
$portionen = $edit ? $rezept['Portionen'] : 4;

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
</head>
<body>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

            <h1>Rezept <?php echo $edit ? 'bearbeiten' : 'hinzufügen' ?></h1>
            <form action="api.php<?php echo $edit ? '?edit=true&rezept=' . $rezeptID : '' ?>
" method="post" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required placeholder="Rezeptname" style="text-transform: none" value="<?php echo $edit ? $name : '' ?>">

                <br>

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
                        echo "<option value='" . $row['ID'] . "' " . ($edit && $rezept['Kategorie_ID'] == $row['ID'] ? 'selected' : '') . ">&nbsp" . $row['Name'] . " (" . $row['Anzahl'] . ")</option>";
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






                <button type="submit" class="btn green"><?php echo $edit ? 'Speichern' : 'Hinzufügen' ?></button>
            </form>
        </div>
    </div>
</body>
<script src="script.js"></script>
</html>

