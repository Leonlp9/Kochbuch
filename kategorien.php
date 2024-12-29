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
	<title>Kategorien</title>

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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <link rel="stylesheet" href="style.css">

    <style>

        .kategorie {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
        }

        .kategorie .count {
            margin-left: 5px;
            font-size: 0.8em;
        }

        .subKategorien {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: var(--color);
            min-width: 100px;
            min-height: 50px;
        }

        .subKategorie {
            padding: 5px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="nav-grid">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">
		<h1>Kategorien</h1>
        <br>

        <div class="kategorien"></div>

        <button class="btn green" id="addKategorie">Kategorie hinzufügen</button>

        <script>
            // Kategorien laden
            $.get('api.php?task=getKategorien&includeCount=true', function (data) {

                var kategorienDiv = $('.kategorien');
                kategorienDiv.html('');

                data.forEach(function (kategorie) {
                    var kategorieDiv = $('<div class="kategorie" data-id="' + kategorie.ID + '" style="background-color: ' + kategorie.ColorHex + '"></div>');
                    kategorieDiv.html(kategorie.Name);

                    var count = $('<span class="count">' + kategorie.usage_count + '</span>');
                    kategorieDiv.append(count);

                    var subKategorienDiv = $('<div class="subKategorien"></div>');
                    kategorieDiv.append(subKategorienDiv);

                    new Sortable(subKategorienDiv[0], {
                        group: 'shared',
                        animation: 200,
                        onEnd: function (evt) {

                            if (evt.newIndex === evt.oldIndex) {
                                return;
                            }

                            // das element das verschoben wurde
                            var item = evt.item.dataset.id;

                            // in das element das verschoben wurde
                            var target = evt.to.parentElement.dataset.id;

                            console.log(item);
                            console.log(target);

                        }
                    });

                    kategorienDiv.append(kategorieDiv);
                });
            });

            new Sortable($('.kategorien')[0], {
                group: 'shared',
                animation: 200,
                sort: false, // Verhindert das Sortieren der Hauptkategorien
                onEnd: function (evt) {

                    if (evt.newIndex === evt.oldIndex) {
                        return;
                    }

                    // das element das verschoben wurde
                    var item = evt.item.dataset.id;

                    // in das element das verschoben wurde
                    var target = evt.to.parentElement.dataset.id;

                    console.log(item);
                    console.log(target);

                }
            });

            $('#addKategorie').click(function () {
                let kategorieBuilder = new FormBuilder("Neue Kategorie erstellen", (data) => {
                    console.log('Formular gesendet:', data);
                }, () => {
                    console.log('Abbrechen gedrückt');
                });

                kategorieBuilder.addInputField("Name", "Name der Kategorie", "");
                kategorieBuilder.addColorField("Farbe", "#ba5656");
                kategorieBuilder.renderForm();
            });

        </script>
	</div>
</div>
</body>
<script src="script.js"></script>
</html>
