<?php

header('Content-Type: application/json');

if (isset($_POST['name']) && isset($_POST['unit'])) {
    include 'shared/global.php';
    global $pdo;

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
} else {
    echo json_encode(['success' => false]);
}