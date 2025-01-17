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
    <title>Küchengeräte</title>

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
        #kuechengeräte {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .kitchenAppliance {
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            background: var(--secondaryBackground);
            cursor: pointer;
        }

        .kitchenAppliance:hover {
            background: var(--selected_highlight);
        }

        .kitchenAppliance img {
            display: block;
            margin: 0 auto;
        }

        .kitchenAppliance h2 {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="nav-grid-content">
    <?php require_once 'shared/navbar.php'; ?>
    <div class="container">
        <h1>Küchengeräte</h1>

        <div id="kuechengeräte">

        </div>

        <script>
            function updateKuechengeräte() {
                var kuechengeräte = $('#kuechengeräte');
                kuechengeräte.empty();

                fetch('api?task=getKitchenAppliances')
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(kitchenAppliance => {
                            var div = $('<div class="kitchenAppliance"></div>');
                            div.append($('<h2>' + kitchenAppliance.Name + '</h2>'));
                            div.append($('<img src="' + kitchenAppliance.Image + '" alt="' + kitchenAppliance.Name + '" style="width: 100px; height: 100px;">'));
                            kuechengeräte.append(div);

                            div.click(() => {
                                var form = new FormBuilder('Küchengerät bearbeiten', (data) => {
                                    var formData = new FormData();
                                    formData.append('id', kitchenAppliance.ID);
                                    formData.append('Name', data.Name);
                                    formData.append('Image', data.Bild);

                                    fetch('api?task=updateKitchenAppliance', {
                                        method: 'POST',
                                        body: formData
                                    }).then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                updateKuechengeräte();
                                            }
                                        });
                                }, () => {

                                })

                                form.addInputField('Name', 'Name', kitchenAppliance.Name);
                                form.addFileField('Bild', "image/*");
                                form.addButton('Löschen', () => {
                                    fetch('api?task=deleteKitchenAppliance&id=' + kitchenAppliance.ID)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                updateKuechengeräte();
                                                form.closeForm();
                                            }
                                        });
                                });

                                form.renderForm();
                            });
                        });
                    });
            }

            updateKuechengeräte();
        </script>

        <br>

        <div>
            <button class="btn green" id="addKuechengerät">Küchengerät hinzufügen</button>
        </div>

        <script>
            $('#addKuechengerät').click(function () {
                var form = new FormBuilder('Küchengerät hinzufügen', (data) => {
                    var formData = new FormData();
                    formData.append('Name', data.Name);
                    formData.append('Image', data.Bild);

                    fetch('api?task=addKitchenAppliance', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                updateKuechengeräte();
                                form.closeForm();
                            }
                        });
                }, () => {

                })

                form.addInputField('Name', 'Name', '');
                form.addFileField('Bild', "image/*");

                form.renderForm();
            });
        </script>

    </div>
</div>
</body>
</html>
