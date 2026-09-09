<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$errors = [];


/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        name
    FROM categories
    ORDER BY name ASC
");

$categories = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Default Form Values
|--------------------------------------------------------------------------
*/

$category_id = '';
$name = '';
$level = '';
$description = '';


/*
|--------------------------------------------------------------------------
| Process Form
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf_token();

    $category_id = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );

    $name = trim($_POST['name'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $description = trim($_POST['description'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validate Category
    |--------------------------------------------------------------------------
    */

    if (!$category_id) {

        $errors[] = 'Category is required.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$category_id]);

        if (!$stmt->fetch()) {

            $errors[] = 'Selected category does not exist.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Name
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $errors[] = 'Topic name is required.';

    } elseif (strlen($name) > 100) {

        $errors[] = 'Topic name must not exceed 100 characters.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Level
    |--------------------------------------------------------------------------
    */

    $allowed_levels = [
        'A1',
        'A2',
        'B1',
        'B2',
        'C1',
        'C2'
    ];

    if ($level === '') {

        $errors[] = 'Level is required.';

    } elseif (!in_array($level, $allowed_levels, true)) {

        $errors[] = 'Invalid level selected.';

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Slug
    |--------------------------------------------------------------------------
    */

    $slug = strtolower($name);

    $slug = preg_replace(
        '/[^a-z0-9]+/',
        '-',
        $slug
    );

    $slug = trim($slug, '-');


    if ($slug === '') {

        $errors[] = 'Topic name cannot generate a valid slug.';

    } elseif (strlen($slug) > 100) {

        $errors[] = 'Generated slug must not exceed 100 characters.';

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Slug
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM topics
            WHERE slug = ?
            LIMIT 1
        ");

        $stmt->execute([$slug]);

        if ($stmt->fetch()) {

            $errors[] = 'This topic already exists.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Topic
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO topics (
                category_id,
                name,
                slug,
                level,
                description
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $category_id,
            $name,
            $slug,
            $level,
            $description !== '' ? $description : null
        ]);

        redirect(BASE_URL . 'admin/topics/');

    }

}

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
        Add Topic - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Add Topic</h1>


    <p>

        <a href="<?= BASE_URL ?>admin/topics/">
            ← Back to Topics
        </a>

    </p>


    <?php if (!empty($errors)): ?>

        <div>

            <?php foreach ($errors as $error): ?>

                <p>
                    <?= e($error) ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <?= csrf_field() ?>


        <div>

            <label for="category_id">
                Category
            </label>

            <br>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <option value="">
                    -- Select Category --
                </option>

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?= e($category['id']) ?>"
                        <?= (string) $category_id === (string) $category['id'] ? 'selected' : '' ?>
                    >
                        <?= e($category['name']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <br>


        <div>

            <label for="name">
                Topic Name
            </label>

            <br>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= e($name) ?>"
                maxlength="100"
                required
            >

        </div>


        <br>


        <div>

            <label for="level">
                Level
            </label>

            <br>

            <select
                id="level"
                name="level"
                required
            >
                <option value="">
                    -- Select Level --
                </option>

                <option
                    value="A1"
                    <?= strtolower($level) === 'A1' ? 'selected' : '' ?>
                >
                    A1
                </option>

                <option
                    value="A2"
                    <?= strtolower($level) === 'A2' ? 'selected' : '' ?>
                >
                    A2
                </option>

                <option
                    value="B1"
                    <?= strtolower($level) === 'B1' ? 'selected' : '' ?>
                >
                    B1
                </option>

                <option 
                    value="B2"
                    <?= strtolower($level) === 'B2' ? 'selected' : '' ?>
                >
                    B2
                </option>
                
                <option 
                    value="C1"
                    <?= strtolower($level) === 'C1' ? 'selected' : '' ?>
                >
                    C1
                </option>
                
                <option 
                    value="C2"
                    <?= strtolower($level) === 'C2' ? 'selected' : '' ?>
                >
                    C2
                </option>
                
            </select>

        </div>


        <br>


        <div>

            <label for="description">
                Description
            </label>

            <br>

            <textarea
                id="description"
                name="description"
                rows="5"
                cols="50"
            ><?= e($description) ?></textarea>

        </div>


        <br>


        <button type="submit">
            Add Topic
        </button>

    </form>

</body>

</html>