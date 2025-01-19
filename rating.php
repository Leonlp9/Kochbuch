<?php

include "shared/global.php";
global $pdo;

if (!isset($_GET['id'])) {
    header("Location: index.php");
    die();
}

$id = $_GET['id'];

$rezept = json_decode(file_get_contents(BASE_URL. "api?task=getRezept&id=$id&zutaten"), true)[0];

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rating</title>

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

        .rezept {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr;
        }

        .images {
            width: 100%;
            display: grid;
            margin: 10px 0;
        }

        .images img {
            border-radius: 10px;
            max-height: 600px;
            max-width: 100%;
            margin: 0 auto;
        }

        #bewertungen {
            list-style: none;
            padding: 0;
            width: 100%;
            display: grid;
            gap: 10px;
        }

        #bewertungen li {
            display: grid;
            grid-template-columns: 50px 1fr;
            gap: 10px;
            align-items: start;
            width: 100%;
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            transition: background 0.2s ease;
        }

        #bewertungen li:hover {
            background: var(--nonSelected);
        }

        #bewertungen img {
            border-radius: 5px;
        }

        #bewertungForm {
            display: grid;
            gap: 10px;
        }

        #bewertungForm div {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr auto;
        }

        #bewertungForm input, #bewertungForm textarea {
            padding: 5px;
            border-radius: 5px;
            border: none;
            outline: none;
            width: 100%;
            background: var(--nonSelected);
            color: var(--color);
        }

        #bewertungen li:first-child:hover {
            background: var(--secondaryBackground);
        }

        #bewertungForm textarea {
            max-width: 100%;
            min-width: 100%;
            min-height: 100px;
            max-height: 300px;
            resize: vertical;
        }

        #bewertungForm button {
            padding: 5px;
            border-radius: 5px;
            border: none;
            background: var(--green);
            color: var(--background);
            cursor: pointer;
        }

        #bewertungForm button:hover {
            background: var(--darkerGreen);
        }

        #bewertungForm #bewertungStars {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .fa-star {
            cursor: pointer;
        }

        #anmerkungen {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        #anmerkungen li {
            background: var(--secondaryBackground);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            transition: background 0.2s ease;
            display: grid;
            grid-template-columns: 1fr auto;
        }

        #anmerkungen li:hover {
            background: var(--nonSelected);
        }
    </style>
</head>
<body>
<div class="nav-grid-content">
    <?php
    require_once 'shared/navbar.php';
    ?>
    <div class="container">
        <h1>Rating</h1>

        <div class="rezept">
            <div class="images">
                <?php
                foreach ($rezept['Bilder'] as $bild) {
                    $bild = $bild["Image"];
                    echo "<img src='$bild' alt=''>";
                }
                ?>
            </div>

            <div class="info">
                <h2><?php echo $rezept['Name']; ?></h2>
            </div>

            <ul class="no-print" id="bewertungen">

                <li>
                    <img src="https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" alt="" id="bewertungImage">
                    <div id="bewertungForm">
                        <div>
                            <input type="text" placeholder="Name" id="bewertungName">
                            <input type="number" placeholder="Bewertung" min="0" max="5" hidden>
                            <div id="bewertungStars">
                                <!--                                    stars-->
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <script>
                                document.getElementById("bewertungStars").addEventListener("click", (e) => {
                                    let stars = document.getElementById("bewertungStars").children;
                                    let rating = 0;
                                    for (let i = 0; i < stars.length; i++) {
                                        if (e.target === stars[i]) {
                                            rating = i + 1;
                                        }
                                    }
                                    document.getElementById("bewertungForm").querySelector("input[type=number]").value = rating;
                                    for (let i = 0; i < stars.length; i++) {
                                        if (i < rating) {
                                            stars[i].classList.remove("far");
                                            stars[i].classList.add("fas");
                                        } else {
                                            stars[i].classList.remove("fas");
                                            stars[i].classList.add("far");
                                        }
                                    }
                                });
                            </script>
                        </div>
                        <textarea placeholder="Text"></textarea>
                        <button onclick="bewerten()">Bewerten</button>
                        <script>
                            function bewerten() {
                                let name = document.getElementById("bewertungName").value;
                                let rating = document.getElementById("bewertungForm").querySelector("input[type=number]").value;
                                let text = document.getElementById("bewertungForm").querySelector("textarea").value;

                                if (name === "" || rating === "" || text === "") {
                                    return;
                                }

                                fetch('api.php?task=addEvaluation&rezept=<?= $rezept['ID'] ?>&name=' + name + '&rating=' + rating + '&text=' + text, {
                                    method: 'GET'
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        </script>
                    </div>
                    <script>
                        document.getElementById("bewertungName").addEventListener("input", () => {
                            document.getElementById("bewertungImage").src = "https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" + document.getElementById("bewertungName").value;
                        });
                    </script>
                </li>

                <?php
                foreach ($rezept['Bewertungen'] as $bewertung) {

                    $stars = "";
                    //sternchen und halbe sternchen ausgeben
                    for ($i = 0; $i < floor($bewertung['Bewertung']); $i++) {
                        $stars .= "<i class='fas fa-star'></i>";
                    }
                    if ($bewertung['Bewertung'] - floor($bewertung['Bewertung']) >= 0.5) {
                        $stars .= "<i class='fas fa-star-half-alt'></i>";
                    }
                    for ($i = 0; $i < 5 - ceil($bewertung['Bewertung']); $i++) {
                        $stars .= "<i class='far fa-star'></i>";
                    }


                    echo "<li id='bewertung{$bewertung['ID']}'>
                                <img src='{$bewertung['Image']}' alt='' width='50px' height='50px'>
                                <div>
                                    <h3>{$bewertung['Name']} - $stars</h3>
                                    <p>{$bewertung['Text']}</p>
                                </div>
                            </li>
                            
                            <script >
                            
                            document.getElementById('bewertung{$bewertung['ID']}').addEventListener('click', () => {
                                let bewertungsForm = new FormBuilder('Bewertung bearbeiten', (formData) => {
                                    fetch('api.php?task=editEvaluation&rezept={$bewertung['ID']}&name=' + formData['Name'] + '&rating=' + formData['Bewertung'] + '&text=' + formData['Text'], {
                                        method: 'GET'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }, () => {});
                                
                                bewertungsForm.addInputField('Name', 'Name', '{$bewertung['Name']}');
                                bewertungsForm.addNumberField('Bewertung', 0, 5, '{$bewertung['Bewertung']}');
                                bewertungsForm.addInputField('Text', 'Text', '{$bewertung['Text']}');
                                
                                bewertungsForm.addButton('Löschen', () => {
                                    fetch('api.php?task=deleteEvaluation&id={$bewertung['ID']}', {
                                        method: 'GET'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                });
                                
                                bewertungsForm.renderForm();
                            });
                            </script>
                            
                            ";
                }
                ?>
            </ul>
        </div>

    </div>
</div>
</body>
</html>
