<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Only POST
|--------------------------------------------------------------------------
*/

if (!is_post()) {

    http_response_code(405);

    exit('Method Not Allowed.');
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

verify_csrf_token();

/*
|--------------------------------------------------------------------------
| Get ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    exit('Invalid lesson section ID.');
}

/*
|--------------------------------------------------------------------------
| Check Section
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        lesson_id,
        title
    FROM lesson_sections
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$section = $stmt->fetch();

if (!$section) {

    exit('Lesson section not found.');
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM lesson_sections
    WHERE id = ?
");

$stmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(
    BASE_URL . 'admin/lesson_sections/'
);