<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = [
    'username' => 'root',
    'password' => '',
    'base_url' => 'http://localhost/Kochbuch/',
];

if (!file_exists('config.ini')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $config['username'] = $_POST['username'];
        $config['password'] = $_POST['password'];
        $config['base_url'] = $_POST['base_url'];

        $configString = '';
        foreach ($config as $key => $value) {
            $configString .= "$key = \"$value\"\n";
        }

        file_put_contents('config.ini', $configString);
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
    <title>Setup</title>

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--color);
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
            overflow: auto;
        }

        h1, h2, h3, h4, h5, h6 {
            margin: 20px 0 10px;
            padding: 0;
            font-weight: bold;
        }

        h1 {
            font-size: 36px;
            border-bottom: 1px solid var(--nonSelected);
            text-align: left;
        }

        form {
            display: grid;
            gap: 10px;
        }

        label {
            display: grid;
        }

        input {
            display: grid;
        }

        button {
            display: grid;
            padding: 10px;
            background-color: var(--secondaryBackground);
            color: var(--color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<h1>Setup</h1>
<form action="setup.php" method="post">
    <label for="username">Database Username</label>
    <input type="text" name="username" id="username" required value="<?php echo $config['username']; ?>">
    <label for="password">Database Password</label>
    <input type="password" name="password" id="password" value="<?php echo $config['password']; ?>">
    <label for="base_url">Base URL</label>
    <input type="text" name="base_url" id="base_url" required value="<?php echo $config['base_url']; ?>">
    <button type="submit">Save</button>
</form>
</body>
</html>
