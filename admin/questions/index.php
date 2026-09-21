<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Get Questions
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        questions.id,
        questions.lesson_id,
        questions.category_id,
        questions.question_type,
        questions.difficulty,
        questions.question,
        questions.explanation,
        questions.points,
        questions.is_active,
        questions.created_at,

        lessons.title AS lesson_title,
        categories.name AS category_name,

        (
            SELECT COUNT(*)
            FROM question_options
            WHERE question_options.question_id = questions.id
        ) AS option_count

    FROM questions

    LEFT JOIN lessons
        ON lessons.id = questions.lesson_id

    LEFT JOIN categories
        ON categories.id = questions.category_id

    ORDER BY
        questions.id DESC
");

$questions = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Question Type Labels
|--------------------------------------------------------------------------
*/

$questionTypeLabels = [
    'multiple_choice' => 'Multiple Choice',
    'true_false'      => 'True / False',
    'fill_blank'      => 'Fill in the Blank',
    'arrange_words'   => 'Arrange Words',
    'matching'        => 'Matching',
    'translation'     => 'Translation',
    'dictation'       => 'Dictation',
    'reading'         => 'Reading',
    'listening'       => 'Listening',
    'speaking'        => 'Speaking',
    'writing'         => 'Writing'
];

/*
|--------------------------------------------------------------------------
| Difficulty Labels
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Questions - <?= e(APP_NAME) ?>
    </title>
    
    <link
        rel="stylesheet"
        href="<?= BASE_URL . 'assets/css/style.css' ?>"

</head>

<body>

    <h1>Questions</h1>

    <p>
        Manage questions for lessons and tests.
    </p>

    <!--
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    -->

    <p>

        <a href="<?= BASE_URL . 'admin/' ?>">
            Back to Admin Dashboard
        </a>

        |

        <a href="<?= BASE_URL . 'admin/lessons/' ?>">
            Lessons
        </a>

    </p>

    <p>

        <a href="<?= BASE_URL . 'admin/questions/create.php' ?>">
            + Add Question
        </a>

    </p>

    <?php if (empty($questions)): ?>

        <p>
            No questions found.
        </p>

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

                    <th>Category</th>

                    <th>Type</th>

                    <th>Question</th>

                    <th>Difficulty</th>

                    <th>Points</th>

                    <th>Options</th>

                    <th>Status</th>

                    <th>Created At</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($questions as $question): ?>

                    <tr>

                        <!-- ID -->

                        <td>
                            <?= e($question['id']) ?>
                        </td>

                        <!-- Lesson -->

                        <td>

                            <?php if (!empty($question['lesson_title'])): ?>

                                <?= e($question['lesson_title']) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>

                        <!-- Category -->

                        <td>

                            <?php if (!empty($question['category_name'])): ?>

                                <?= e($question['category_name']) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>

                        <!-- Question Type -->

                        <td>

                            <?= e(
                                $questionTypeLabels[
                                    $question['question_type']
                                ]
                                ?? $question['question_type']
                            ) ?>

                        </td>

                        <!-- Question -->

                        <td>

                            <?php
                            $questionText = $question['question'];

                            if (mb_strlen($questionText) > 80) {
                                $questionText =
                                    mb_substr($questionText, 0, 80)
                                    . '...';
                            }
                            ?>

                            <?= e($questionText) ?>

                        </td>

                        <!-- Difficulty -->

                        <td>

                            <?= e(
                                $difficultyLabels[
                                    (int) $question['difficulty']
                                ]
                                ?? 'Unknown'
                            ) ?>

                        </td>

                        <!-- Points -->

                        <td>

                            <?= e($question['points']) ?>

                        </td>

                        <!-- Options -->

                        <td>

                            <?= e($question['option_count']) ?>

                        </td>

                        <!-- Status -->

                        <td>

                            <?php if ((int) $question['is_active'] === 1): ?>

                                Active

                            <?php else: ?>

                                Inactive

                            <?php endif; ?>

                        </td>

                        <!-- Created At -->

                        <td>

                            <?= e($question['created_at']) ?>

                        </td>

                        <!-- Action -->

                        <td>

                            <a
                                href="<?= BASE_URL . 'admin/questions/edit.php?id=' . e($question['id']) ?>"
                            >
                                Edit
                            </a>

                            |

                            <form
                                method="POST"
                                action="<?= BASE_URL . 'admin/questions/delete.php' ?>"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this question?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($question['id']) ?>"
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