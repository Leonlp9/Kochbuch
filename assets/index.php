<?php
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Game Elements Test</title>

    <script src="https://kit.fontawesome.com/8482ce4752.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="../style.css">

    <style>
        .icons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .icons img {
            width: 75px;
            height: 75px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Assets</h1>

    <h2>Farb Paletten</h2>
    <div class="colorPalette">
        <button class="btn blue" onclick="setStyle('dark')">Dark Mode</button>
        <button class="btn blue" onclick="setStyle('dark-rounder')">Dark Round Mode</button>
        <button class="btn blue" onclick="setStyle('pink')">Pink Mode</button>
        <button class="btn blue" onclick="setStyle('pink-rounder')">Pink Round Mode</button>
        <button class="btn blue" onclick="setStyle('light')">Light Mode</button>
        <button class="btn blue" onclick="setStyle('light-rounder')">Light Round Mode</button>
    </div>
    <script>
        function setStyle(style) {
            document.documentElement.setAttribute('theme', style);
        }
    </script>

    <h2>Knöpfe</h2>
    <button class="btn">Willkommen Zurück</button>
    <button class="blue btn">Willkommen Zurück</button>
    <button class="white btn">Willkommen Zurück</button>
    <button class="red btn">Willkommen Zurück</button>
    <button class="green btn">Willkommen Zurück</button>
    <button disabled class="btn">Willkommen Zurück</button>

    <h2>Switch</h2>
    <label class="switch blue">
        <input type="checkbox">
        <span class="slider"></span>
    </label>
    <label class="switch blue">
        <input type="checkbox" checked>
        <span class="slider"></span>
    </label>
    <br>
    <label class="switch green">
        <input type="checkbox">
        <span class="slider"></span>
    </label>
    <label class="switch green">
        <input type="checkbox" checked>
        <span class="slider"></span>
    </label>
    <br>
    <label class="switch red">
        <input type="checkbox">
        <span class="slider"></span>
    </label>
    <label class="switch red">
        <input type="checkbox" checked>
        <span class="slider"></span>
    </label>
    <br>
    <label class="switch white">
        <input type="checkbox">
        <span class="slider"></span>
    </label>
    <label class="switch white">
        <input type="checkbox" checked>
        <span class="slider"></span>
    </label>
    <br>
    <br>
    <label class="switch red">
        <input type="checkbox" disabled>
        <span class="slider"></span>
    </label>
    <label class="switch red">
        <input type="checkbox" checked disabled>
        <span class="slider"></span>
    </label>
    <br>
    <label class="switch green">
        <input type="checkbox" disabled>
        <span class="slider"></span>
    </label>
    <label class="switch green">
        <input type="checkbox" checked disabled>
        <span class="slider"></span>
    </label>

    <h2>Number Input</h2>
    <div class="number-input">
        <button class="down">-</button>
        <input class="quantity" min="0" name="quantity" value="1" type="number">
        <button class="up">+</button>
    </div>
    <br>
    <div class="number-input" style="width: 125px;">
        <button class="down">-</button>
        <input class="quantity" min="0" name="quantity" value="1" type="number">
        <button class="up">+</button>
    </div>

    <h2>Select</h2>
    <select id="select">
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
        <option value="3">Option 3</option>
        <option value="4">Option 4</option>
    </select>

    <select id="select1" disabled>
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
        <option value="3">Option 3</option>
        <option value="4">Option 4</option>
    </select>

    <h2>Input</h2>
    <input type="text" value="Text">
    <input type="text" value="Text" disabled>

    <h2>Textarea</h2>
    <textarea>Text</textarea>
    <textarea class="onlyVertical">Text</textarea>
    <textarea class="onlyHorizontal">Text</textarea>
    <textarea disabled>Text</textarea>

    <h2>Progress</h2>
    <progress value="0" max="100"></progress>
    <progress value="25" max="100" class="blue"></progress>
    <progress value="50" max="100" class="white"></progress>
    <progress value="75" max="100" class="red"></progress>
    <progress value="100" max="100" class="green"></progress>

    <script>

        function animateProgress() {
            let progress = document.querySelectorAll('progress');
            progress.forEach((element) => {
                element.value = Math.floor(Math.random() * 100);
            });
        }

        setInterval(animateProgress, 1000);

    </script>

    <h2>Fonts</h2>
    <div class="VarelaRound">Varela Round</div>
    <div class="Roboto">Roboto</div>

    <h2>Divs</h2>
    <div class="element">
        Element
    </div>
    <div class="element blue">
        Element
    </div>
    <div class="element white">
        Element
    </div>
    <div class="element red">
        Element
    </div>
    <div class="element green">
        Element
    </div>

    <h2>Upload</h2>
    <label class="upload">
        <i class="fas fa-upload"></i> Datei hochladen
        <input type="file">
    </label>

    <h2>List</h2>
    <ul>
        <li>Element 1</li>
        <li>Element 2</li>
        <li>Element 3</li>
        <li>Element 4</li>
    </ul>

    <h2>Links</h2>
    <a href="#">Link</a>
    <a href="#" class="underline">Link</a>

    <h2>Submit</h2>
    <input type="submit" class="btn">
    <input type="submit" class="btn blue">
    <input type="submit" class="btn white">
    <input type="submit" class="btn red">
    <input type="submit" class="btn green">

    <h2>Icons</h2>
    <div class="icons">
        <?php
            //alle Icons aus /ingredientIcons/ anzeigen
            $icons = scandir('../ingredientIcons');
            foreach ($icons as $icon) {
                if ($icon != '.' && $icon != '..') {
                    echo '<img src="../ingredientIcons/' . $icon . '" alt="' . $icon . '">';
                }
            }
        ?>
    </div>
</div>
<script src="../script.js"></script>
</body>
</html>