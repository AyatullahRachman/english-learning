<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();


/*
|--------------------------------------------------------------------------
| Only Allow POST
|--------------------------------------------------------------------------
*/

if (!is_post()) {

    http_response_code(405);

    exit('Method Not Allowed.');

}


/*
|--------------------------------------------------------------------------
| Verify CSRF Token
|--------------------------------------------------------------------------
*/

verify_csrf_token();


/*
|--------------------------------------------------------------------------
| Get Topic ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    exit('Invalid topic ID.');

}


/*
|--------------------------------------------------------------------------
| Check Topic Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name
    FROM topics
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$topic = $stmt->fetch();

if (!$topic) {

    exit('Topic not found.');

}


/*
|--------------------------------------------------------------------------
| Check Topic Usage
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM lessons
    WHERE topic_id = ?
");

$stmt->execute([$id]);

$lessonCount = (int) $stmt->fetchColumn();

if ($lessonCount > 0) {

    exit(
        'This topic cannot be deleted because it is still used by lessons.'
    );

}


/*
|--------------------------------------------------------------------------
| Delete Topic
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM topics
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(BASE_URL . 'admin/topics/');