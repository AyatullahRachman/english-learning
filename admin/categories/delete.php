<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();


/*
|--------------------------------------------------------------------------
| Only POST Request Allowed
|--------------------------------------------------------------------------
*/

if (!is_post()) {

    http_response_code(405);

    exit('Method Not Allowed.');

}

verify_csrf_token();


/*
|--------------------------------------------------------------------------
| Get Category ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    exit('Invalid category ID.');

}


/*
|--------------------------------------------------------------------------
| Check Category Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, name
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
| Check Category Usage
|--------------------------------------------------------------------------
|
| A category may already be used by topics.
| We should not delete a category that is still being used.
|
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM topics
    WHERE category_id = ?
");

$stmt->execute([$id]);

$topicCount = (int) $stmt->fetchColumn();

if ($topicCount > 0) {

    exit(
        'This category cannot be deleted because it is still used by topics.'
    );

}


/*
|--------------------------------------------------------------------------
| Delete Category
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM categories
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(BASE_URL . 'admin/categories/');