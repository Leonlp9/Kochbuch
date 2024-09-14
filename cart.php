<?php

require_once 'shared/global.php';
global $pdo;

//create a shopping table if it does not exist
$einkaufsliste = "CREATE TABLE IF NOT EXISTS einkaufsliste (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Zutat_ID INT NOT NULL,
    Item VARCHAR(255),
    Menge INT NOT NULL,
    Einheit VARCHAR(255) NOT NULL
)";
$pdo->exec($einkaufsliste);

//http://localhost:63342/Kochbuch/cart.php?rezept=26
//add all ingredients from a recipe to the shopping list

if (isset($_GET['rezept'])) {
    $rezeptID = $_GET['rezept'];

    $stmt = $pdo->prepare("SELECT * FROM rezepte WHERE ID = ?");
    $stmt->execute([$rezeptID]);
    $rezept = $stmt->fetch();
    $zutaten = json_decode($rezept['Zutaten_JSON'], true);

    foreach ($zutaten as $zutat) {
        $stmt = $pdo->prepare("SELECT * FROM zutaten WHERE ID = ?");
        $stmt->execute([$zutat['ID']]);
        $zutatDB = $stmt->fetch();

        $menge = $zutat['Menge'];
        $einheit = $zutatDB['unit'];
        $item = $zutatDB['Name'];

        //wenn schon vorhanden, dann erhöhe die Menge
        $stmt = $pdo->prepare("SELECT * FROM einkaufsliste WHERE Zutat_ID = ?");
        $stmt->execute([$zutat['ID']]);
        $itemDB = $stmt->fetch();

        if ($itemDB) {
            $menge += $itemDB['Menge'];
            $stmt = $pdo->prepare("UPDATE einkaufsliste SET Menge = ? WHERE Zutat_ID = ?");
            $stmt->execute([$menge, $zutat['ID']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO einkaufsliste (Zutat_ID, Item, Menge, Einheit) VALUES (?, ?, ?, ?)");
            $stmt->execute([$zutat['ID'], $item, $menge, $einheit]);
        }
    }

    header("Location: cart.php");
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Einkaufsliste</title>

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

        .cartAdd {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            font-size: 20px;
            background-color: var(--nonSelected);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .cartAdd {
                bottom: 60px;
                right: 10px;
            }
        }

        .cart {
            overflow-x: scroll;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: var(--background);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

    </style>
</head>
<body>
    <div class="cartAdd">
        <i class="fas fa-shopping-cart"></i>
    </div>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">
            <h1>Einkaufsliste</h1>

            <div class="cart">
            <table>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Menge</th>
                    <th>Einheit</th>
                </tr>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM einkaufsliste");
                $stmt->execute();
                $einkaufsliste = $stmt->fetchAll();

                foreach ($einkaufsliste as $item) {

                    $stmt = $pdo->prepare("SELECT * FROM zutaten WHERE ID = ?");
                    $stmt->execute([$item['Zutat_ID']]);
                    $zutat = $stmt->fetch();

                    echo "<tr>";

                    //check if image exists
                    if (file_exists("ingredientIcons/" . $zutat['Image'])) {
                        echo "<td><img src='ingredientIcons/" . $zutat['Image'] . "' style='width: 50px; height: 50px;'></td>";
                    } else {
                        echo "<td><img src='ingredientIcons/default.svg' style='width: 50px; height: 50px;'></td>";
                    }

                    echo "<td>" . $item['Item'] . "</td>";
                    echo "<td>" . $item['Menge'] . "</td>";
                    echo "<td>" . $item['Einheit'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            </div>

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
