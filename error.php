<?php

//error codes
$errors = [
    403 => "Zugriff verweigert!",
    404 => "Seite nicht gefunden",
    500 => "Interner Serverfehler",
    1001 => "Datenbankverbindung fehlgeschlagen"
];

if (isset($_GET['code'])) {
    $error = $_GET['code'];
    if (array_key_exists($error, $errors)) {
        $errorText = $errors[$error];
    } else {
        $errorText = "Unbekannter Fehler";
    }
} else {
    $errorText = "Unbekannter Fehler";
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $errorText; ?></title>

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
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="script.js"></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="nav-grid-content">
    <?php

    //wenn kein datenbankfehler, dann navbar anzeigen
    if ($error != 1001) {
        require_once 'shared/navbar.php';
    }
    ?>
    <div class="container">
        <h1><?php echo $errorText; ?></h1>

        <?php if ($error == 1001) { ?>
            <p>Hilfe zur Installation findest du <a href="readme#installation">hier</a></p>
            <p>Oder wenn du die config.ini bearbeiten möchtest, klicke <a href="setup.php">hier</a></p>
        <?php } ?>
    </div>
</div>
</body>
</html>
