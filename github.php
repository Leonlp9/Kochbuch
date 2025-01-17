<?php
require_once 'shared/global.php';
global $pdo;

// Verzeichnisse/Dateien, die nicht überschrieben werden sollen
$exclude = [
    'config.ini',
    'uploads',
    'ingredientIcons',
];

// Funktion, um Git-Befehl auszuführen
function executeGitCommand($command, &$output = null, &$returnVar = null) {
    $command .= ' 2>&1'; // Fehlerausgabe zusammenführen
    exec($command, $output, $returnVar);
    $output = array_filter($output, function($line) {
        return !preg_match('/^hint:/', $line); // Hinweise ignorieren
    });
    return $returnVar === 0;
}


// Repository-Information abrufen
function getRepositoryInfo() {

    // Repository-Name und Remote-URL abrufen
    $repoName = "Unbekannt";
    $repoUrl = "#";
    if (executeGitCommand('git config --get remote.origin.url', $output)) {
        $remoteUrl = $output[0];

        // Benutzername und Repository-Name extrahieren
        if (preg_match("/github\.com[:\/]([^\/]+)\/([^\/]+)\.git$/", $remoteUrl, $matches)) {
            $userName = $matches[1];
            $repoName = $matches[2];
            $repoName = $userName . '/' . $repoName;
        }

        // URL zur GitHub-Webseite erstellen, wenn es sich um eine GitHub-URL handelt
        if (strpos($remoteUrl, 'github.com') !== false) {
            $repoUrl = preg_replace("/\.git$/", "", $remoteUrl);
        }
    }

    return [
        'name' => $repoName,
        'url' => $repoUrl,
    ];
}

// Prüfen, ob Updates vorhanden sind und Commit-Nachrichten anzeigen
function checkForUpdates() {

    if (!executeGitCommand('git fetch', $output, $returnVar)) {
        // Fehlerdetails in der Statusmeldung zurückgeben
        return "Fehler beim Prüfen auf Updates: " . implode('<br>', $output);
    }

    if (executeGitCommand('git log HEAD..origin/master --oneline', $output)) {
        if (empty($output)) {
            return "Ihr Repository ist auf dem neuesten Stand.";
        }

        $commitMessages = "<ul>";
        foreach ($output as $line) {
            $commitMessages .= "<li>" . htmlspecialchars($line) . "</li>";
        }
        $commitMessages .= "</ul>";
        return "Neue Commits verfügbar:<br>" . $commitMessages;
    }

    return "Fehler beim Abrufen der Commit-Meldungen.";
}

// Repository aktualisieren
function updateRepository() {

    foreach ($GLOBALS['exclude'] as $item) {
        executeGitCommand("git update-index --assume-unchanged $item");
    }

    //git checkout -- .
    if (!executeGitCommand('git checkout -- .', $output)) {
        return "Fehler beim Zurücksetzen der Dateien: <br>" . implode('<br>', $output);
    }

    if (executeGitCommand('git pull', $output)) {

        //Dateien wieder auf den Vergleichsstand setzen
        foreach ($GLOBALS['exclude'] as $item) {
            executeGitCommand("git update-index --no-assume-unchanged $item");
        }

        return "Repository wurde erfolgreich aktualisiert: <br>" . implode('<br>', $output);
    } else {
        return "Fehler beim Aktualisieren des Repositories: <br>" . implode('<br>', $output);
    }
}

// Status-Handling
$status = "Bereit für Updates.";
$step = "check"; // Schritt, um anzuzeigen, welche Aktion aktuell möglich ist

if (isset($_POST['check_updates'])) {
    $status = checkForUpdates();
    $step = (strpos($status, 'Neue Commits verfügbar') !== false) ? "install" : "check";
} elseif (isset($_POST['install_updates'])) {
    $status = updateRepository();
    $step = "check";
}

$repoInfo = getRepositoryInfo();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Github</title>

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL ?>/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL ?>/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL ?>/icons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo BASE_URL ?>/icons/site.webmanifest">
    <link rel="mask-icon" href="<?php echo BASE_URL ?>/icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="<?php echo BASE_URL ?>/icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Kochbuch">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Kochbuch">
    <meta name="msapplication-TileColor" content="#f6f6f6">
    <meta name="msapplication-config" content="<?php echo BASE_URL ?>/icons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://kit.fontawesome.com/8482ce4752.js" crossorigin="anonymous"></script>

    <!-- QuillJS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Include jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- jQuery UI Touch Punch -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>


    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="script.js"></script>

    <style>
        .repo-name {
            margin-top: 10px;
        }

        .repo-name a {
            color: var(--blue);
        }

        .status {
            margin-top: 10px;
        }

        .form {
            margin-top: 10px;
            display: grid;
            gap: 10px;
        }

        .form button {
            width: 100%;
            padding: 10px;
            background-color: var(--blue);
            color: var(--color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .container{
            word-break: break-word;
        }
    </style>
</head>
<body>
<div class="nav-grid-content">
    <?php require_once 'shared/navbar.php'; ?>
    <div class="container">
        <h1>GitHub</h1>

        <h2 class="repo-name"><a href="<?php echo htmlspecialchars($repoInfo['url']); ?>" target="_blank">
                <?php echo htmlspecialchars($repoInfo['name']); ?></a></h2>
        <p class="status">Status: <?php echo $status; ?></p>
        <form method="post" class="form">
            <?php if ($step === "check"): ?>
                <button type="submit" name="check_updates">Updates suchen</button>
            <?php elseif ($step === "install"): ?>
                <button type="submit" name="install_updates">Install Now</button>
            <?php endif; ?>
        </form>

    </div>
</div>
</body>
</html>
