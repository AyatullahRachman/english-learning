<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>
        Welcome, <?= e(current_user_name()) ?>!
    </h1>

    <p>
        You are successfully logged in.
    </p>

    <p>
        Your role:
        <strong>
            <?= e(current_user_role()) ?>
        </strong>
    </p>

    <hr>

    <h2>
        English Learning Dashboard
    </h2>

    <p>
        Your learning journey starts here.
    </p>

    <a href="<?= BASE_URL ?>logout.php">
        Logout
    </a>

</body>

</html>