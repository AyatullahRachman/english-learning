<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Get Lesson ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    exit('Invalid lesson ID.');
}

/*
|--------------------------------------------------------------------------
| Get Lesson
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        topic_id,
        title,
        slug,
        description,
        learning_objective,
        level,
        difficulty,
        content,
        estimated_minutes,
        order_number,
        is_active
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
| Variables
|--------------------------------------------------------------------------
*/

$errors = [];

$topic_id = $lesson['topic_id'];
$title = $lesson['title'];
$description = $lesson['description'];
$learning_objective = $lesson['learning_objective'];
$level = $lesson['level'];
$difficulty = (int) $lesson['difficulty'];
$content = $lesson['content'];
$estimated_minutes = (int) $lesson['estimated_minutes'];
$order_number = (int) $lesson['order_number'];
$is_active = (int) $lesson['is_active'];

/*
|--------------------------------------------------------------------------
| Get Topics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT id, name, level
    FROM topics
    WHERE is_active = 1
    ORDER BY name ASC
");

$topics = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Process Form
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf_token();

    $topic_id = filter_input(
        INPUT_POST,
        'topic_id',
        FILTER_VALIDATE_INT
    );

    $title = trim($_POST['title'] ?? '');

    $description = trim(
        $_POST['description'] ?? ''
    );

    $learning_objective = trim(
        $_POST['learning_objective'] ?? ''
    );

    $level = trim(
        $_POST['level'] ?? ''
    );

    $difficulty = filter_input(
        INPUT_POST,
        'difficulty',
        FILTER_VALIDATE_INT
    );

    $content = trim(
        $_POST['content'] ?? ''
    );

    $estimated_minutes = filter_input(
        INPUT_POST,
        'estimated_minutes',
        FILTER_VALIDATE_INT
    );

    $order_number = filter_input(
        INPUT_POST,
        'order_number',
        FILTER_VALIDATE_INT
    );

    $is_active = isset($_POST['is_active'])
        ? 1
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (!$topic_id) {
        $errors[] = 'Please select a topic.';
    }

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if (strlen($title) > 150) {
        $errors[] = 'Title must not exceed 150 characters.';
    }

    $allowed_levels = [
        'A1',
        'A2',
        'B1',
        'B2',
        'C1',
        'C2'
    ];

    if (!in_array($level, $allowed_levels, true)) {
        $errors[] = 'Invalid level.';
    }

    if (
        $difficulty === false ||
        !in_array($difficulty, [1, 2, 3], true)
    ) {
        $errors[] = 'Invalid difficulty.';
    }

    if (
        $estimated_minutes === false ||
        $estimated_minutes < 1
    ) {
        $errors[] = 'Estimated minutes must be at least 1.';
    }

    if (
        $order_number === false ||
        $order_number < 0
    ) {
        $errors[] = 'Order number must be 0 or greater.';
    }

    /*
    |--------------------------------------------------------------------------
    | Check Topic
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM topics
            WHERE id = ?
            AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([
            $topic_id
        ]);

        if (!$stmt->fetch()) {
            $errors[] = 'Selected topic does not exist or is inactive.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Slug
    |--------------------------------------------------------------------------
    */

    $slug = '';

    if (empty($errors)) {

        $slug = strtolower($title);

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

        $slug = trim(
            $slug,
            '-'
        );

        if ($slug === '') {
            $errors[] = 'Unable to generate slug.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Slug
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM lessons
            WHERE slug = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->execute([
            $slug,
            $id
        ]);

        if ($stmt->fetch()) {
            $errors[] = 'A lesson with this title already exists.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Lesson
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE lessons
            SET
                topic_id = ?,
                title = ?,
                slug = ?,
                description = ?,
                learning_objective = ?,
                level = ?,
                difficulty = ?,
                content = ?,
                estimated_minutes = ?,
                order_number = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $topic_id,
            $title,
            $slug,
            $description !== ''
                ? $description
                : null,
            $learning_objective !== ''
                ? $learning_objective
                : null,
            $level,
            $difficulty,
            $content !== ''
                ? $content
                : null,
            $estimated_minutes,
            $order_number,
            $is_active,
            $id
        ]);

        redirect(
            BASE_URL . 'admin/lessons/'
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
        Edit Lesson - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Edit Lesson</h1>

    <p>
        <a href="<?= BASE_URL . 'admin/lessons/' ?>">
            Back to Lessons
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

    <form
        method="POST"
        action=""
    >

        <?= csrf_field() ?>

        <div>

            <label for="topic_id">
                Topic
            </label>

            <br>

            <select
                id="topic_id"
                name="topic_id"
                required
            >

                <option value="">
                    -- Select Topic --
                </option>

                <?php foreach ($topics as $topic): ?>

                    <option
                        value="<?= e($topic['id']) ?>"
                        <?= (string) $topic_id === (string) $topic['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($topic['name']) ?>
                        (<?= e($topic['level']) ?>)
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="title">
                Lesson Title
            </label>

            <br>

            <input
                type="text"
                id="title"
                name="title"
                value="<?= e($title) ?>"
                maxlength="150"
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
                rows="4"
            ><?= e($description) ?></textarea>

        </div>

        <br>

        <div>

            <label for="learning_objective">
                Learning Objective
            </label>

            <br>

            <textarea
                id="learning_objective"
                name="learning_objective"
                rows="5"
            ><?= e($learning_objective) ?></textarea>

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

                <?php foreach (
                    ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']
                    as $levelOption
                ): ?>

                    <option
                        value="<?= e($levelOption) ?>"
                        <?= $level === $levelOption
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($levelOption) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="difficulty">
                Difficulty
            </label>

            <br>

            <select
                id="difficulty"
                name="difficulty"
                required
            >

                <option
                    value="1"
                    <?= $difficulty === 1
                        ? 'selected'
                        : '' ?>
                >
                    Easy
                </option>

                <option
                    value="2"
                    <?= $difficulty === 2
                        ? 'selected'
                        : '' ?>
                >
                    Medium
                </option>

                <option
                    value="3"
                    <?= $difficulty === 3
                        ? 'selected'
                        : '' ?>
                >
                    Hard
                </option>

            </select>

        </div>

        <br>

        <div>

            <label for="content">
                Content
            </label>

            <br>

            <textarea
                id="content"
                name="content"
                rows="10"
            ><?= e($content) ?></textarea>

        </div>

        <br>

        <div>

            <label for="estimated_minutes">
                Estimated Minutes
            </label>

            <br>

            <input
                type="number"
                id="estimated_minutes"
                name="estimated_minutes"
                value="<?= e($estimated_minutes) ?>"
                min="1"
                required
            >

        </div>

        <br>

        <div>

            <label for="order_number">
                Order Number
            </label>

            <br>

            <input
                type="number"
                id="order_number"
                name="order_number"
                value="<?= e($order_number) ?>"
                min="0"
                required
            >

        </div>

        <br>

        <div>

            <label>

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= $is_active === 1
                        ? 'checked'
                        : '' ?>
                >

                Active

            </label>

        </div>

        <br>

        <button type="submit">
            Update Lesson
        </button>

        <a href="<?= BASE_URL . 'admin/lessons/' ?>">
            Cancel
        </a>

    </form>

</body>

</html>