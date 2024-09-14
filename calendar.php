<?php

include "shared/global.php";
global $pdo;

$kalender = "CREATE TABLE IF NOT EXISTS kalender (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Datum DATE NOT NULL,
    Rezept_ID INT,
    Text TEXT
)";
$pdo->exec($kalender);


//const date = input.value;
//const rezeptID = <$id>;
//window.location.href = `calendar.php?date=${date}&rezept=${rezeptID}&action=add`;
if (isset($_GET['action']) && $_GET['action'] == "add") {
    $date = $_GET['date'];
    $rezept = (isset($_GET['rezept'])) ? $_GET['rezept'] : null;
    $action = $_GET['action'];
    $info = (isset($_GET['info'])) ? $_GET['info'] : null;
    $stmt = $pdo->prepare("INSERT INTO kalender (Datum, Rezept_ID, Text) VALUES (?, ?, ?)");
    $stmt->execute([$date, $rezept, $info]);
    header("Location: calendar.php");

}else if (isset($_GET['action']) && $_GET['action'] == "delete") {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM kalender WHERE ID = ?");
    $stmt->execute([$id]);
    header("Location: calendar.php");
}

$kalender = $pdo->prepare("SELECT kalender.ID as Kalender_ID, kalender.Datum, kalender.Rezept_ID, kalender.Text, rezepte.ID, rezepte.Name, bilder.Image
        FROM kalender
         LEFT JOIN rezepte ON kalender.Rezept_ID = rezepte.ID
         LEFT JOIN bilder ON rezepte.ID = bilder.Rezept_ID
         WHERE kalender.Datum >= CURDATE()
         ORDER BY Datum");
$kalender->execute();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kalender</title>

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

    <link rel="stylesheet" href="style.css">

    <style>
        .day {
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .dayContent {
            display: grid;
            gap: 10px;
        }

        .rezept {
            display: grid;
            grid-template-columns: 1fr 2fr 25px;
            gap: 10px;
            padding: 5px;
            border-radius: 8px;
            background-color: var(--secondaryBackground);
            -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
        }

        .rezept:hover {
            background-color: var(--nonSelected);
        }

        .rezept img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .calendarAdd {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            font-size: 20px;
            background-color: var(--nonSelected);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .calendarAdd {
                bottom: 60px;
                right: 10px;
            }
        }


        input[type="date"] {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--nonSelected);
            outline: none;
            width: 100%;
        }

        input[type="text"].planenExtraInput {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--nonSelected);
            margin-bottom: 10px;
            outline: none;
            background: var(--background);
            color: var(--color);
            text-transform: none;
        }
    </style>

</head>
<body>

<!--    New custom calendar element with text and without recipe-->
    <div class="calendarAdd">
        <i class="fas fa-plus" style="color: white"></i>
    </div>
    <script>
        document.querySelector('.calendarAdd').addEventListener('click', () => {
            const back = document.createElement('div');
            back.style.position = 'fixed';
            back.style.top = '0';
            back.style.left = '0';
            back.style.width = '100%';
            back.style.height = '100%';
            back.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            back.style.zIndex = '1000';
            back.style.display = 'flex';
            back.style.justifyContent = 'center';
            back.style.alignItems = 'center';

            const form = document.createElement('form');
            form.style.backgroundColor = 'var(--secondaryBackground)';
            form.style.padding = '20px';
            form.style.borderRadius = '10px';
            form.style.display = 'grid';
            form.style.gap = '10px';
            form.style.width = '300px';
            form.style.maxWidth = '100%';

            const h2 = document.createElement('h2');
            h2.innerText = 'Planen';
            form.appendChild(h2);

            const date = document.createElement('input');
            date.type = 'date';
            date.value = new Date().toISOString().split('T')[0];
            date.min = new Date().toISOString().split('T')[0];
            date.required = true;
            form.appendChild(date);

            const text = document.createElement('input');
            text.classList.add('planenExtraInput');
            text.type = 'text';
            text.placeholder = 'Text';
            text.required = true;
            form.appendChild(text);

            const button = document.createElement('button');
            button.innerText = 'Planen';
            button.style.padding = '10px 20px';
            button.style.backgroundColor = '#bbef9c';
            button.style.color = 'var(--color)';
            button.style.border = 'none';
            button.style.borderRadius = '10px';
            button.style.cursor = 'pointer';
            button.style.marginTop = '20px';
            form.appendChild(button);

            back.appendChild(form);
            document.body.appendChild(back);


            back.addEventListener('click', () => {
                back.remove();
            });

            form.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const date = form.querySelector('input[type="date"]').value;
                const text = form.querySelector('input[type="text"]').value;
                if (date === '' || text === '') {
                    alert('Bitte füllen Sie alle Felder aus');
                    return;
                }
                window.location.href = `calendar.php?date=${date}&action=add&info=${text}`;
            });
        });
    </script>
    <div class="nav-grid">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">
            <h1>Meine Woche</h1>

            <div id='calendar'>
                <?php

                $lastDate = "";

                while ($row = $kalender->fetch()) {
                    $date = $row['Datum'];

                    //convert to german date format
                    $date = date("d.m.Y", strtotime($date));

                    $rezept = $row['Name'];
                    $text = $row['Text'];

                    if ($lastDate != $date) {
                        echo "<div class='day'>";
                        echo "<h2>$date</h2>";
                        $lastDate = $date;
                        echo "<div class='dayContent'>";
                    }

                    if ($rezept == null) {
                        echo "
                        <div class='rezept' style='min-height: 100px; grid-template-columns: 2fr 25px;'>
                            <div>
                                <h3 style='text-transform: none' >$text</h3>
                            </div>
                           <div onclick='window.location.href=`calendar.php?action=delete&id=$row[Kalender_ID]`' style='cursor: pointer; display: flex; justify-content: center; align-items: center; background-color: var(--nonSelected); border-radius: 10px'>
                                <i class='fas fa-trash-alt' style='color: white'></i>
                           </div>
                        </div>
                        ";
                    }else {
                        echo "
                        <a class='rezept' href='rezept.php?id=$row[ID]'>
                            <img src='uploads/$row[Image]' alt='$row[Name]'>
                            <div>
                                <h3 style='text-transform: none' >$rezept</h3>
                                <p style='text-transform: none'>$text</p>
                            </div>
                            <div onclick='window.location.href=`calendar.php?action=delete&id=$row[Kalender_ID]`' style='cursor: pointer; display: flex; justify-content: center; align-items: center; background-color: var(--nonSelected); border-radius: 10px'>
                                <i class='fas fa-trash-alt' style='color: white'></i>
                            </div>
                        </a>
                        ";
                    }

                    if ($lastDate != $date) {
                        echo "</div>";
                        echo "</div>";
                    }
                }

                if ($lastDate == "") {
                    echo "<h2>Keine Einträge</h2>";
                }

                ?>
            </div>

            <script>
                let kalender = <?php echo $kalender ?>;
                let calendarEl = document.getElementById('calendar');

                calendarEl.innerHTML = kalender.map(e => {
                    return `<div class="day">
                        <h2>${e.Datum}</h2>
                        <p>${e.Text}</p>
                    </div>`
                }).join('');

            </script>

        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
