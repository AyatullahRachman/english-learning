<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$errors = [];


/*
|--------------------------------------------------------------------------
| Get Category ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {

    exit('Invalid category ID.');

}


/*
|--------------------------------------------------------------------------
| Get Existing Category
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        slug,
        description
    FROM categories
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$category = $stmt->fetch();

if (!$category) {

    exit('Category not found.');

}


/*
|--------------------------------------------------------------------------
| Default Form Values
|--------------------------------------------------------------------------
*/

$name = $category['name'];
$description = $category['description'] ?? '';


/*
|--------------------------------------------------------------------------
| Process Form
|--------------------------------------------------------------------------
*/

if (is_post()) {
    
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $errors[] = 'Category name is required.';

    } elseif (strlen($name) > 100) {

        $errors[] = 'Category name must not exceed 100 characters.';

    }


    /*
    |--------------------------------------------------------------------------
    | Generate Slug
    |--------------------------------------------------------------------------
    */

    $slug = strtolower($name);

    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    $slug = trim($slug, '-');


    if ($slug === '') {

        $errors[] = 'Category name cannot generate a valid slug.';

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
            FROM categories
            WHERE slug = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->execute([
            $slug,
            $id
        ]);

        if ($stmt->fetch()) {

            $errors[] = 'This category already exists.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE categories
            SET
                name = ?,
                slug = ?,
                description = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $slug,
            $description !== '' ? $description : null,
            $id
        ]);

        redirect(BASE_URL . 'admin/categories/');

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
        Edit Category - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Edit Category</h1>


    <p>

        <a href="<?= BASE_URL ?>admin/categories/">
            ← Back to Categories
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

            <label for="name">
                Category Name
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
            Update Category
        </button>

    </form>

</body>

</html>