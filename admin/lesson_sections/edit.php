<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Get Section ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    exit('Invalid lesson section ID.');
}

/*
|--------------------------------------------------------------------------
| Section Types
|--------------------------------------------------------------------------
*/

$sectionTypes = [
    'explanation' => 'Explanation',
    'example'     => 'Example',
    'tip'         => 'Tip',
    'note'        => 'Note',
    'summary'     => 'Summary'
];

/*
|--------------------------------------------------------------------------
| Get Lesson Section
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        lesson_id,
        section_type,
        title,
        content,
        order_number
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
| Default Values
|--------------------------------------------------------------------------
*/

$lessonId = $section['lesson_id'];
$sectionType = $section['section_type'];
$title = $section['title'] ?? '';
$content = $section['content'];
$orderNumber = $section['order_number'];

$errors = [];

/*
|--------------------------------------------------------------------------
| Get Active Lessons
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        title,
        level
    FROM lessons
    WHERE is_active = 1
    ORDER BY
        title ASC
");

$lessons = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf_token();

    $lessonId = filter_input(
        INPUT_POST,
        'lesson_id',
        FILTER_VALIDATE_INT
    );

    $sectionType = trim(
        $_POST['section_type'] ?? ''
    );

    $title = trim(
        $_POST['title'] ?? ''
    );

    $content = trim(
        $_POST['content'] ?? ''
    );

    $orderNumber = filter_input(
        INPUT_POST,
        'order_number',
        FILTER_VALIDATE_INT
    );

    /*
    |--------------------------------------------------------------------------
    | Validate Lesson
    |--------------------------------------------------------------------------
    */

    if (!$lessonId) {

        $errors[] = 'Please select a lesson.';

    } else {

        $stmt = $pdo->prepare("
            SELECT id
            FROM lessons
            WHERE id = ?
            AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$lessonId]);

        if (!$stmt->fetch()) {

            $errors[] = 'Selected lesson is not available.';

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Section Type
    |--------------------------------------------------------------------------
    */

    if (!array_key_exists($sectionType, $sectionTypes)) {

        $errors[] = 'Invalid section type.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Title
    |--------------------------------------------------------------------------
    */

    if (strlen($title) > 150) {

        $errors[] = 'Title must not exceed 150 characters.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Content
    |--------------------------------------------------------------------------
    */

    if ($content === '') {

        $errors[] = 'Content is required.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Order Number
    |--------------------------------------------------------------------------
    */

    if (
        $orderNumber === false ||
        $orderNumber === null ||
        $orderNumber < 0
    ) {

        $errors[] = 'Order number must be 0 or greater.';

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE lesson_sections
            SET
                lesson_id = ?,
                section_type = ?,
                title = ?,
                content = ?,
                order_number = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $lessonId,
            $sectionType,
            $title !== '' ? $title : null,
            $content,
            $orderNumber,
            $id
        ]);

        redirect(
            BASE_URL . 'admin/lesson_sections/'
        );
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
        Edit Lesson Section - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Edit Lesson Section</h1>

    <p>
        <a href="<?= BASE_URL . 'admin/lesson_sections/' ?>">
            Back to Lesson Sections
        </a>
    </p>

    <?php if (!empty($errors)): ?>

        <div>

            <strong>Please fix the following:</strong>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <form method="POST">

        <?= csrf_field() ?>

        <div>

            <label for="lesson_id">
                Lesson
            </label>

            <br>

            <select
                name="lesson_id"
                id="lesson_id"
                required
            >

                <option value="">
                    -- Select Lesson --
                </option>

                <?php foreach ($lessons as $lesson): ?>

                    <option
                        value="<?= e($lesson['id']) ?>"
                        <?= (string) $lessonId === (string) $lesson['id']
                            ? 'selected'
                            : '' ?>
                    >

                        <?= e($lesson['title']) ?>

                        -
                        <?= e($lesson['level']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="section_type">
                Section Type
            </label>

            <br>

            <select
                name="section_type"
                id="section_type"
                required
            >

                <?php foreach ($sectionTypes as $value => $label): ?>

                    <option
                        value="<?= e($value) ?>"
                        <?= $sectionType === $value
                            ? 'selected'
                            : '' ?>
                    >

                        <?= e($label) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="title">
                Section Title
            </label>

            <br>

            <input
                type="text"
                name="title"
                id="title"
                maxlength="150"
                value="<?= e($title) ?>"
            >

        </div>

        <br>

        <div>

            <label for="content">
                Content
            </label>

            <br>

            <textarea
                name="content"
                id="content"
                rows="12"
                cols="80"
                required
            ><?= e($content) ?></textarea>

        </div>

        <br>

        <div>

            <label for="order_number">
                Order Number
            </label>

            <br>

            <input
                type="number"
                name="order_number"
                id="order_number"
                min="0"
                value="<?= e($orderNumber) ?>"
                required
            >

        </div>

        <br>

        <button type="submit">
            Update Lesson Section
        </button>

        <a href="<?= BASE_URL . 'admin/lessons_sections/' ?>">
            Cancel
        </a>

    </form>

</body>

</html>