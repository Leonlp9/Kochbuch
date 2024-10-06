<?php
require_once 'shared/global.php';
global $pdo;

if (isset($_POST['type'])) {
    if ($_POST['type'] === 'create') {
        $name = $_POST['name'];

        $sql = "INSERT INTO filterprofile (Name, Filter) VALUES (:name, '')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        echo 'success';
        die();
    }
}

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Filterprofile</title>

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
        .filterprofile {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
            gap: 20px;
        }

        .filterprofile .filter {
            background: var(--nonSelected);
            padding: 10px;
            border-radius: 10px;
        }

        .filterprofile .filter img {
            width: 100%;
            border-radius: 10px;
            user-select: none;
            pointer-events: none;
        }

        .filterprofile .filter h2 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
            font-size: 20px;
            text-transform: none;
            user-select: none;
            line-break: anywhere;
        }

        .filterprofil {

        }

        .filterprofil img {
            width: 100px;
            border-radius: 10px;
            user-select: none;
            pointer-events: none;
        }

        .zutaten {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<body>
<div class="nav-grid">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">

        <?php
        if (isset($_GET['id'])) { ?>

<!--                zurück knopf-->
                <a href="filterprofile.php" class="btn blue">Zurück</a>
                <br>
            <div class="filterprofil">
                <?php

                $sql = "SELECT * FROM filterprofile WHERE ID = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $_GET['id']);
                $stmt->execute();
                $filterprofile = $stmt->fetch();

				echo "<h2>" . $filterprofile['Name'] . "</h2>";
				echo "<img src='https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" . $filterprofile['Name'] . "' alt='Profilbild'>";

                ?>
            </div>
            <br>
<!--            zutaten-->
            <h2>Zutaten</h2>

            <div class="zutaten">
                <?php

                $sql = "SELECT * FROM zutaten order by Name";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $zutaten = $stmt->fetchAll();

                foreach ($zutaten as $zutat) {
                    echo "<div class='zutat'>";

					// Zeige das Bild der Zutat, falls vorhanden, oder ein Standardbild
					if (!empty($zutat['Image']) && file_exists("ingredientIcons/" . $zutat['Image'])) {
						echo "<img src='ingredientIcons/" . $zutat['Image'] . "' alt='" . $zutat['Name'] . "'>";
					} else {
						echo "<img src='ingredientIcons/default.svg' alt='Default Image'>";
					}

                    echo "<h3>" . $zutat['Name'] . "</h3>";
                    echo "</div>";
                }

                ?>


        <?php
        } else {
        ?>


            <h1>Filterprofile</h1>
            <br>
        <div class="filterprofile">
            <?php

            $sql = "SELECT * FROM filterprofile";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $filterprofile = $stmt->fetchAll();

            foreach ($filterprofile as $filter) {
                echo "<a href='filterprofile.php?id=" . $filter['ID'] . "' class='filter'>";
                echo "<img src='https://api.dicebear.com/9.x/bottts-neutral/svg?seed=" . $filter['Name'] . "' alt='Profilbild'>";
                echo "<h2>" . $filter['Name'] . "</h2>";
                echo "</a>";
            }
            ?>
        </div>

        <br>

        <div style="display: flex; justify-content: center; flex-direction: column; align-items: center;">

            <input type="text" id="filterName" placeholder="Name" style="text-transform: none;">
            <button class="btn blue" onclick="createFilterprofile()">Erstellen</button>

            <script>
                function createFilterprofile() {
                    let name = document.getElementById('filterName').value;
                    if (name === '') {
                        alert('Bitte geben Sie einen Namen ein');
                        return;
                    }

                    $.ajax({
                        url: 'filterprofile.php',
                        type: 'POST',
                        data: {
                            type: 'create',
                            name: name
                        },
                        success: function (data) {
                            if (data === 'success') {
                                location.reload();
                            } else {
                                alert(data);
                            }
                        }
                    });
                }
            </script>

        </div>

		<?php
		}
		?>

	</div>
</div>
</body>
<script src="script.js"></script>
</html>
