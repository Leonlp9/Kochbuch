<?php
require_once 'shared/global.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    if ($_POST['type'] === 'add') {
		$name = $_POST['name'];
		$color = $_POST['color'];
        $stmt = $pdo->prepare('INSERT INTO kategorien (Name, ColorHex) VALUES (:name, :color)');
        $stmt->execute([
            'name' => $name,
            'color' => $color
        ]);
    }

    if ($_POST['type'] === 'edit') {
		$id = $_POST['id'];
		$name = $_POST['name'];
		$color = $_POST['color'];
        $stmt = $pdo->prepare('UPDATE kategorien SET Name = :name, ColorHex = :color WHERE ID = :id');
        $stmt->execute([
            'name' => $name,
            'color' => $color,
            'id' => $id
        ]);
    }

    if ($_POST['type'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM kategorien WHERE ID = :id');
        $stmt->execute([
            'id' => $id
        ]);
	}

    echo json_encode('success');
    exit();
}

?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Kategorien</title>

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
        .kategorien {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border-radius: 10px;
            scrollbar-width: none;
            justify-content: center;
        }

        .kategorie {
            width: clamp(150px, 20vw, 200px);
            height: clamp(150px, 20vw, 200px);
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
            transition: transform 0.2s, border-radius 0.2s;
        }

        .kategorie:hover {
            transform: scale(1.05);
            border-radius: 20px;
        }
    </style>
</head>
<body>
<div class="nav-grid">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">
		<h1>Kategorien</h1>
        <br>

        <button class="btn green" id="addKategorie">Kategorie hinzufügen</button>

        <br>
        <br>

        <div class="kategorien">
            <?php
            $stmt = $pdo->prepare('SELECT * FROM kategorien');
            $stmt->execute();
            $kategorien = $stmt->fetchAll();
            foreach ($kategorien as $kategorie) {
                ?>
                <div class="kategorie" data-id="<?php echo $kategorie['ID']; ?>" style="background-color: <?php echo $kategorie['ColorHex']; ?>">
                    <?php echo $kategorie['Name']; ?>
                </div>
                <?php
            }
            ?>
        </div>

	</div>
</div>
</body>
<script>
    let kategorien = document.querySelectorAll('.kategorie');
    kategorien.forEach(kategorie => {
        kategorie.addEventListener('click', () => {

            const background = document.createElement('div');
            background.style.position = 'fixed';
            background.style.top = '0';
            background.style.left = '0';
            background.style.width = '100%';
            background.style.height = '100%';
            background.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            background.style.zIndex = '1000';
            background.style.display = 'flex';
            background.style.justifyContent = 'center';
            background.style.alignItems = 'center';
            document.body.appendChild(background);

            const modal = document.createElement('div');
            modal.style.backgroundColor = 'white';
            modal.style.padding = '20px';
            modal.style.borderRadius = '10px';
            modal.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
            modal.style.zIndex = '1001';
            modal.style.display = 'flex';
            modal.style.flexDirection = 'column';
            modal.style.gap = '10px';
            modal.style.transition = 'transform 0.3s';
            background.appendChild(modal);

            const title = document.createElement('h2');
            title.innerText = kategorie.innerText;
            modal.appendChild(title);

            const name = document.createElement('input');
            name.type = 'text';
            name.value = kategorie.innerText;
            name.style.textTransform = 'none';
            modal.appendChild(name);

            const color = document.createElement('input');
            color.type = 'color';
            color.value = "#" + kategorie.style.backgroundColor.slice(4, -1).split(',').map(x => parseInt(x).toString(16).padStart(2, '0')).join('');
            modal.appendChild(color);

            const save = document.createElement('button');
            save.innerText = 'Speichern';
            save.classList.add('btn', 'green');
            save.style.cursor = 'pointer';
            save.addEventListener('click', () => {
                $.ajax({
                    url: 'kategorien.php',
                    type: 'POST',
                    data: {
                        type: 'edit',
                        id: kategorie.dataset.id,
                        name: name.value,
                        color: color.value
                    },
                    success: function (data) {
                        if (data === 'success') {
                            kategorie.innerText = name.value;
                            kategorie.style.backgroundColor = color.value;
                            background.remove();
                        }
                    }
                });
            });


            const del = document.createElement('button');
            del.innerText = 'Löschen';
            del.classList.add('btn', 'red');
            del.style.cursor = 'pointer';
            del.addEventListener('click', () => {
                $.ajax({
                    url: 'kategorien.php',
                    type: 'POST',
                    data: {
                        type: 'delete',
                        id: kategorie.dataset.id
                    },
                    success: function (data) {
                        if (data === 'success') {
                            kategorie.remove();
                            background.remove();
                        }
                    }
                });
            });


                // wenn kein child angeklickt wird dann wird das modal geschlossen
            background.addEventListener('click', (e) => {
                if (e.target === background) {
                    background.remove();
                }
            });


            modal.appendChild(save);
            modal.appendChild(del);

        });
    });

    document.getElementById('addKategorie').addEventListener('click', () => {
        const background = document.createElement('div');
        background.style.position = 'fixed';
        background.style.top = '0';
        background.style.left = '0';
        background.style.width = '100%';
        background.style.height = '100%';
        background.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        background.style.zIndex = '1000';
        background.style.display = 'flex';
        background.style.justifyContent = 'center';
        background.style.alignItems = 'center';
        document.body.appendChild(background);

        const modal = document.createElement('div');
        modal.style.backgroundColor = 'white';
        modal.style.padding = '20px';
        modal.style.borderRadius = '10px';
        modal.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
        modal.style.zIndex = '1001';
        modal.style.display = 'flex';
        modal.style.flexDirection = 'column';
        modal.style.gap = '10px';
        modal.style.transition = 'transform 0.3s';
        background.appendChild(modal);

        const title = document.createElement('h2');
        title.innerText = 'Kategorie hinzufügen';
        modal.appendChild(title);

        const name = document.createElement('input');
        name.type = 'text';
        name.placeholder = 'Name';
        name.style.textTransform = 'none';
        modal.appendChild(name);

        const color = document.createElement('input');
        color.type = 'color';
        color.value = '#000000';
        modal.appendChild(color);

        const save = document.createElement('button');
        save.innerText = 'Speichern';
        save.classList.add('btn', 'green');
        save.style.cursor = 'pointer';
        save.addEventListener('click', () => {
            $.ajax({
                url: 'kategorien.php',
                type: 'POST',
                data: {
                    type: 'add',
                    name: name.value,
                    color: color.value
                },
                success: function (data) {
                    if (data === 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        background.addEventListener('click', (e) => {
            if (e.target === background) {
                background.remove();
            }
        });

        modal.appendChild(save);
    });

</script>
<script src="script.js"></script>
</html>
