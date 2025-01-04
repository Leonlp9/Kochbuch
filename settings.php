<?php
require_once 'shared/global.php';
global $pdo;

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

    <style>
        .settings {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .settings div {
            display: grid;
        }

        .settings div a {
            display: grid;
            text-decoration: none;
        }


        @media (max-width: 768px) {
            .settings {
                grid-template-columns: 1fr;
            }
        }

        #githubVersion {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 5px;
            font-size: 1em;
        }
    </style>
</head>
<body>
<div class="nav-grid-content">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">
		<h1>Einstellungen</h1>

		<br>

        <div class="settings">
            <div>
                <h2>Theme</h2>
                <select name="theme" id="theme" onchange="changeTheme()">
                    <option value="light">Hell</option>
                    <option value="dark">Dunkel</option>
                    <option value="helloween">Helloween</option>
                    <option value="christmas">Weihnachten</option>
                    <option value="spring">Frühling</option>
                    <option value="dracula">Dracula</option>
                    <option value="midnight">Mitternacht</option>
                </select>
                <script>

                    function setTheme(newTheme) {
                        document.documentElement.setAttribute('theme', newTheme);
                        localStorage.setItem('theme', newTheme);
                    }

                    function changeTheme() {
                        setTheme(document.getElementById('theme').value);
                    }

                    const theme = localStorage.getItem('theme');
                    if (theme) {
                        setTheme(theme);
                        document.getElementById('theme').value = theme;
                    }
                </script>
            </div>

            <!--        Zutaten-Filterprofil-->
            <div>
                <h2>Filterprofile</h2>
                <a href="filterprofile.php">
                    <button class="btn blue">Filterprofile bearbeiten</button>
                </a>
            </div>

    <!--		Kategorien bearbeiten-->
            <div>
                <h2>Kategorien</h2>
                <a href="kategorien.php">
                    <button class="btn blue">Kategorien bearbeiten</button>
                </a>
            </div>

    <!--		Zutaten bearbeiten-->
            <div>
                <h2>Zutaten</h2>
                <a href="zutaten.php">
                    <button class="btn blue">Zutaten bearbeiten</button>
                </a>
            </div>

            <div>
                <h2>Sicherheitskopie erstellen</h2>
                <div onclick="downloadBackUp()">
                    <button class="btn blue">Sicherheitskopie erstellen</button>
                </div>
            </div>

            <div>
                <h2>GitHub Version</h2>
                <div id="githubVersion"></div>
            </div>

            <script>
                function downloadBackUp() {
                    $.ajax({
                        url: 'api.php',
                        type: 'GET',
                        data: {
                            task: 'export_db'
                        },
                        success: function (data) {
                            var blob = new Blob([data], {type: 'application/octet-stream'});
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'Backup_' + new Date().toISOString().slice(0, 10) + '.sql';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                        }
                    });
                }

                async function getLastGitHubCommit() {
                    try {
                        const response = await fetch('https://api.github.com/repos/Leonlp9/Kochbuch/commits');
                        const data = await response.json();
                        return data[0].commit; // Dies gibt den Commit-Titel zurück
                    } catch (error) {
                        console.error('Fehler beim Abrufen des letzten Commit-Titels:', error);
                        return null; // Im Fehlerfall null zurückgeben oder entsprechend anpassen
                    }
                }

                function loadGitHubVersion() {
                    getLastGitHubCommit().then(data => {
                        $('#githubVersion').html(`<p><em>Letzter Commit:</em> ${data.message}</p>`);
                    });
                }

                loadGitHubVersion();

                function getLocalVersion() {
                    return fetch('version.txt').then(response => response.text());
                }

            </script>
        </div>
	</div>
</div>
</body>
<script src="script.js"></script>
</html>
