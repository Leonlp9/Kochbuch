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

    <meta name="apple-mobile-web-app-title" content="Kochbuch">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Kochbuch">
    <meta name="msapplication-TileColor" content="#f6f6f6">
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
            overflow: auto;
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

        .readme table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;

            /*wenn zu breit*/
            overflow-x: auto;
        }

        .readme th, .readme td {
            border: 1px solid var(--nonSelected);
            padding: 5px;
        }

        .readme th {
            background-color: var(--secondaryBackground);
        }

        .readme tr:nth-child(even) {
            background-color: var(--secondaryBackground);
        }

        .readme tr:nth-child(odd) {
            background-color: var(--background);
        }

        .readme thead {
            background-color: var(--secondaryBackground);
        }

        .readme thead th {
            border: 1px solid var(--nonSelected);
        }

        .readme tbody {
            background-color: var(--background);
        }

        .readme tbody td {
            border: 1px solid var(--nonSelected);
        }

        .readme hr {
            border: none;
            border-top: 1px solid var(--nonSelected);
            margin: 20px 0;
        }

        .readme .toc {
            display: none;
        }

        .readme .toc h2 {
            font-size: 20px;
            margin: 0;
        }

        .readme .toc ul {
            list-style-type: none;
            padding: 0;
        }

        .readme .toc li {
            margin: 5px 0;
        }

        .readme .toc a {
            color: var(--blue);
            text-decoration: none;
        }

        .readme .toc a:hover {
            text-decoration: underline;
        }

        .readme .toc .toc-2 {
            padding-left: 20px;
        }

        .readme .toc .toc-3 {
            padding-left: 40px;
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

                ['h1', 'h2', 'h3', 'h4'].forEach(tag => {
                    tempDiv.querySelectorAll(tag).forEach(element => {
                        const id = generateIdFromText(element.textContent);
                        element.setAttribute('id', id);
                    });
                });

                $('#readme').html(tempDiv.innerHTML);
            });
        </script>

    </div>
</div>
</body>
</html>
