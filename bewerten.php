<?php

include 'shared/global.php';
global $pdo;

if (isset($_POST['edit']) && isset($_POST['rezept']) && isset($_POST['bewertung']) && isset($_POST['name']) && isset($_POST['text'])){
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
}

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
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = $pdo->prepare('DELETE FROM bewertungen WHERE ID = :id');
    $sql->bindValue(':id', $id);
    $sql->execute();
    die();
}