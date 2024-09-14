<?php

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    require_once 'shared/global.php';
    global $pdo;

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

    header("Location: index.php");
} else {
    header("Location: error.php?error=1002");
}