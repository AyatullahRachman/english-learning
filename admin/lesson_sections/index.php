<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Get Lesson Sections
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        lesson_sections.id,
        lesson_sections.lesson_id,
        lesson_sections.section_type,
        lesson_sections.title,
        lesson_sections.content,
        lesson_sections.order_number,
        lessons.title AS lesson_title
    FROM lesson_sections
    INNER JOIN lessons
        ON lessons.id = lesson_sections.lesson_id
    ORDER BY
        lessons.title ASC,
        lesson_sections.order_number ASC,
        lesson_sections.id ASC
");

$sections = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Section Type Labels
|--------------------------------------------------------------------------
*/

$sectionTypeLabels = [
    'explanation' => 'Explanation',
    'example'     => 'Example',
    'tip'         => 'Tip',
    'note'        => 'Note',
    'summary'     => 'Summary'
];

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
        Lesson Sections - <?= e(APP_NAME) ?>
    </title>
    
    <link
        rel="stylesheet"
        href="<?= BASE_URL . 'assets/css/style.css' ?>"
</head>

<body>

    <h1>Lesson Sections</h1>

    <p>
        <a href="<?= BASE_URL . 'admin/' ?>">
            Back to Admin Dashboard
        </a>
    </p>

    <p>
        <a href="<?= BASE_URL . 'admin/lesson_sections/create.php' ?>">
            + Add Lesson Section
        </a>
    </p>

    <?php if (empty($sections)): ?>

        <p>No lesson sections found.</p>

    <?php else: ?>

        <table
            border="1"
            cellpadding="8"
            cellspacing="0"
        >

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Lesson</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Order</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($sections as $section): ?>

                    <tr>

                        <td>
                            <?= e($section['id']) ?>
                        </td>

                        <td>
                            <?= e($section['lesson_title']) ?>
                        </td>

                        <td>
                            <?= e(
                                $sectionTypeLabels[
                                    $section['section_type']
                                ] ?? $section['section_type']
                            ) ?>
                        </td>

                        <td>
                            <?= e($section['title']) ?>
                        </td>

                        <td>
                            <?= e(
                                mb_strimwidth(
                                    $section['content'],
                                    0,
                                    100,
                                    '...'
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= e($section['order_number']) ?>
                        </td>

                        <td>

                            <a
                                href="<?= BASE_URL . 'admin/lesson_sections/edit.php?id=' . e($section['id']) ?>"
                            >
                                Edit
                            </a>

                            |

                            <form
                                method="POST"
                                action="<?= BASE_URL . 'admin/lesson_sections/delete.php' ?>"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this lesson section?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($section['id']) ?>"
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