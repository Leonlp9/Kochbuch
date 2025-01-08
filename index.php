<?php
require_once 'shared/global.php';
global $pdo;

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kochbuch</title>

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
        #titleImage {
            width: 100%;
            aspect-ratio: 4 / 3;
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            margin-top: 20px;
            max-height: 600px;
            transition: background-image 1s ease;

            display: flex;
            align-items: stretch;
            flex-direction: column;
            justify-content: flex-end;
            overflow: hidden;
        }

        #titleImage h2 {
            background: rgba(255, 255, 255, 0.5);
            padding: 10px;
            color: #1a1a1a;
            text-align: center;
            animation: fadeIn 1s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.5), 0 0 10px rgba(255, 255, 255, 0.5);

            border-radius: 10px;

            transition: padding 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            margin: 0 10px 10px;
        }

        #timeBar {
            height: 10px;
            background-color: var(--background);
            width: 0;
            animation: timeBar 5s linear infinite;
            border-radius: 10px 10px 0 0;
        }

        @keyframes timeBar {
            0% {
                width: 0;
                transform: translateY(100%);
            }
            10% {
                width: 10%;
                transform: translateY(0);
            }
            95% {
                width: 95%;
                transform: translateY(0);
            }
            100% {
                width: 100%;
                transform: translateY(100%);
            }
        }

        @keyframes fadeIn {
            from {
                color: rgba(255, 255, 255, 0);
                background: rgba(255, 255, 255, 0);
                transform: translateY(100%);
            }
            to {
                color: #1a1a1a;
                background: rgba(255, 255, 255, 0.5);
                transform: translateY(0);
            }
        }

        #titleImage:hover h2 {
            padding: 20px;
        }


        #lastAdded {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        #lastOpened {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        #today {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }


        #profiles {
            margin-bottom: 20px;
            overflow: scroll;
            display: flex;
            gap: 10px;
            border-radius: 10px;
            scrollbar-width: none;
        }

        #profiles div {
            width: 20%;
            aspect-ratio: 1 / 1;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            user-select: none;
            flex-shrink: 0;
            max-width: 175px;
        }

        @media (max-width: 768px) {


            #profiles div {
                width: 120px;
            }


        }

        #profiles div img {
            border-radius: 10px;
        }

        #random {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        #katetegorien {
            margin-bottom: 20px;
            overflow: scroll;
            display: flex;
            gap: 10px;
            border-radius: 10px;
            scrollbar-width: none;
        }

        #katetegorien div {
            width: 195px;
            height: 195px;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            user-select: none;
            flex-shrink: 0;
            color: #2c2c2c;
        }

        @media (max-width: 768px) {
            #lastAdded {
                grid-template-columns: 1fr 1fr;
            }

            #lastOpened {
                grid-template-columns: 1fr 1fr;
            }

            #today {
                grid-template-columns: 1fr 1fr;
            }

            #random {
                grid-template-columns: 1fr 1fr;
            }

            #profiles {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }

        .recipe {
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: background-color 0.5s ease;
        }

        .recipe img {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
        }

        .recipe h3 {
            margin: 0;
            padding: 10px;
            background-color: var(--secondaryBackground);
            color: var(--color);
            text-align: center;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .recipe:hover h3 {
            background-color: var(--nonSelected);
        }

    </style>

</head>
<body>
    <div class="nav-grid-content">
        <?php
        require_once 'shared/navbar.php';
        ?>
        <div class="container">

<!--            Einstellungen oben rechts-->
            <a style="position: absolute; top: 10px; right: 10px;" href="settings" class="mobile">
                <button id="settings">
                    <i class="fas fa-cog"></i>
                </button>
            </a>

            <h1>Willkommen im Kochbuch <i class="fas fa-utensils"></i></h1>

            <div id="titleImage"></div>

            <script>
                let recipes = [];

                fetch("api?task=search&random=true&search=")
                    .then(response => response.json())
                    .then(data => {
                        recipes = data;
                        update();
                    });

                function update() {
                    if (recipes.length > 0) {
                        let recipe = recipes[Math.floor(Math.random() * recipes.length)];
                        $("#titleImage").css("background-image", `url(${recipe.Image})`);
                        //onklick
                        $("#titleImage").click(() => {
                            window.location.href = `rezept?id=${recipe.rezepte_ID}`;
                        });

                        //delete all children
                        $("#titleImage").html("");

                        //add child with title
                        let title = $(`<h2>${recipe.Name}</h2>`);
                        $("#titleImage").append(title);

                        //time bar
                        let timeBar = $(`<div id="timeBar"></div>`);
                        $("#titleImage").append(timeBar);

                    }
                }

                setInterval(() => {
                    update();
                }, 5000);
            </script>

            <br>

            <h2>Heute steht an</h2>
            <div id="today"></div>

            <script>
                fetch("api?task=getKalender&date=" + new Date().toISOString().split("T")[0])
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(entry => {
                            let entryDiv = $(`<div class="recipe" data-id="${entry.rezepte_ID}"></div>`);

                            if (entry.Image === null) {
                                entryDiv.html(`
                                <h3>${entry.Text}</h3>
                                `);
                            }else{
                                entryDiv.html(`
                                <img src="${entry.Image}" alt="${entry.Name}">
                                <h3>${entry.Name}</h3>
                            `);
                            }

                            entryDiv.click(() => {
                                if (entry.Rezept_ID !== null){
                                    window.location.href = `rezept?id=${entry.Rezept_ID}`;
                                }
                            });
                            $("#today").append(entryDiv);
                        });

                        if (data.length === 0){
                            $("#today").html("<h3>Für heute ist nichts geplant</h3>");
                        }
                    });
            </script>

            <br>

            <h2>Filterprofile</h2>
            <div id="profiles" class="horizontalScrollBarJS"></div>
            <script>
                fetch("api?task=getFilterprofile")
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(profile => {
                            console.log(profile);
                            let profileDiv = $(`<div data-id="${profile.ID}"></div>`);
                            profileDiv.html(`
                                <img src="${profile.Image}" alt="${profile.Name}">
                                <h3>${profile.Name}</h3>
                            `);
                            profileDiv.click(() => {
                                window.location.href = `filterprofile?id=${profile.ID}`;
                            });
                            $("#profiles").append(profileDiv);
                        });
                    });
            </script>

            <br>

            <h2>Random</h2>
            <div id="random"></div>
            <button id="shake" class="btn green" onclick="shakeRandomRecipes()">Shake</button>

            <script>
                function shakeRandomRecipes() {
                    fetch("api?task=search&random=true&search=")
                        .then(response => response.json())
                        .then(data => {
                            $("#random").html("");
                            data.forEach(recipe => {
                                let recipeDiv = $(`<div class="recipe" data-id="${recipe.rezepte_ID}"></div>`);
                                recipeDiv.html(`
                                <img src="${recipe.Image}" alt="${recipe.Name}">
                                <h3>${recipe.Name}</h3>
                            `);
                                recipeDiv.click(() => {
                                    window.location.href = `rezept?id=${recipe.rezepte_ID}`;
                                });
                                $("#random").append(recipeDiv);
                            });
                        });
                }
                shakeRandomRecipes();
            </script>

            <br>
            <br>

            <h2>Kategorien</h2>
            <div id="katetegorien" class="horizontalScrollBarJS"></div>
            <script>
                fetch("api?task=getKategorien")
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(kategorie => {
                            let kategorieDiv = $(`<div class="kategorie" data-id="${kategorie.ID}"></div>`);
                            kategorieDiv.html(`
                                <h3>${kategorie.Name}</h3>
                            `);
                            kategorieDiv.click(() => {
                                window.location.href = `search?kategorie=${kategorie.ID}`;
                            });
                            kategorieDiv.css("background-color", kategorie.ColorHex);
                            $("#katetegorien").append(kategorieDiv);
                        });
                    });
            </script>

            <br>

            <h2>Zuletzt hinzugefügt</h2>
            <div id="lastAdded"></div>

            <script>
                fetch("api?task=search&neueste=true&search=")
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(recipe => {
                            let recipeDiv = $(`<div class="recipe" data-id="${recipe.rezepte_ID}"></div>`);
                            recipeDiv.html(`
                                <img src="${recipe.Image}" alt="${recipe.Name}">
                                <h3>${recipe.Name}</h3>
                            `);
                            recipeDiv.click(() => {
                                window.location.href = `rezept?id=${recipe.rezepte_ID}`;
                            });
                            $("#lastAdded").append(recipeDiv);
                        });
                    });
            </script>

            <br>

            <h2>Zuletzt aufgerufen</h2>
            <div id="lastOpened"></div>

            <script>
                fetch("api?task=search&last_visit=true&search=")
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(recipe => {
                            let recipeDiv = $(`<div class="recipe" data-id="${recipe.rezepte_ID}"></div>`);
                            recipeDiv.html(`
                                <img src="${recipe.Image}" alt="${recipe.Name}">
                                <h3>${recipe.Name}</h3>
                            `);
                            recipeDiv.click(() => {
                                window.location.href = `rezept?id=${recipe.rezepte_ID}`;
                            });
                            $("#lastOpened").append(recipeDiv);
                        });
                    });

            </script>
        </div>
    </div>
</body>
</html>
