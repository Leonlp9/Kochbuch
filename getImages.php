<?php

header('Content-Type: application/json');

include 'shared/global.php';
global $pdo;

if (isset($_GET['rezept_id']) && isset($_GET['image']) && isset($_GET['delete'])) {
    $rezept_id = $_GET['rezept_id'];
    $image = $_GET['image'];
    $delete = $_GET['delete'];

    unlink('uploads/' . $image);

    $sql = $pdo->prepare('DELETE FROM bilder WHERE Rezept_ID = :rezept_id AND Image = :image');
    $sql->bindValue(':rezept_id', $rezept_id);
    $sql->bindValue(':image', $image);
    $sql->execute();
    die();
}

if (isset($_GET['rezept_id'])) {
    $id = $_GET['rezept_id'];
    $sql = $pdo->prepare('SELECT * FROM bilder WHERE Rezept_ID = :id');
    $sql->bindValue(':id', $id);
    $sql->execute();
    $bilder = $sql->fetchAll(PDO::FETCH_ASSOC); // Use PDO::FETCH_ASSOC to get only associative keys

    echo json_encode($bilder);
    die();
}