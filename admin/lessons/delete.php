<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Only POST is allowed
|--------------------------------------------------------------------------
*/

if (!is_post()) {

    http_response_code(405);

    exit('Method Not Allowed.');
}

/*
|--------------------------------------------------------------------------
| Verify CSRF
|--------------------------------------------------------------------------
*/

verify_csrf_token();

/*
|--------------------------------------------------------------------------
| Get Lesson ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    exit('Invalid lesson ID.');
}

/*
|--------------------------------------------------------------------------
| Check Lesson
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, title
    FROM lessons
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$lesson = $stmt->fetch();

if (!$lesson) {
    exit('Lesson not found.');
}

/*
|--------------------------------------------------------------------------
| Check Lesson Sections
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM lesson_sections
    WHERE lesson_id = ?
");

$stmt->execute([$id]);

$sectionCount = (int) $stmt->fetchColumn();

if ($sectionCount > 0) {

    exit(
        'This lesson cannot be deleted because it still has lesson sections.'
    );
}

/*
|--------------------------------------------------------------------------
| Delete Lesson
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM lessons
    WHERE id = ?
");

$stmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(
    BASE_URL . 'admin/lessons/'
);