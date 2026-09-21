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

    exit('Invalid question ID.');
}

/*
|--------------------------------------------------------------------------
| Check Question
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        question
    FROM questions
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$question = $stmt->fetch();

if (!$question) {

    exit('Question not found.');
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Delete Options
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM question_options
        WHERE question_id = ?
    ");

    $stmt->execute([$id]);

    /*
    |--------------------------------------------------------------------------
    | Delete Question
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        DELETE FROM questions
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit(
        'Failed to delete question.'
    );
}

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(
    BASE_URL . 'admin/questions/'
);