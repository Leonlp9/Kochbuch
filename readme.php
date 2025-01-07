<?php

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Readme</title>

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

    <style>

        /*github like readme style*/
        .readme {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--color);
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
        }

        .readme h1, .readme h2, .readme h3, .readme h4, .readme h5, .readme h6 {
            margin: 20px 0 10px;
            padding: 0;
            font-weight: bold;
        }

        .readme h1 {
            font-size: 36px;
            border-bottom: 1px solid var(--nonSelected);
            text-align: left;
        }

        .readme h2 {
            font-size: 30px;
            border-bottom: 1px solid var(--nonSelected);
        }

        .readme h3 {
            font-size: 24px;
        }

        .readme h4 {
            font-size: 20px;
        }

        .readme h5 {
            font-size: 18px;
        }

        .readme h6 {
            font-size: 16px;
        }

        .readme p {
            margin: 10px 0;
            padding: 0;
        }

        .readme a {
            color: var(--blue);
            text-decoration: none;
        }

        .readme a:hover {
            text-decoration: underline;
        }

        .readme ul, .readme ol {
            margin: 10px 0;
            padding: 0 0 0 20px;
        }

        .readme li {
            margin: 5px 0;
            padding: 0;
        }

        .readme code {
            font-family: Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 14px;
            background-color: var(--secondaryBackground);
            border: 1px solid var(--nonSelected);
            border-radius: 3px;
            padding: 0 5px;
        }

        .readme pre {
            margin: 10px 0;
            padding: 10px;
            background-color: var(--secondaryBackground);
            border: 1px solid var(--nonSelected);
            border-radius: 3px;
            overflow: auto;
        }

        .readme pre code {
            font-family: Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 14px;
            background-color: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
        }

        .readme img {
            max-width: 100%;
            height: auto;
        }

        .readme blockquote {
            margin: 10px 0;
            padding: 10px;
            background-color: var(--secondaryBackground);
            border-left: 5px solid var(--nonSelected);
        }

        .readme blockquote p {
            margin: 0;
        }
    </style>

</head>
<body>
<div class="nav-grid-content">
    <?php
    require_once 'shared/navbar.php';
    ?>
    <div class="container">

        <div id="readme" class="readme"></div>

        <script>
           function generateIdFromText(text) {
                return text.toLowerCase().replace(/[^\w]+/g, '-').replace(/-+$/, '');
            }

            $.get('README.md', function (data) {
                const parsedContent = marked.parse(data);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = parsedContent;

                tempDiv.querySelectorAll('h1').forEach(h1 => {
                    const id = generateIdFromText(h1.textContent);
                    h1.setAttribute('id', id);
                });

                tempDiv.querySelectorAll('h2').forEach(h2 => {
                    const id = generateIdFromText(h2.textContent);
                    h2.setAttribute('id', id);
                });

                $('#readme').html(tempDiv.innerHTML);
            });
        </script>

    </div>
</div>
</body>
</html>