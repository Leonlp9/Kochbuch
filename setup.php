<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = [
    'database' => 'kochbuch',
    'username' => 'root',
    'password' => '',
    'base_url' => 'http://localhost/Kochbuch/',
    'gemini_token' => '',
    'bring_email' => '',
    'bring_password' => '',
];

if (file_exists('config.ini')) {
    $config = array_merge($config, parse_ini_file('config.ini'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['database'] = $_POST['database'];
    $config['username'] = $_POST['username'];
    $config['password'] = $_POST['password'];
    $config['base_url'] = $_POST['base_url'];
    $config['gemini_token'] = $_POST['gemini_token'];
    $config['bring_email'] = $_POST['bring_email'];
    $config['bring_password'] = $_POST['bring_password'];

    $configString = '';
    foreach ($config as $key => $value) {
        $configString .= "$key = \"$value\"\n";
    }

    file_put_contents('config.ini', $configString);

    header('Location: ' . str_replace('localhost', $_SERVER['HTTP_HOST'], $config['base_url']));
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
            padding: 10px;
            background-color: var(--secondaryBackground);
            color: var(--color);
            border: none;
            border-radius: 5px;
            outline: none;
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

    <h2>Database</h2>

    <label for="database">Database Name</label>
    <input type="text" name="database" id="database" required value="<?php echo $config['database']; ?>">
    <label for="username">Database Username</label>
    <input type="text" name="username" id="username" required value="<?php echo $config['username']; ?>">
    <label for="password">Database Password</label>
    <input type="password" name="password" id="password" value="<?php echo $config['password']; ?>">

    <h2>Base URL</h2>

    <label for="base_url">Base URL</label>
    <input type="text" name="base_url" id="base_url" required value="<?php echo $config['base_url']; ?>">

    <h2>Gemini</h2>

    <label for="gemini_token">Gemini Token</label>
    <input type="text" name="gemini_token" id="gemini_token" value="<?php echo $config['gemini_token']; ?>">

    <h2>Bring!</h2>

    <label for="bring_email">Email</label>
    <input type="text" name="bring_email" id="bring_email" value="<?php echo $config['bring_email']; ?>">
    <label for="bring_password">Password</label>
    <input type="password" name="bring_password" id="bring_password" value="<?php echo $config['bring_password']; ?>">

    <h2>Save</h2>
    <button type="submit">Save</button>
</form>
</body>
</html>
