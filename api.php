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

            echo json_encode($bilder);
            die();
        }else{
            echo json_encode(['error' => 'No rezept_id provided']);
            die();
        }
    case 'deleteImage':
        if (isset($_GET['rezept_id']) && isset($_GET['image'])) {
            $rezept_id = $_GET['rezept_id'];
            $image = $_GET['image'];

            unlink('uploads/' . $image);

            $sql = $pdo->prepare('DELETE FROM bilder WHERE Rezept_ID = :rezept_id AND Image = :image');
            $sql->bindValue(':rezept_id', $rezept_id);
            $sql->bindValue(':image', $image);
            $sql->execute();
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

            echo json_encode(['success' => true]);
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
                $zutaten[$key]['Image'] = 'default.svg';
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

                $zutatenTables = [""];
                foreach ($zutaten_array as $zutat) {
                    if(!in_array($zutat['table'], $zutatenTables)){
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

            // Kalender hinzufügen
            $sql = $pdo->prepare('SELECT * FROM kalender WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $kalender = $sql->fetchAll(PDO::FETCH_ASSOC);
            $rezepte[0]['Kalender'] = $kalender;

            // Images hinzufügen
            $sql = $pdo->prepare('SELECT * FROM bilder WHERE Rezept_ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();
            $bilder = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($bilder as &$bild) {
                $bild['Image'] = 'uploads/' . $bild['Image'];
            }
            $rezepte[0]['Bilder'] = $bilder;


            echo json_encode($rezepte);
            die();
        } else {
            echo json_encode(['error' => 'No id provided']);
            die();
        }
    case "addEvaluation":
        if (isset($_POST['rezept']) && isset($_POST['bewertung']) && isset($_POST['name']) && isset($_POST['text'])) {
            $rezept = $_POST['rezept'];
            $bewertung = $_POST['bewertung'];
            $name = $_POST['name'];
            $text = $_POST['text'];

            $sql = $pdo->prepare('INSERT INTO bewertungen (Rezept_ID, Bewertung, Name, Text) VALUES (:rezept, :bewertung, :name, :text)');
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "editEvaluation":
        if (isset($_POST['edit']) && isset($_POST['rezept']) && isset($_POST['bewertung']) && isset($_POST['name']) && isset($_POST['text'])) {
            $edit = $_POST['edit'];
            $rezept = $_POST['rezept'];
            $bewertung = $_POST['bewertung'];
            $name = $_POST['name'];
            $text = $_POST['text'];

            $sql = $pdo->prepare('UPDATE bewertungen SET Bewertung = :bewertung, Name = :name, Text = :text WHERE ID = :edit AND Rezept_ID = :rezept');
            $sql->bindValue(':edit', $edit);
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided']);
            die();
        }
    case "deleteEvaluation":
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
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
        $kategorien = $pdo->query("SELECT ID, Name, ColorHex FROM kategorien order by Name")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($kategorien);
        die();
    case "getFilterprofile":
        $filterprofile = $pdo->query("SELECT * FROM filterprofile")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filterprofile as $key => $filter) {
            $filterprofile[$key]['Filter'] = json_decode($filter['Filter'], true);
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
        if (isset($_POST['name']) && isset($_POST['unit'])) {
            $name = $_POST['name'];
            //$image Lowercase name + .svg
            $image = strtolower($name) . '.svg';
            $unit = $_POST['unit'];

            $sql = $pdo->prepare('INSERT INTO zutaten (Name, Image, Unit) VALUES (:name, :image, :unit)');
            $sql->bindValue(':name', $name);
            $sql->bindValue(':image', $image);
            $sql->bindValue(':unit', $unit);
            $sql->execute();

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "anmerkung":
        if (isset($_POST['rezept']) && isset($_POST['text'])) {
            $rezept = $_POST['rezept'];
            $text = $_POST['text'];

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
    case "addBewertung":
        if (isset($_POST['rezept']) && isset($_POST['bewertung']) && isset($_POST['name']) && isset($_POST['text'])) {
            $rezept = $_POST['rezept'];
            $bewertung = $_POST['bewertung'];
            $name = $_POST['name'];
            $text = $_POST['text'];

            $sql = $pdo->prepare('INSERT INTO bewertungen (Rezept_ID, Bewertung, Name, Text) VALUES (:rezept, :bewertung, :name, :text)');
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "editBewertung":
        if (isset($_POST['id']) && isset($_POST['rezept']) && isset($_POST['bewertung']) && isset($_POST['name']) && isset($_POST['text'])) {
            $id = $_POST['id'];
            $rezept = $_POST['rezept'];
            $bewertung = $_POST['bewertung'];
            $name = $_POST['name'];
            $text = $_POST['text'];

            $sql = $pdo->prepare('UPDATE bewertungen SET Bewertung = :bewertung, Name = :name, Text = :text WHERE ID = :id AND Rezept_ID = :rezept');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':rezept', $rezept);
            $sql->bindValue(':bewertung', $bewertung);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':text', $text);
            $sql->execute();

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "deleteBewertung":
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $sql = $pdo->prepare('DELETE FROM bewertungen WHERE ID = :id');
            $sql->bindValue(':id', $id);
            $sql->execute();

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
            $kal['Image'] = 'uploads/' . $kal['Image'];
        }

        echo json_encode($kalender);
        die();
    case "addKalender":
        if (isset($_POST['date']) && isset($_POST['rezept']) && isset($_POST['info'])) {
            $date = $_POST['date'];
            $rezept = $_POST['rezept'];
            $info = $_POST['info'];

            $stmt = $pdo->prepare("INSERT INTO kalender (Datum, Rezept_ID, Text) VALUES (?, ?, ?)");
            $stmt->execute([$date, $rezept, $info]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }
    case "deleteKalender":
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM kalender WHERE ID = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
            die();
        } else {
            echo json_encode(['error' => 'Not all parameters provided', 'success' => false]);
            die();
        }

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
                            ]
                    ]
            ]
        );
        die();
}