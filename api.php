<?php

header('Content-Type: application/json');

include 'shared/global.php';
global $pdo;

if (!isset($_GET['task'])) {
    echo json_encode(['error' => 'No task provided']);
    die();
}


$task = $_GET['task'];

switch ($task) {
    case 'getImages':
        if (isset($_GET['rezept_id'])) {
            $id = $_GET['rezept_id'];
            $sql = $pdo->prepare('SELECT * FROM bilder WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $bilder = $sql->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to get only associative keys

            foreach ($bilder as &$bild) {
                $bild['Image'] = 'uploads/' . $bild['Image'];
            }

            echo json_encode($bilder);
            die();
        } else {
            echo json_encode(['error' => 'No rezept_id provided']);
            die();
        }
    case 'deleteImage':
        if (isset($_GET['rezept_id']) && isset($_GET['image'])) {
            $rezept_id = $_GET['rezept_id'];
            $image = $_GET['image'];

            //get image from db by id
            $sql = $pdo->prepare('SELECT * FROM bilder WHERE Rezept_ID = :rezept_id AND ID = :image');
            $sql->bindValue(':rezept_id', $rezept_id);
            $sql->bindValue(':image', $image);
            $sql->execute();
            $bild = $sql->fetch(PDO::FETCH_ASSOC);

            //delete image from uploads
            unlink("uploads/" . $bild['Image']);

            $sql = $pdo->prepare('DELETE FROM bilder WHERE Rezept_ID = :rezept_id AND ID = :image');
            $sql->bindValue(':rezept_id', $rezept_id);
            $sql->bindValue(':image', $image);
            $sql->execute();

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'No rezept_id or image provided']);
            die();
        }
    case 'deleteRezept':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            $stmt = $pdo->prepare("DELETE FROM rezepte WHERE ID = ?");
            $stmt->execute([$id]);

            //lösche alle Bilder
            $stmt = $pdo->prepare("SELECT * FROM bilder WHERE Rezept_ID = ?");
            $stmt->execute([$id]);
            $bilder = $stmt->fetchAll();
            foreach ($bilder as $bild) {
                unlink("uploads/" . $bild['Image']);
            }

            $stmt = $pdo->prepare("DELETE FROM bilder WHERE Rezept_ID = ?");
            $stmt->execute([$id]);

            //lösche alle Bewertungen
            $stmt = $pdo->prepare("DELETE FROM bewertungen WHERE Rezept_ID = ?");
            $stmt->execute([$id]);

            //lösche alle anmerkungen
            $stmt = $pdo->prepare("DELETE FROM anmerkungen WHERE Rezept_ID = ?");
            $stmt->execute([$id]);

            //lösche alle kalendereinträge
            $stmt = $pdo->prepare("DELETE FROM kalender WHERE Rezept_ID = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
            header("Location: ./");
            die();
        } else {
            echo json_encode(['error' => 'No id provided']);
            die();
        }
    case 'getZutaten':
        if (isset($_GET['name'])) {
            $name = $_GET['name'];
        } else {
            $name = '';
        }

        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        } else {
            $limit = 20;
        }

        // Wenn der Name mit * endet, dann alle anzeigen und das * entfernen
        if (substr($name, -1) == '*') {
            $name = substr($name, 0, -1);
            $limit = 10000;
        }

        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = $pdo->prepare('SELECT * FROM zutaten WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $zutaten = $sql->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to get only associative keys

            // Check if the image exists in ingredientIcons, if not set it to default.svg
            foreach ($zutaten as $key => $zutat) {
                if (!file_exists('ingredientIcons/' . $zutat['Image'])) {
                    $zutaten[$key]['Image'] = 'default.svg';
                }else{
                    $zutaten[$key]['Image'] = 'ingredientIcons/' . $zutat['Image'];
                }
            }

            echo json_encode($zutaten);
            die();
        }

        // SQL-Abfrage anpassen, um die Sortierung zu berücksichtigen
        $sql = $pdo->prepare('
            SELECT * 
            FROM zutaten 
            WHERE Name LIKE :name 
            ORDER BY 
                CASE 
                    WHEN Name LIKE :prefix THEN 0 
                    ELSE 1 
                END, 
                Name 
            LIMIT :limit
        ');

        // Platzhalter für Suchmuster
        $prefix = $name . '%';
        $fullText = '%' . $name . '%';

        $sql->bindValue(':name', $fullText);
        $sql->bindValue(':prefix', $prefix);
        $sql->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sql->execute();
        $zutaten = $sql->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to get only associative keys

        // Check if the image exists in ingredientIcons, if not set it to default.svg
        foreach ($zutaten as $key => $zutat) {
            if (!file_exists('ingredientIcons/' . $zutat['Image'])) {
                $zutaten[$key]['Image'] = 'ingredientIcons/default.svg';
            }else{
                $zutaten[$key]['Image'] = 'ingredientIcons/' . $zutat['Image'];
            }
        }

        echo json_encode($zutaten);
        die();
    case 'getRezept':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = $pdo->prepare('SELECT * FROM rezepte WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $rezepte = $sql->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to get only associative keys

            if (count($rezepte) == 0) {
                echo json_encode(['error' => 'No recipe found']);
                die();
            }

            //bool ob zutaten mitgeliefert werden sollen
            if (isset($_GET['zutaten'])) {
                $zutaten = json_decode($rezepte[0]['Zutaten_JSON'], true);
                $zutaten_array = [];
                foreach ($zutaten as $zutat) {
                    $sql = $pdo->prepare('SELECT Name, unit, Image FROM zutaten WHERE ID = :id');
                    $sql->bindValue(':id', $zutat['ID']);
                    $sql->execute();
                    $zutat_name = $sql->fetch(PDO::FETCH_ASSOC);
                    $zutaten_array[] = [
                        'ID' => $zutat['ID'],
                        'Menge' => $zutat['Menge'],
                        'unit' => $zutat_name['unit'],
                        'Name' => $zutat_name['Name'],
                        'Image' => $zutat_name['Image'],
                        'additionalInfo' => $zutat['additionalInfo'],
                        'table' => $zutat['table']
                    ];
                }
                $rezepte[0]['Zutaten_JSON'] = $zutaten_array;

                //füge ingredientIcons hinzu
                foreach ($rezepte[0]['Zutaten_JSON'] as &$zutat) {
                    if (!file_exists('ingredientIcons/' . $zutat['Image'])) {
                        $zutat['Image'] = 'ingredientIcons/default.svg';
                    } else {
                        $zutat['Image'] = 'ingredientIcons/' . $zutat['Image'];
                    }
                }

                $zutatenTables = [""];
                foreach ($zutaten_array as &$zutat) {
                    if (!in_array($zutat['table'], $zutatenTables)) {
                        $zutatenTables[] = $zutat['table'];
                    }
                }
                $rezepte[0]['ZutatenTables'] = $zutatenTables;
            } else {
                //lösche zutaten json aus dem array
                unset($rezepte[0]['Zutaten_JSON']);
            }

            // Kateogrie hinzufügen
            $sql = $pdo->prepare('SELECT Name, ColorHex FROM kategorien WHERE ID = :id');
            $sql->bindValue(':id', $rezepte[0]['Kategorie_ID']);
            $sql->execute();
            $kategorie = $sql->fetch(PDO::FETCH_ASSOC);
            $rezepte[0]['Kategorie'] = $kategorie['Name'];
            $rezepte[0]['KategorieColor'] = $kategorie['ColorHex'];

            // Anmerkungen hinzufügen
            $sql = $pdo->prepare('SELECT * FROM anmerkungen WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $anmerkungen = $sql->fetchAll(PDO::FETCH_ASSOC);
            $rezepte[0]['Anmerkungen'] = $anmerkungen;

            // Bewertungen hinzufügen
            $sql = $pdo->prepare('SELECT * FROM bewertungen WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $bewertungen = $sql->fetchAll(PDO::FETCH_ASSOC);
            $rezepte[0]['Bewertungen'] = $bewertungen;

            // Den Bewertungen die Bilder hinzufügen der Bewerter hinzufügen
            foreach ($rezepte[0]['Bewertungen'] as &$bewertung) {
                $bewertung['Image'] = 'https://api.dicebear.com/9.x/bottts-neutral/svg?seed=' . $bewertung['Name'];
            }

            // Kalender hinzufügen
            $sql = $pdo->prepare('SELECT * FROM kalender WHERE Rezept_ID = :id AND Datum >= CURDATE()');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $kalender = $sql->fetchAll(PDO::FETCH_ASSOC);
            $rezepte[0]['Kalender'] = $kalender;;

            // Images hinzufügen
            $sql = $pdo->prepare('SELECT * FROM bilder WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $bilder = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($bilder as &$bild) {
                $bild['Image'] = 'uploads/' . $bild['Image'];
            }
            $rezepte[0]['Bilder'] = $bilder;


            //current timestamp 1736367121
            $current_timestamp = time();

            //update last_visit timestamp in rezepte
            $sql = $pdo->prepare('UPDATE rezepte SET last_visit = :current_timestamp WHERE ID = :id');
            $sql->bindValue(':current_timestamp', $current_timestamp);
            $sql->bindValue(':id', $id);
            $sql->execute();

            $kitchenAppliances = json_decode($rezepte[0]['KitchenAppliances'] != null && $rezepte[0]['KitchenAppliances'] != "" ? $rezepte[0]['KitchenAppliances'] : "[]");
            $kitchenAppliances_array = [];
            foreach ($kitchenAppliances as $appliance) {
                $sql = $pdo->prepare('SELECT Name, Image FROM kitchenappliances WHERE ID = :id');
                $sql->bindValue(':id', $appliance);
                $sql->execute();
                $appliance_name = $sql->fetch(PDO::FETCH_ASSOC);
                $kitchenAppliances_array[] = [
                    'ID' => $appliance,
                    'Name' => $appliance_name['Name'],
                    'Image' => $appliance_name['Image']
                ];
            }
            $rezepte[0]['KitchenAppliances'] = json_encode($kitchenAppliances_array);


            echo json_encode($rezepte);
            die();
        } else {
            echo json_encode(['error' => 'No id provided']);
            die();
        }
    case "addEvaluation":
        if (isset($_GET['rezept']) && isset($_GET['rating']) && isset($_GET['name']) && isset($_GET['text'])) {
            $rezept = $_GET['rezept'];
            $bewertung = $_GET['rating'];
            $name = $_GET['name'];
            $text = $_GET['text'];

            $sql = $pdo->prepare('INSERT INTO bewertungen (Rezept_ID, Bewertung, Name, Text) VALUES (:rezept, :bewertung, :name, :text)');
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();
            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "editEvaluation":
        if (isset($_GET['rezept']) && isset($_GET['rating']) && isset($_GET['name']) && isset($_GET['text'])) {
            $rezept = $_GET['rezept'];
            $bewertung = $_GET['rating'];
            $name = $_GET['name'];
            $text = $_GET['text'];

            $sql = $pdo->prepare('UPDATE bewertungen SET Bewertung = :bewertung, Name = :name, Text = :text WHERE ID = :rezept');
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();
            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "deleteEvaluation":
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = $pdo->prepare('DELETE FROM bewertungen WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "search":

        if (!isset($_GET['search'])) {
            echo json_encode(['error' => 'No search term provided']);
            die();
        }

        $search = $_GET['search'];
        $order = (isset($_GET['order'])) ? $_GET['order'] : "Name";
        $zeit = (isset($_GET['zeit'])) ? $_GET['zeit'] : "4";
        $kategorie = (isset($_GET['kategorie'])) ? $_GET['kategorie'] : "*";
        $random = (isset($_GET['random'])) ? $_GET['random'] : false;
        $neueste = (isset($_GET['neueste'])) ? $_GET['neueste'] : false;
        $last_visit = (isset($_GET['last_visit'])) ? $_GET['last_visit'] : false;

        $join = "LEFT JOIN bilder ON rezepte.ID = bilder.Rezept_ID";
        $where = "WHERE rezepte.Name LIKE '%$search%'";
        if ($kategorie != "*") {
            $where .= " AND rezepte.Kategorie_ID = $kategorie";
        }

        switch ($zeit) {
            case "0":
                $where .= " AND rezepte.Zeit <= 15";
                break;
            case "1":
                $where .= " AND rezepte.Zeit > 15 AND rezepte.Zeit <= 30";
                break;
            case "2":
                $where .= " AND rezepte.Zeit > 30 AND rezepte.Zeit <= 60";
                break;
            case "3":
                $where .= " AND rezepte.Zeit > 60";
                break;
        }

        $order = "ORDER BY rezepte.$order";

        $join .= " LEFT JOIN bewertungen ON rezepte.ID = bewertungen.Rezept_ID";

        if ($order == "ORDER BY rezepte.Rating") {
            $order = "ORDER BY AVG(bewertungen.Bewertung) DESC";
        }

        if ($random) {
            $order = "ORDER BY RAND() LIMIT 8";
        }

        if ($neueste) {
            $order = "ORDER BY rezepte.ID DESC LIMIT 8";
        }

        if ($last_visit) {
            $order = "ORDER BY rezepte.last_visit DESC LIMIT 8";
        }

        $rezepte = $pdo->query("
            SELECT
                rezepte.ID as rezepte_ID,
                rezepte.Name as Name,
                MIN(bilder.Image) as Image,
                rezepte.Zeit,
                AVG(bewertungen.Bewertung) as Durchschnittsbewertung
            FROM
                rezepte
            $join
            $where
            GROUP BY
                rezepte.ID
            $order
        ")->fetchAll(PDO::FETCH_ASSOC);

        $response = [];

        foreach ($rezepte as $rezept) {
            $image = (!file_exists("uploads/" . $rezept['Image']) || $rezept['Image'] == null) ? "ingredientIcons/default.svg" : "uploads/" . $rezept['Image'];
            $zeit = $rezept['Zeit'];
            $stunden = floor($zeit / 60);
            $minuten = $zeit % 60;
            $zeitString = ($stunden > 0 ? $stunden . "h " : "") . ($minuten > 0 ? $minuten . "min" : "");

            $rating = $pdo->query("SELECT AVG(Bewertung) as Rating, COUNT(Bewertung) as Anzahl FROM bewertungen WHERE Rezept_ID = " . $rezept['rezepte_ID'])->fetch();
            $count = $rating['Anzahl'];
            $rating = $rating['Rating'] ?? 0;

            $response[] = [
                'rezepte_ID' => $rezept['rezepte_ID'],
                'Name' => $rezept['Name'],
                'Image' => $image,
                'Zeit' => $zeitString,
                'Rating' => (float)$rating, // Konvertiere Rating in float
                'RatingCount' => (int)$count // Konvertiere RatingCount in int
            ];
        }

        echo json_encode($response);
        die();
    case "getKategorien":
        if (isset($_GET['includeCount']) && $_GET['includeCount'] == 'true') {
            $kategorien = $pdo->query("SELECT k.ID, k.Name, k.ColorHex, COUNT(rk.Kategorie_ID) AS usage_count FROM kategorien k LEFT JOIN rezepte rk ON k.ID = rk.Kategorie_ID GROUP BY k.ID ORDER BY k.Name")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $kategorien = $pdo->query("SELECT ID, Name, ColorHex FROM kategorien ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($kategorien);
        die();
    case "getFilterprofile":
        $filterprofile = $pdo->query("SELECT * FROM filterprofile")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filterprofile as $key => $filter) {
            $filterprofile[$key]['Filter'] = json_decode($filter['Filter'], true);
        }

        foreach ($filterprofile as &$filter) {
            $filter['Image'] = 'https://api.dicebear.com/9.x/bottts-neutral/svg?seed=' . $filter['Name'];
        }


        echo json_encode($filterprofile);
        die();
    case "getAnmerkungen":
        if (isset($_GET['rezept'])) {
            $rezept = $_GET['rezept'];
            $anmerkungen = $pdo->query("SELECT * FROM anmerkungen WHERE Rezept_ID = $rezept")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($anmerkungen);
            die();
        } else {
            echo json_encode(['error' => 'No rezept provided']);
            die();
        }
    case "addZutat":
        if (isset($_GET['name']) && isset($_GET['unit'])) {
            $name = $_GET['name'];
            //$image Lowercase name + .svg
            $image = strtolower($name) . '.svg';
            $unit = $_GET['unit'];

            $sql = $pdo->prepare('INSERT INTO zutaten (Name, Image, Unit) VALUES (:name, :image, :unit)');
            $sql->bindValue(':name', $name);
            $sql->bindValue(':image', $image);
            $sql->bindValue(':unit', $unit);
            $sql->execute();

            $id = $pdo->lastInsertId(); // Get the ID of the newly inserted ingredient

            echo json_encode(['success' => true, 'ID' => $id]); // Include the ID in the response
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
        }
        die();
    case "anmerkung":
        if (isset($_GET['rezept']) && isset($_GET['text'])) {
            $rezept = $_GET['rezept'];
            $text = $_GET['text'];

            //check if anmerkung already exists for this rezept else insert
            $sql = $pdo->prepare('SELECT * FROM anmerkungen WHERE Rezept_ID = :rezept');
            $sql->bindValue(':rezept', $rezept);
            $sql->execute();
            $anmerkung = $sql->fetch();

            if ($anmerkung) {
                $sql = $pdo->prepare('UPDATE anmerkungen SET Anmerkung = :text WHERE Rezept_ID = :rezept');
                $sql->bindValue(':rezept', $rezept);
                $sql->bindValue(':text', $text);
                $sql->execute();
            } else {
                $sql = $pdo->prepare('INSERT INTO anmerkungen (Rezept_ID, Anmerkung) VALUES (:rezept, :text)');
                $sql->bindValue(':rezept', $rezept);
                $sql->bindValue(':text', $text);
                $sql->execute();
            }

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "getKalender":
        if (isset($_GET['showPast']) && $_GET['showPast'] == 'true') {
            $showPast = '';
        } else {
            $showPast = 'WHERE kalender.Datum >= CURDATE()';
        }

        $kalender = $pdo->query("
            SELECT 
                kalender.ID as Kalender_ID, 
                kalender.Datum, 
                kalender.Rezept_ID, 
                kalender.Text, 
                rezepte.ID, 
                rezepte.Name, 
                MIN(bilder.Image) as Image
            FROM 
                kalender
            LEFT JOIN 
                rezepte ON kalender.Rezept_ID = rezepte.ID
            LEFT JOIN 
                bilder ON rezepte.ID = bilder.Rezept_ID
            $showPast
            GROUP BY 
                kalender.ID, 
                kalender.Datum, 
                kalender.Rezept_ID, 
                kalender.Text, 
                rezepte.ID, 
                rezepte.Name
            ORDER BY 
                Datum ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($kalender as &$kal) {
            if (!file_exists('uploads/' . $kal['Image']) || $kal['Image'] == null) {
                if ($kal['Image'] == null) {
                    $kal['Image'] = null;
                } else {
                    $kal['Image'] = 'ingredientIcons/default.svg';
                }
            } else {
                $kal['Image'] = 'uploads/' . $kal['Image'];
            }
        }

        echo json_encode($kalender);
        die();
    case "addKalender":
        if (isset($_GET['date']) && isset($_GET['info'])) {
            $date = $_GET['date'];
            $rezept = isset($_GET['rezept']) ? $_GET['rezept'] : null;
            $info = $_GET['info'];

            $stmt = $pdo->prepare("INSERT INTO kalender (Datum, Rezept_ID, Text) VALUES (?, ?, ?)");
            $stmt->execute([$date, $rezept, $info]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "deleteKalender":
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM kalender WHERE ID = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "updateKalender":
        if (isset($_GET['id']) && isset($_GET['text'])) {
            $id = $_GET['id'];
            $text = $_GET['text'];

            $stmt = $pdo->prepare("UPDATE kalender SET Text = ? WHERE ID = ?");
            $stmt->execute([$text, $id]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "getEinkaufsliste":
        $einkaufsliste = $pdo->query("
            SELECT 
                einkaufsliste.ID as Einkaufsliste_ID, 
                einkaufsliste.Zutat_ID, 
                einkaufsliste.Menge, 
                einkaufsliste.Einheit, 
                zutaten.ID, 
                zutaten.Name, 
                zutaten.Image
            FROM 
                einkaufsliste
            LEFT JOIN 
                zutaten ON einkaufsliste.Zutat_ID = zutaten.ID
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($einkaufsliste as &$item) {
            $item['Image'] = 'ingredientIcons/' . $item['Image'];

            if (!file_exists($item['Image'])) {
                $item['Image'] = 'ingredientIcons/default.svg';
            }
        }

        echo json_encode($einkaufsliste);
        die();
    case "addEinkaufsliste":
        if (isset($_POST['zutat']) && isset($_POST['menge']) && isset($_POST['einheit'])) {
            $zutat = $_POST['zutat'];
            $menge = $_POST['menge'];
            $einheit = $_POST['einheit'];

            $stmt = $pdo->prepare("SELECT * FROM einkaufsliste WHERE Zutat_ID = ?");
            $stmt->execute([$zutat]);
            $item = $stmt->fetch();

            if ($item) {
                $menge += $item['Menge'];
                $stmt = $pdo->prepare("UPDATE einkaufsliste SET Menge = ? WHERE Zutat_ID = ?");
                $stmt->execute([$menge, $zutat]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO einkaufsliste (Zutat_ID, Menge, Einheit) VALUES (?, ?, ?)");
                $stmt->execute([$zutat, $menge, $einheit]);
            }

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "deleteEinkaufsliste":
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM einkaufsliste WHERE ID = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "export_db":
        if (file_exists("config.ini") && is_readable("config.ini")) {
            // Get username and password from config.ini
            $dbInfo = parse_ini_file("config.ini");

            // Check if username and password keys exist in the parsed array
            if (isset($dbInfo['username']) && isset($dbInfo['password'])) {
                $host = 'localhost';
                $user = $dbInfo['username'];
                $password = $dbInfo['password'];
                $dbname = 'kochbuch';

                // Dynamischer Pfad für mysqldump
                if (stripos(PHP_OS, 'WIN') !== false) {
                    // Windows (lokaler PC mit XAMPP)
                    $mysqldumpPath = 'C:/xampp/mysql/bin/mysqldump';
                } else {
                    // Linux/Unix Server
                    $mysqldumpPath = '/usr/bin/mysqldump';  // typischer Pfad auf einem Server
                }

                // Ordner "backups" erstellen, falls er nicht existiert
                $backupDir = __DIR__ . '/backups';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0777, true);  // Erstellen des Ordners mit entsprechenden Rechten
                }

                // Dateiname für das Backup
                $backupFile = $backupDir . '/' . $dbname . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

                // Shell-Befehl zum Exportieren der Datenbank
                $command = "$mysqldumpPath --user=$user --password=$password --host=$host $dbname > $backupFile";

                // Shell-Befehl ausführen
                system($command);

                // Prüfen, ob die Backup-Datei existiert und an den Benutzer senden
                if (file_exists($backupFile)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
                    header('Content-Length: ' . filesize($backupFile));
                    readfile($backupFile);

                    // Datei löschen, nachdem sie heruntergeladen wurde
                    unlink($backupFile);
                    exit;
                } else {
                    echo "Backup fehlgeschlagen.";
                }
            }
        }
    case "addRezept":
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!isset($_POST['name']) || !isset($_POST['kategorie']) || !isset($_POST['dauer']) || !isset($_POST['portionen']) || !isset($_POST['anleitung']) || !isset($_POST['zutaten']) || !isset($_POST['extraCustomInfos']) || !isset($_POST['kitchenAppliances']) || !isset($_FILES['bilder'])) {
                echo json_encode(['error' => 'Not all parameters provided', 'success' => false, 'post' => $_POST, 'files' => $_FILES]);
                die();
            }

            if (isset($_GET['edit'])) {
                $name = $_POST['name'];
                $kategorie = $_POST['kategorie'];
                $dauer = $_POST['dauer'];
                $portionen = $_POST['portionen'];
                $anleitung = $_POST['anleitung'];
                $zutaten = json_decode($_POST['zutaten']);
                $files = $_FILES['bilder'];
                $optionalInfos = json_decode($_POST['extraCustomInfos']);
                $rezeptID = $_GET['rezept'];
                $kitchenAppliances = json_decode($_POST['kitchenAppliances']);

                $sql = "UPDATE rezepte SET Name = :name, Kategorie_ID = :kategorie, Zeit = :dauer, Portionen = :portionen, Zubereitung = :anleitung, Zutaten_JSON = :zutaten, OptionalInfos = :optionalInfos, KitchenAppliances = :kitchenAppliances WHERE ID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'name' => $name,
                    'kategorie' => $kategorie,
                    'dauer' => $dauer,
                    'portionen' => $portionen,
                    'anleitung' => $anleitung,
                    'zutaten' => json_encode($zutaten),
                    'optionalInfos' => json_encode($optionalInfos),
                    'kitchenAppliances' => json_encode($kitchenAppliances),
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
                            if (!is_dir('uploads')) {
                                mkdir('uploads', 0777, true);
                            }
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

            }else {
                $name = $_POST['name'];
                $kategorie = $_POST['kategorie'];
                $dauer = $_POST['dauer'];
                $portionen = $_POST['portionen'];
                $anleitung = $_POST['anleitung'];
                $zutaten = json_decode($_POST['zutaten']);
                $files = $_FILES['bilder'];
                $optionalInfos = json_decode($_POST['extraCustomInfos']);
                $kitchenAppliances = json_decode($_POST['kitchenAppliances']);

                $sql = "INSERT INTO rezepte (Name, Kategorie_ID, Zeit, Portionen, Zubereitung, Zutaten_JSON, OptionalInfos, KitchenAppliances) VALUES (:name, :kategorie, :dauer, :portionen, :anleitung, :zutaten, :optionalInfos, :kitchenAppliances)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'name' => $name,
                    'kategorie' => $kategorie,
                    'dauer' => $dauer,
                    'portionen' => $portionen,
                    'anleitung' => $anleitung,
                    'zutaten' => json_encode($zutaten),
                    'optionalInfos' => json_encode($optionalInfos),
                    'kitchenAppliances' => json_encode($kitchenAppliances)
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
                            if (!is_dir('uploads')) {
                                mkdir('uploads', 0777, true);
                            }
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
                die();
            }
    }
    case "addKategorie":
        if (isset($_GET['name']) && isset($_GET['color'])) {
            $name = $_GET['name'];
            $color = $_GET['color'];

            $sql = $pdo->prepare('INSERT INTO kategorien (Name, ColorHex) VALUES (:name, :color)');
            $sql->bindValue(':name', $name);
            $sql->bindValue(':color', $color);
            $sql->execute();

            $id = $pdo->lastInsertId(); // Get the ID of the newly inserted category

            echo json_encode(['success' => true, 'ID' => $id]); // Include the ID in the response
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
        }
        die();
    case "editKategorie":
        if (isset($_GET['id']) && isset($_GET['name']) && isset($_GET['color'])) {
            $id = $_GET['id'];
            $name = $_GET['name'];
            $color = $_GET['color'];

            $sql = $pdo->prepare('UPDATE kategorien SET Name = :name, ColorHex = :color WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':color', $color);
            $sql->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
        }
        die();
    case "deleteKategorie":
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = $pdo->prepare('DELETE FROM kategorien WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "addFilterprofile":
        if (isset($_GET['name'])) {
            $name = $_GET['name'];
            $filter = '[]';

            $sql = $pdo->prepare('INSERT INTO filterprofile (Name, Filter) VALUES (:name, :filter)');
            $sql->bindValue(':name', $name);
            $sql->bindValue(':filter', $filter);
            $sql->execute();

            $id = $pdo->lastInsertId(); // Get the ID of the newly inserted filter profile

            echo json_encode(['success' => true, 'ID' => $id]); // Include the ID in the response
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
        }
        die();
    case "getKitchenAppliances":
        $kitchenAppliances = $pdo->query("SELECT * FROM kitchenAppliances")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($kitchenAppliances);
        die();

    default:
        echo json_encode(
            [
                'error' => 'Invalid task',
                'task' => $task,
                'availableTasks' =>
                    [
                        'getImages' =>
                            [
                                "Info" => "Get all images of a recipe",
                                "Parameters" => ["rezept_id" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true]]
                            ],
                        'deleteImage' =>
                            [
                                "Info" => "Delete an image of a recipe",
                                "Parameters" => ["rezept_id" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "image" => ["Info" => "Name of the image", "Type" => "string", "Required" => true]]
                            ],
                        'deleteRezept' =>
                            [
                                "Info" => "Delete a recipe",
                                "Parameters" => ["id" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true]]
                            ],
                        'getZutaten' =>
                            [
                                "Info" => "Get ingredients",
                                "Parameters" => ["name" => ["Info" => "Name of the ingredient", "Type" => "string", "Required" => false],
                                    "limit" => ["Info" => "Limit of ingredients", "Type" => "int", "Required" => false],
                                    "id" => ["Info" => "ID of the ingredient", "Type" => "int", "Required" => false]]
                            ],
                        'getRezept' =>
                            [
                                "Info" => "Get a recipe",
                                "Parameters" => ["id" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "zutaten" => ["Info" => "Get ingredients of the recipe", "Type" => "bool", "Required" => false]]
                            ],
                        'addEvaluation' =>
                            [
                                "Info" => "Add an evaluation",
                                "Parameters" => ["rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "bewertung" => ["Info" => "Rating", "Type" => "int", "Required" => true],
                                    "name" => ["Info" => "Name of the evaluator", "Type" => "string", "Required" => true],
                                    "text" => ["Info" => "Text of the evaluation", "Type" => "string", "Required" => true]]
                            ],
                        'editEvaluation' =>
                            [
                                "Info" => "Edit an evaluation",
                                "Parameters" => ["edit" => ["Info" => "ID of the evaluation", "Type" => "int", "Required" => true],
                                    "rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "bewertung" => ["Info" => "Rating", "Type" => "int", "Required" => true],
                                    "name" => ["Info" => "Name of the evaluator", "Type" => "string", "Required" => true],
                                    "text" => ["Info" => "Text of the evaluation", "Type" => "string", "Required" => true]]
                            ],
                        'deleteEvaluation' =>
                            [
                                "Info" => "Delete an evaluation",
                                "Parameters" => ["id" => ["Info" => "ID of the evaluation", "Type" => "int", "Required" => true]]
                            ],
                        'search' =>
                            [
                                "Info" => "Search for recipes",
                                "Parameters" => ["search" => ["Info" => "Search term", "Type" => "string", "Required" => true],
                                    "order" => ["Info" => "Order by", "Type" => "string", "Required" => false],
                                    "zeit" => ["Info" => "Time", "Type" => "int", "Required" => false],
                                    "kategorie" => ["Info" => "Category", "Type" => "int", "Required" => false],
                                    "random" => ["Info" => "Random recipes", "Type" => "bool", "Required" => false],
                                    "neueste" => ["Info" => "Newest recipes", "Type" => "bool", "Required" => false]]
                            ],
                        'getKategorien' =>
                            [
                                "Info" => "Get all categories",
                                "Parameters" => []
                            ],
                        'getFilterprofile' =>
                            [
                                "Info" => "Get all filter profiles",
                                "Parameters" => []
                            ],
                        'getAnmerkungen' =>
                            [
                                "Info" => "Get all notes of a recipe",
                                "Parameters" => ["rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true]]
                            ],
                        'addZutat' =>
                            [
                                "Info" => "Add an ingredient",
                                "Parameters" => ["name" => ["Info" => "Name of the ingredient", "Type" => "string", "Required" => true],
                                    "unit" => ["Info" => "Unit of the ingredient", "Type" => "string", "Required" => true]]
                            ],
                        'anmerkung' =>
                            [
                                "Info" => "Add a note to a recipe",
                                "Parameters" => ["rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "text" => ["Info" => "Text of the note", "Type" => "string", "Required" => true]]
                            ],
                        'addBewertung' =>
                            [
                                "Info" => "Add an evaluation",
                                "Parameters" => ["rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "bewertung" => ["Info" => "Rating", "Type" => "int", "Required" => true],
                                    "name" => ["Info" => "Name of the evaluator", "Type" => "string", "Required" => true],
                                    "text" => ["Info" => "Text of the evaluation", "Type" => "string", "Required" => true]]
                            ],
                        'editBewertung' =>
                            [
                                "Info" => "Edit an evaluation",
                                "Parameters" => ["id" => ["Info" => "ID of the evaluation", "Type" => "int", "Required" => true],
                                    "rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "bewertung" => ["Info" => "Rating", "Type" => "int", "Required" => true],
                                    "name" => ["Info" => "Name of the evaluator", "Type" => "string", "Required" => true],
                                    "text" => ["Info" => "Text of the evaluation", "Type" => "string", "Required" => true]]
                            ],
                        'deleteBewertung' =>
                            [
                                "Info" => "Delete an evaluation",
                                "Parameters" => ["id" => ["Info" => "ID of the evaluation", "Type" => "int", "Required" => true]]
                            ],
                        'getKalender' =>
                            [
                                "Info" => "Get all calendar entries",
                                "Parameters" => ["showPast" => ["Info" => "Show past entries", "Type" => "bool", "Required" => false]]
                            ],
                        'addKalender' =>
                            [
                                "Info" => "Add an entry to the calendar",
                                "Parameters" => ["date" => ["Info" => "Date of the entry", "Type" => "string", "Required" => true],
                                    "rezept" => ["Info" => "ID of the recipe", "Type" => "int", "Required" => true],
                                    "info" => ["Info" => "Info of the entry", "Type" => "string", "Required" => true]]
                            ],
                        'deleteKalender' =>
                            [
                                "Info" => "Delete an entry from the calendar",
                                "Parameters" => ["id" => ["Info" => "ID of the entry", "Type" => "int", "Required" => true]]
                            ],
                        'getEinkaufsliste' =>
                            [
                                "Info" => "Get the shopping list",
                                "Parameters" => []
                            ],
                        'addEinkaufsliste' =>
                            [
                                "Info" => "Add an item to the shopping list",
                                "Parameters" => ["zutat" => ["Info" => "ID of the ingredient", "Type" => "int", "Required" => true],
                                    "menge" => ["Info" => "Amount of the ingredient", "Type" => "int", "Required" => true],
                                    "einheit" => ["Info" => "Unit of the ingredient", "Type" => "string", "Required" => true]]
                            ],
                        'deleteEinkaufsliste' =>
                            [
                                "Info" => "Delete an item from the shopping list",
                                "Parameters" => ["id" => ["Info" => "ID of the item", "Type" => "int", "Required" => true]]
                            ],
                        'export_db' =>
                            [
                                "Info" => "Export the database",
                                "Parameters" => []
                            ]
                    ]
            ]
        );
        die();

}