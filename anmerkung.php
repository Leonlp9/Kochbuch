<?php

include 'shared/global.php';
global $pdo;

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
}