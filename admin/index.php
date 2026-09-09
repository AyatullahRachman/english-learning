<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

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
        Admin Dashboard - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>
        Admin Dashboard
    </h1>

    <p>
        Welcome,
        <?= e(current_user_name()) ?>
    </p>

    <hr>

    <h2>
        Content Management
    </h2>

    <ul>

        <li>
            <a href="<?= BASE_URL . 'admin/categories/' ?>">
                Categories
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL . 'admin/topics/' ?>">
                Topics
            </a>
        </li>

        <li>
            <a href="#">
                Lessons
            </a>
        </li>

        <li>
            <a href="#">
                Questions
            </a>
        </li>

        <li>
            <a href="#">
                Vocabulary
            </a>
        </li>

        <li>
            <a href="#">
                Tests
            </a>
        </li>

    </ul>

    <hr>

    <a href="<?= BASE_URL ?>dashboard/">
        Student Dashboard
    </a>

    <br>

    <a href="<?= BASE_URL ?>logout.php">
        Logout
    </a>

</body>

</html>