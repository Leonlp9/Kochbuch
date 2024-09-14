<?php
header('Content-Type: application/json');

include 'shared/global.php';
global $pdo;

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
?>
