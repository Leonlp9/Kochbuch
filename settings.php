<?php
require_once 'shared/global.php';
global $pdo;

if (isset($_POST['export_db'])) {

    if (file_exists("dbInfo.ini") && is_readable("dbInfo.ini")) {
		// Get username and password from dbInfo.ini
		$dbInfo = parse_ini_file("dbInfo.ini");

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
}

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Einstellungen</title>

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

	<!-- Include jQuery UI -->
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<!-- jQuery UI Touch Punch -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>


	<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="nav-grid">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">
		<h1>Einstellungen</h1>

		<br>
<!--		buttons die umleiten für -->
        <!--        Zutaten-Filterprofil-->
        <h2>Filterprofile</h2>
        <a href="filterprofile.php">
            <button class="btn blue">Filterprofile bearbeiten</button>
        </a>
<!--		Kategorien bearbeiten-->
		<h2>Kategorien</h2>
		<a href="kategorien.php">
			<button class="btn blue">Kategorien bearbeiten</button>
		</a>
<!--		Zutaten bearbeiten-->
		<h2>Zutaten</h2>
		<a href="zutaten.php">
			<button class="btn blue">Zutaten bearbeiten</button>
		</a>

        <h2>Sicherheitskopie erstellen</h2>
        <form method="post">
            <button type="submit" name="export_db" class="btn green">Datenbank exportieren</button>
        </form>
	</div>
</div>
</body>
<script src="script.js"></script>
</html>
