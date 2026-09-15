<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Get Lessons
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        lessons.id,
        lessons.title,
        lessons.slug,
        lessons.description,
        lessons.learning_objective,
        lessons.level,
        lessons.difficulty,
        lessons.estimated_minutes,
        lessons.order_number,
        lessons.is_active,
        lessons.created_at,
        lessons.updated_at,
        topics.name AS topic_name
    FROM lessons
    INNER JOIN topics
        ON topics.id = lessons.topic_id
    ORDER BY
        lessons.order_number ASC,
        lessons.id ASC
");

$lessons = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Difficulty Label
|--------------------------------------------------------------------------
*/

$difficultyLabels = [
    1 => 'Easy',
    2 => 'Medium',
    3 => 'Hard'
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lessons - <?= e(APP_NAME) ?></title>
</head>

<body>

    <h1>Lessons</h1>

    <p>
        <a href="<?= BASE_URL . 'admin/' ?>">
            Back to Admin Dashboard
        </a>
    </p>

    <p>
        <a href="<?= BASE_URL . 'admin/lessons/create.php' ?>">
            + Add Lesson
        </a>
    </p>

    <?php if (empty($lessons)): ?>

        <p>No lessons found.</p>

    <?php else: ?>

        <table border="1" cellpadding="8" cellspacing="0">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Topic</th>
                    <th>Title</th>
                    <th>Level</th>
                    <th>Difficulty</th>
                    <th>Estimated Time</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($lessons as $lesson): ?>

                    <tr>

                        <td>
                            <?= e($lesson['id']) ?>
                        </td>

                        <td>
                            <?= e($lesson['topic_name']) ?>
                        </td>

                        <td>
                            <?= e($lesson['title']) ?>
                        </td>

                        <td>
                            <?= e($lesson['level']) ?>
                        </td>

                        <td>
                            <?= e(
                                $difficultyLabels[$lesson['difficulty']]
                                ?? 'Unknown'
                            ) ?>
                        </td>

                        <td>
                            <?= e($lesson['estimated_minutes']) ?> min
                        </td>

                        <td>
                            <?= e($lesson['order_number']) ?>
                        </td>

                        <td>
                            <?php if ((int) $lesson['is_active'] === 1): ?>

                                Active

                            <?php else: ?>

                                Inactive

                            <?php endif; ?>
                        </td>

                        <td>
                            <?= e($lesson['created_at']) ?>
                        </td>

                        <td>

                            <a
                                href="<?= BASE_URL . 'admin/lessons/edit.php?id=' . e($lesson['id']) ?>"
                            >
                                Edit
                            </a>

                            |

                            <form
                                method="POST"
                                action="<?= BASE_URL . 'admin/lessons/delete.php' ?>"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this lesson?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($lesson['id']) ?>"
                                >

                                <button type="submit">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</body>

</html>