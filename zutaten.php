<?php
require_once 'shared/global.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {

        //wenn ein icon vorhanden ist, wird es gelöscht
        $stmt = $pdo->prepare("SELECT Image FROM zutaten WHERE ID = :id");
        $stmt->execute(['id' => $_POST['id']]);
        $image = $stmt->fetchColumn();
        if (file_exists("ingredientIcons/" . $image)) {
            unlink("ingredientIcons/" . $image);
        }

        $stmt = $pdo->prepare("DELETE FROM zutaten WHERE ID = :id");
        $stmt->execute(['id' => $_POST['id']]);

        die();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['id'], $_POST['name'], $_POST['unit'])) {
        $stmt = $pdo->prepare("UPDATE zutaten SET Name = :name, Image = :image, unit = :unit WHERE ID = :id");
        $stmt->execute([
            'name' => $_POST['name'],
            'unit' => $_POST['unit'],
            'id' => $_POST['id'],
            'image' => strtolower($_POST['name']) . ".svg"
        ]);
        if (isset($_FILES['icon']) && $_FILES['icon']['error'] === 0) {
            // Check if file is an image of type svg
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['icon']['tmp_name']);

            //bild bei ingredientIcons speichern mit dem namen der zutat, wenn schon vorhanden überschreiben
            if ($mime === 'image/svg+xml') {
                move_uploaded_file($_FILES['icon']['tmp_name'], "ingredientIcons/" . strtolower($_POST['name']) . ".svg");
            }
        }
    }
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
	<title>Zutaten</title>

    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo BASE_URL ?>/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL ?>/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL ?>/icons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo BASE_URL ?>/icons/site.webmanifest">
    <link rel="mask-icon" href="<?php echo BASE_URL ?>/icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="<?php echo BASE_URL ?>/icons/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Kochbuch">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Kochbuch">
    <meta name="msapplication-TileColor" content="#f6f6f6">
    <meta name="msapplication-config" content="<?php echo BASE_URL ?>/icons/browserconfig.xml">
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

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="script.js"></script>

	<link rel="stylesheet" href="style.css">

    <style>
        .zutaten {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            padding-bottom: 10px;
        }

        .zutat {
            padding: 10px;
            border-radius: 10px;
            background-color: var(--secondaryBackground);
            cursor: pointer;
            user-select: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .zutat:hover {
            background-color: var(--nonSelected);
        }

        .zutat img {
            width: 50px;
            height: 50px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<div class="nav-grid-content">
	<?php require_once 'shared/navbar.php'; ?>
	<div class="container">
		<h1>Zutaten</h1>
        <br>

        <p><i class="fas fa-info-circle"></i> Denke daran, alles, was hier geändert wird, wird auch jedem Rezepte geändert!</p>

<!--        alle zutaten anzeigen-->
        <h2>Alle Zutaten</h2>

        <div class="search">
            <input type="text" id="search" placeholder="Suche..." oninput="search()">
        </div>

        <div class="zutaten">
			<?php
            $stmt = $pdo->prepare("
                SELECT z.*, 
                       (SELECT COUNT(*) 
                        FROM rezepte 
                        WHERE JSON_CONTAINS(Zutaten_JSON, JSON_OBJECT('ID', z.ID))) as count 
                FROM zutaten z 
                ORDER BY z.Name
            ");
            $stmt->execute();
            $zutaten = $stmt->fetchAll();

            foreach ($zutaten as $zutat) {
                $id = $zutat['ID'];
                $count = $zutat['count'];
                ?>
                <div class='zutat' data-id='<?php echo $zutat['ID'] ?>'>
                    <?php
                    // Zeige das Bild der Zutat, falls vorhanden, oder ein Standardbild
                    if (!empty($zutat['Image']) && file_exists("ingredientIcons/" . $zutat['Image'])) {
                        echo "<img src='ingredientIcons/" . $zutat['Image'] . "' alt='" . $zutat['Name'] . "'>";
                    } else {
                        echo "<img src='ingredientIcons/default.svg' alt='Default Image'>";
                    }
                    ?>
                    <span style="font-weight: bold"><?php echo htmlspecialchars($zutat['Name']) ?></span>
                    <?php if (!empty($zutat['unit'])): ?>
                        <span>(<span class="unit"><?php echo htmlspecialchars($zutat['unit']) ?></span>)</span>
                    <?php endif; ?>
                    <?php
                    if ($count > 0) {
                        echo "<span>Vorkommen: <span class='count'>" . htmlspecialchars($count) . "</span></span>";
                    }
                    ?>
                </div>
                <?php
            }
            ?>

        </div>
	</div>
</div>
</body>
<script>
    const zutaten = document.querySelectorAll('.zutat');
    zutaten.forEach(zutat => {
        zutat.addEventListener('click', () => {
            const background = document.createElement('div');
            background.style.position = 'fixed';
            background.style.top = '0';
            background.style.left = '0';
            background.style.width = '100%';
            background.style.height = '100%';
            background.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            background.style.zIndex = '100';
            background.style.display = 'flex';
            background.style.justifyContent = 'center';
            background.style.alignItems = 'center';
            background.addEventListener('click', (e) => {
                if (e.target === background) {
                    background.remove();
                }
            });

            const popup = document.createElement('div');
            popup.style.backgroundColor = 'var(--secondaryBackground)';
            popup.style.padding = '20px';
            popup.style.borderRadius = '10px';
            popup.style.display = 'flex';
            popup.style.flexDirection = 'column';
            popup.style.alignItems = 'center';
            popup.style.position = 'relative';
            popup.style.zIndex = '101';
            popup.style.width = '50%';
            popup.style.maxWidth = '500px';
            popup.style.minWidth = '300px';
            popup.style.textAlign = 'center';
            popup.style.overflow = 'auto';
            popup.style.maxHeight = '80vh';
            popup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';

            const img = document.createElement('img');
            img.src = zutat.querySelector('img').src;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.marginBottom = '10px';
            popup.appendChild(img);

            const name = document.createElement('h2');
            name.textContent = zutat.querySelector('span').textContent;
            popup.appendChild(name);

            const count = zutat.querySelector('.count');
            if (count) {
                const countText = document.createElement('p');
                countText.textContent = 'Vorkommen: ' + count.textContent;
                popup.appendChild(countText);

                const button = document.createElement('button');
                button.textContent = 'Rezepte anzeigen';
                button.classList.add('btn', 'blue');
                button.addEventListener('click', () => {
                    window.location.href = 'search.php';
                });
                popup.appendChild(button);

                const breakLine = document.createElement('br');
                popup.appendChild(breakLine);

            }

            const changeName = document.createElement('input');
            changeName.type = 'text';
            changeName.style.textTransform = 'none';
            changeName.value = zutat.querySelector('span').textContent;
            popup.appendChild(changeName);

            const changeUnit = document.createElement('input');
            changeUnit.type = 'text';
            changeUnit.style.textTransform = 'none';
            changeUnit.value = zutat.querySelector('.unit').textContent;
            popup.appendChild(changeUnit);

            const changeIcon = document.createElement('label');
            changeIcon.classList.add('upload');
            changeIcon.innerHTML = '<i class="fas fa-upload"></i> Bild hochladen';
            const iconInput = document.createElement('input');
            iconInput.type = 'file';
            iconInput.name = 'icon';
            iconInput.accept = 'image/svg+xml';
            changeIcon.appendChild(iconInput);
            const preview = document.createElement('div');
            preview.classList.add('preview');
            changeIcon.appendChild(preview);
            iconInput.onchange = function() {
                preview.style.display = 'grid';
                preview.innerHTML = '';
                for (const file of this.files) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100%';
                        preview.appendChild(img);
                        preview.style.marginTop = '10px';
                    };
                    reader.readAsDataURL(file);
                }
            };
            popup.appendChild(changeIcon);

            const save = document.createElement('button');
            save.textContent = 'Speichern';
            save.classList.add('btn', 'green');
            save.addEventListener('click', () => {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', zutat.dataset.id);
                formData.append('name', changeName.value);
                formData.append('unit', changeUnit.value);
                if (iconInput.files.length > 0) {
                    formData.append('icon', iconInput.files[0]);
                }
                fetch('zutaten.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    window.location.reload();
                });
            });
            popup.appendChild(save);

            if (!count) {
                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Löschen';
                deleteButton.classList.add('btn', 'red');
                deleteButton.addEventListener('click', () => {
                    if (confirm('Möchtest du die Zutat wirklich löschen?')) {
                        fetch('zutaten.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                action: 'delete',
                                id: zutat.dataset.id
                            })
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
                popup.appendChild(deleteButton);
            }else {

                const label = document.createElement('label');
                label.textContent = 'Diese Zutat kann nicht gelöscht werden, da sie in Rezepten verwendet wird';
                label.style.color = 'red';
                label.style.textTransform = 'none';
                popup.appendChild(label);

                const deleteButton = document.createElement('button');
                deleteButton.textContent = 'Löschen';
                deleteButton.classList.add('btn', 'red');
                deleteButton.disabled = true;
                deleteButton.title = 'Diese Zutat kann nicht gelöscht werden, da sie in Rezepten verwendet wird';
                popup.appendChild(deleteButton);
            }

            background.appendChild(popup);
            document.body.appendChild(background);
        });
    });

    function search() {
        let search = document.getElementById('search').value.toLowerCase();

        let zutaten = document.getElementsByClassName('zutat');
        for (let zutat of zutaten) {
            let name = zutat.querySelector('span').textContent.toLowerCase();
            if (name.includes(search)) {
                zutat.style.display = 'flex';
            } else {
                zutat.style.display = 'none';
            }
        }
    }
</script>
</html>
