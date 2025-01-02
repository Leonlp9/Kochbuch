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
    <title>Einkaufsliste</title>

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
        <h1>Einkaufsliste</h1>

        <div class="cart" style="overflow: hidden;">
            <table>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Menge</th>
                    <th>Einheit</th>
                </tr>
            </table>
        </div>

        <script>
            // http://localhost/Kochbuch/api?task=getEinkaufsliste

            $.get("api?task=getEinkaufsliste", function (data) {
                console.log(data);
                data.forEach((item) => {
                    $(".cart table").append(`
                        <tr>
                            <td><img src="${item.Image}" alt="${item.Name}" style="width: 50px; height: 50px;"></td>
                            <td>${item.Name}</td>
                            <td>${item.Menge}</td>
                            <td>${item.Einheit}</td>
                        </tr>
                    `);
                });
            });
        </script>

    </div>
</div>
</body>
<script src="script.js"></script>
</html>
