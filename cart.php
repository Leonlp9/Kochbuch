<?php

require_once 'shared/global.php';
global $pdo;

//create a shopping table if it does not exist
$einkaufsliste = "CREATE TABLE IF NOT EXISTS einkaufsliste (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Zutat_ID INT NOT NULL,
    Item VARCHAR(255),
    Menge INT NOT NULL,
    Einheit VARCHAR(255) NOT NULL
)";
$pdo->exec($einkaufsliste);

//http://localhost:63342/Kochbuch/cart.php?rezept=26
//add all ingredients from a recipe to the shopping list

if (isset($_GET['rezept'])) {
    $rezeptID = $_GET['rezept'];

    $stmt = $pdo->prepare("SELECT * FROM rezepte WHERE ID = ?");
    $stmt->execute([$rezeptID]);
    $rezept = $stmt->fetch();
    $zutaten = json_decode($rezept['Zutaten_JSON'], true);

    foreach ($zutaten as $zutat) {
        $stmt = $pdo->prepare("SELECT * FROM zutaten WHERE ID = ?");
        $stmt->execute([$zutat['ID']]);
        $zutatDB = $stmt->fetch();

        $menge = $zutat['Menge'];
        $einheit = $zutatDB['unit'];
        $item = $zutatDB['Name'];

        //wenn schon vorhanden, dann erhöhe die Menge
        $stmt = $pdo->prepare("SELECT * FROM einkaufsliste WHERE Zutat_ID = ?");
        $stmt->execute([$zutat['ID']]);
        $itemDB = $stmt->fetch();

        if ($itemDB) {
            $menge += $itemDB['Menge'];
            $stmt = $pdo->prepare("UPDATE einkaufsliste SET Menge = ? WHERE Zutat_ID = ?");
            $stmt->execute([$menge, $zutat['ID']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO einkaufsliste (Zutat_ID, Item, Menge, Einheit) VALUES (?, ?, ?, ?)");
            $stmt->execute([$zutat['ID'], $item, $menge, $einheit]);
        }
    }

    header("Location: cart.php");
}

//delete an item from the shopping list
if (isset($_GET['delete'])) {
	header('Content-Type: application/json');
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM einkaufsliste WHERE ID = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);

    die();
}

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

    <style>

        .cartAdd {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            font-size: 20px;
            background-color: var(--nonSelected);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .cartAdd {
                bottom: 60px;
                right: 10px;
            }
        }

        .cart {
            overflow-x: scroll;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--darkerGreen);
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: var(--background);
            color: #1e1e1e;
        }

        tr td {
            background-color: #ffffff;

            word-break: break-word;
            line-break: anywhere;
            hyphens: auto;
        }

        tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        tr:hover td{
            background-color: #ddd;
        }

        img {
            width: 50px;
            height: 50px;
            min-width: 50px;
            min-height: 50px;
        }

        td {
            min-width: 50px;
        }

    </style>
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
				<?php
				$stmt = $pdo->prepare("SELECT * FROM einkaufsliste");
				$stmt->execute();
				$einkaufsliste = $stmt->fetchAll();

				foreach ($einkaufsliste as $item) {
					$stmt = $pdo->prepare("SELECT * FROM zutaten WHERE ID = ?");
					$stmt->execute([$item['Zutat_ID']]);
					$zutat = $stmt->fetch();

					echo "<tr class='draggable' data-id='" . $item['ID'] . "'>";

					if (file_exists("ingredientIcons/" . $zutat['Image'])) {
						echo "<td><img src='ingredientIcons/" . $zutat['Image'] . "' style='width: 50px; height: 50px;'></td>";
					} else {
						echo "<td><img src='ingredientIcons/default.svg' style='width: 50px; height: 50px;'></td>";
					}

					echo "<td>" . $item['Item'] . "</td>";
					echo "<td>" . $item['Menge'] . "</td>";
					echo "<td>" . $item['Einheit'] . "</td>";
					echo "</tr>";
				}
				?>
            </table>
        </div>
    </div>
</div>

<style>
    .draggable {
        cursor: e-resize;
    }
</style>

<script>
    $(function() {
        function handleDrag(event, ui) {
            var parentWidth = $(this).parent().width();
            var itemLeft = ui.position.left;
            var opacity = 1 - Math.abs(itemLeft) / parentWidth;

            //den childs die opacity anpassen
            $(this).children().css("opacity", opacity);
        }

        function handleStop(event, ui) {
            var itemId = ui.helper.data("id");
            var parentWidth = $(this).parent().width();
            var itemLeft = ui.position.left;

            if (Math.abs(itemLeft) > $(this).width() / 1.75) {
                ui.helper.animate({ left: (itemLeft < 0 ? "-" : "+") + parentWidth + "px" }, "fast", function() {
                    $.ajax({
                        url: "cart.php?delete=true",
                        type: "POST",
                        data: { id: itemId },
                        success: function(response) {
                            ui.helper.remove();
                        }
                    });
                });
            } else {
                ui.helper.animate({ left: "0px", opacity: 1 }, "fast");
                //opacity wieder auf 1 setzen
                $(this).children().css("opacity", 1);
            }
        }

        function handleTouchStart(event) {
            var touch = event.originalEvent.touches[0];
            $(this).data("touchX", touch.pageX);
            $(this).data("touchY", touch.pageY);
            touchStartX = touch.pageX;
            startMoveElement = false;
        }

        function handleTouchMove(event) {
            var touch = event.originalEvent.touches[0];

            if (Math.abs(touchStartX - touch.pageX) > 40 || startMoveElement) {
                $(this).css("left", "-" + (touchStartX - touch.pageX) + "px");
                startMoveElement = true;
            } else {
                $(this).css("left", "0px");

                var scrollY = $(window).scrollTop();
                var touchY = $(this).data("touchY");
                var deltaY = touch.pageY - touchY;
                $(window).scrollTop(scrollY - deltaY);

                var velocity = deltaY * 0.5;
                var deceleration = 0.95;

                function continueScrolling() {
                    if (Math.abs(velocity) > 0.5 && !startMoveElement) {
                        scrollY = $(window).scrollTop();
                        $(window).scrollTop(scrollY - velocity);
                        velocity *= deceleration;
                        requestAnimationFrame(continueScrolling);
                    }
                }

                continueScrolling();
            }
        }

        function handleTouchEnd(event) {
            var deltaX = parseInt($(this).css("left"));
            var parentWidth = $(this).parent().width();
            var itemId = $(this).data("id");

            if (Math.abs(touchStartX - touch.pageX) > 25) {
                if (Math.abs(deltaX) > $(this).width() / 1.75) {
                    $(this).animate({left: (deltaX < 0 ? "-" : "+") + parentWidth + "px"}, "fast", function () {
                        $.ajax({
                            url: "cart.php?delete=true",
                            type: "POST",
                            data: {id: itemId},
                            success: function (response) {
                                $(this).remove();
                            }.bind(this)
                        });
                    });
                } else {
                    $(this).animate({left: "0px", opacity: 1}, "fast");
                }
            }
        }

        $(".draggable").draggable({
            axis: "x",
            drag: handleDrag,
            stop: handleStop
        });

        let touchStartX = 0;
        let startMoveElement = false;

        $(".draggable").on("touchstart", handleTouchStart);
        $(".draggable").on("touchmove", handleTouchMove);
        $(".draggable").on("touchend", handleTouchEnd);
    });
</script>


</body>
<script src="script.js"></script>
</html>
