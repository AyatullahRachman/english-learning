<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Question Types
|--------------------------------------------------------------------------
*/

$questionTypes = [
    'multiple_choice' => 'Multiple Choice',
    'true_false'      => 'True / False',
    'fill_blank'      => 'Fill Blank',
    'arrange_words'   => 'Arrange Words',
    'matching'        => 'Matching',
    'translation'     => 'Translation',
    'dictation'       => 'Dictation',
    'reading'         => 'Reading',
    'listening'       => 'Listening',
    'speaking'        => 'Speaking',
    'writing'         => 'Writing'
];

$optionTypes = [
    'multiple_choice',
    'true_false',
    'reading',
    'listening'
];

/*
|--------------------------------------------------------------------------
| Defaults
|--------------------------------------------------------------------------
*/

$lessonId = '';
$categoryId = '';
$questionType = 'multiple_choice';
$difficulty = 1;
$question = '';
$answer = '';
$explanation = '';
$points = '1.00';
$isActive = 1;

$options = [
    'A' => '',
    'B' => '',
    'C' => '',
    'D' => ''
];

$correctOption = '';

$errors = [];

/*
|--------------------------------------------------------------------------
| Get Lessons
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        title,
        level
    FROM lessons
    WHERE is_active = 1
    ORDER BY title ASC
");

$lessons = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        name
    FROM categories
    ORDER BY name ASC
");

$categories = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Handle Form
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf_token();

    $lessonId = filter_input(
        INPUT_POST,
        'lesson_id',
        FILTER_VALIDATE_INT
    );

    $categoryId = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );

    $questionType = trim(
        $_POST['question_type'] ?? ''
    );

    $difficulty = filter_input(
        INPUT_POST,
        'difficulty',
        FILTER_VALIDATE_INT
    );

    $question = trim(
        $_POST['question'] ?? ''
    );

    $answer = trim(
        $_POST['answer'] ?? ''
    );

    $explanation = trim(
        $_POST['explanation'] ?? ''
    );

    $points = trim(
        $_POST['points'] ?? '1.00'
    );

    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $options = [
        'A' => trim($_POST['option_a'] ?? ''),
        'B' => trim($_POST['option_b'] ?? ''),
        'C' => trim($_POST['option_c'] ?? ''),
        'D' => trim($_POST['option_d'] ?? '')
    ];

    $correctOption = trim(
        $_POST['correct_option'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Validate Question Type
    |--------------------------------------------------------------------------
    */

    if (!array_key_exists($questionType, $questionTypes)) {

        $errors[] = 'Invalid question type.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Question
    |--------------------------------------------------------------------------
    */

    if ($question === '') {

        $errors[] = 'Question is required.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Difficulty
    |--------------------------------------------------------------------------
    */

    if (
        $difficulty === false ||
        !in_array($difficulty, [1, 2, 3], true)
    ) {

        $errors[] = 'Invalid difficulty.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Points
    |--------------------------------------------------------------------------
    */

    if (
        !is_numeric($points) ||
        (float) $points < 0
    ) {

        $errors[] = 'Points must be a valid number.';

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Lesson
    |--------------------------------------------------------------------------
    */

    if ($lessonId) {

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
    | Validate Category
    |--------------------------------------------------------------------------
    */

    if ($categoryId) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$categoryId]);

        if (!$stmt->fetch()) {

            $errors[] = 'Selected category is not available.';

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Options
    |--------------------------------------------------------------------------
    */

    if (in_array($questionType, $optionTypes, true)) {

        if (
            $options['A'] === '' ||
            $options['B'] === ''
        ) {

            $errors[] = 'At least options A and B are required.';

        }

        if ($questionType !== 'true_false') {

            if (
                $options['C'] === '' ||
                $options['D'] === ''
            ) {

                $errors[] = 'Options C and D are required.';

            }

        }

        if (
            $correctOption === '' ||
            !array_key_exists($correctOption, $options)
        ) {

            $errors[] = 'Please select the correct option.';

        }

        if (
            $correctOption &&
            $options[$correctOption] === ''
        ) {

            $errors[] = 'The correct option cannot be empty.';

        }

    } else {

        if ($answer === '') {

            $errors[] = 'Answer is required for this question type.';

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            $pdo->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Insert Question
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO questions
                (
                    lesson_id,
                    category_id,
                    question_type,
                    difficulty,
                    question,
                    answer,
                    explanation,
                    points,
                    is_active
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $lessonId ?: null,
                $categoryId ?: null,
                $questionType,
                $difficulty,
                $question,
                in_array(
                    $questionType,
                    $optionTypes,
                    true
                )
                    ? null
                    : $answer,
                $explanation !== ''
                    ? $explanation
                    : null,
                (float) $points,
                $isActive
            ]);

            $questionId = $pdo->lastInsertId();

            /*
            |--------------------------------------------------------------------------
            | Insert Options
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $questionType,
                    $optionTypes,
                    true
                )
            ) {

                $optionStmt = $pdo->prepare("
                    INSERT INTO question_options
                    (
                        question_id,
                        option_key,
                        option_text,
                        is_correct
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                foreach ($options as $key => $text) {

                    if ($text === '') {
                        continue;
                    }

                    $optionStmt->execute([
                        $questionId,
                        $key,
                        $text,
                        $key === $correctOption ? 1 : 0
                    ]);
                }

            }

            $pdo->commit();

            redirect(
                BASE_URL . 'admin/questions/'
            );

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] =
                'Failed to save question. Please try again.';

        }

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
        Add Question - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Add Question</h1>

    <p>
        <a href="<?= BASE_URL . 'admin/questions/' ?>">
            Back to Questions
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

            <label for="category_id">
                Category
            </label>

            <br>

            <select
                name="category_id"
                id="category_id"
            >

                <option value="">
                    -- Select Category --
                </option>

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?= e($category['id']) ?>"
                        <?= (string) $categoryId === (string) $category['id']
                            ? 'selected'
                            : '' ?>
                    >

                        <?= e($category['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="question_type">
                Question Type
            </label>

            <br>

            <select
                name="question_type"
                id="question_type"
                required
            >

                <?php foreach ($questionTypes as $value => $label): ?>

                    <option
                        value="<?= e($value) ?>"
                        <?= $questionType === $value
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

            <label for="difficulty">
                Difficulty
            </label>

            <br>

            <select
                name="difficulty"
                id="difficulty"
                required
            >

                <option
                    value="1"
                    <?= $difficulty === 1 ? 'selected' : '' ?>
                >
                    Easy
                </option>

                <option
                    value="2"
                    <?= $difficulty === 2 ? 'selected' : '' ?>
                >
                    Medium
                </option>

                <option
                    value="3"
                    <?= $difficulty === 3 ? 'selected' : '' ?>
                >
                    Hard
                </option>

            </select>

        </div>

        <br>

        <div>

            <label for="question">
                Question
            </label>

            <br>

            <textarea
                name="question"
                id="question"
                rows="6"
                cols="80"
                required
            ><?= e($question) ?></textarea>

        </div>

        <br>

        <div id="answer-section">

            <label for="answer">
                Answer
            </label>

            <br>

            <textarea
                name="answer"
                id="answer"
                rows="5"
                cols="80"
            ><?= e($answer) ?></textarea>

        </div>

        <br>

        <div id="options-section">

            <h3>Options</h3>

            <p>
                Select the correct option.
            </p>

            <div>

                <label>
                    A.
                    <input
                        type="text"
                        name="option_a"
                        value="<?= e($options['A']) ?>"
                    >

                    <input
                        type="radio"
                        name="correct_option"
                        value="A"
                        <?= $correctOption === 'A'
                            ? 'checked'
                            : '' ?>
                    >

                    Correct
                </label>

            </div>

            <br>

            <div>

                <label>
                    B.
                    <input
                        type="text"
                        name="option_b"
                        value="<?= e($options['B']) ?>"
                    >

                    <input
                        type="radio"
                        name="correct_option"
                        value="B"
                        <?= $correctOption === 'B'
                            ? 'checked'
                            : '' ?>
                    >

                    Correct
                </label>

            </div>

            <br>

            <div>

                <label>
                    C.
                    <input
                        type="text"
                        name="option_c"
                        value="<?= e($options['C']) ?>"
                    >

                    <input
                        type="radio"
                        name="correct_option"
                        value="C"
                        <?= $correctOption === 'C'
                            ? 'checked'
                            : '' ?>
                    >

                    Correct
                </label>

            </div>

            <br>

            <div>

                <label>
                    D.
                    <input
                        type="text"
                        name="option_d"
                        value="<?= e($options['D']) ?>"
                    >

                    <input
                        type="radio"
                        name="correct_option"
                        value="D"
                        <?= $correctOption === 'D'
                            ? 'checked'
                            : '' ?>
                    >

                    Correct
                </label>

            </div>

        </div>

        <br>

        <div>

            <label for="explanation">
                Explanation
            </label>

            <br>

            <textarea
                name="explanation"
                id="explanation"
                rows="5"
                cols="80"
            ><?= e($explanation) ?></textarea>

        </div>

        <br>

        <div>

            <label for="points">
                Points
            </label>

            <br>

            <input
                type="number"
                name="points"
                id="points"
                min="0"
                step="0.01"
                value="<?= e($points) ?>"
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
                    <?= $isActive === 1 ? 'checked' : '' ?>
                >

                Active

            </label>

        </div>

        <br>

        <button type="submit">
            Save Question
        </button>

        <a href="<?= BASE_URL . 'admin/questions/' ?>">
            Cancel
        </a>

    </form>

    <script>

        const questionType =
            document.getElementById('question_type');

        const optionsSection =
            document.getElementById('options-section');

        const answerSection =
            document.getElementById('answer-section');

        const optionTypes = [
            'multiple_choice',
            'true_false',
            'reading',
            'listening'
        ];

        function updateQuestionTypeFields() {

            const type = questionType.value;

            if (optionTypes.includes(type)) {

                optionsSection.style.display = 'block';
                answerSection.style.display = 'none';

            } else {

                optionsSection.style.display = 'none';
                answerSection.style.display = 'block';

            }

            const optionC =
                document.querySelector(
                    'input[name="option_c"]'
                );

            const optionD =
                document.querySelector(
                    'input[name="option_d"]'
                );

            if (type === 'true_false') {

                optionC.disabled = true;
                optionD.disabled = true;

            } else {

                optionC.disabled = false;
                optionD.disabled = false;

            }

        }

        questionType.addEventListener(
            'change',
            updateQuestionTypeFields
        );

        updateQuestionTypeFields();

    </script>

</body>

</html>