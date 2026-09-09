<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();


/*
|--------------------------------------------------------------------------
| Get Topics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        topics.id,
        topics.name,
        topics.slug,
        topics.level,
        topics.description,
        topics.created_at,
        categories.name AS category_name
    FROM topics
    INNER JOIN categories
        ON topics.category_id = categories.id
    ORDER BY topics.id ASC
");

$topics = $stmt->fetchAll();

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
        Topics - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Topics</h1>

    <p>

        <a href="<?= BASE_URL ?>admin/">
            ← Admin Dashboard
        </a>

    </p>

    <p>

        <a href="<?= BASE_URL ?>admin/topics/create.php">
            + Add Topic
        </a>

    </p>


    <?php if (empty($topics)): ?>

        <p>
            No topics found.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Topic</th>
                    <th>Slug</th>
                    <th>Level</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>

            </thead>


            <tbody>

                <?php foreach ($topics as $topic): ?>

                    <tr>

                        <td>
                            <?= e($topic['id']) ?>
                        </td>

                        <td>
                            <?= e($topic['category_name']) ?>
                        </td>

                        <td>
                            <?= e($topic['name']) ?>
                        </td>

                        <td>
                            <?= e($topic['slug']) ?>
                        </td>

                        <td>
                            <?= e($topic['level']) ?>
                        </td>

                        <td>
                            <?= e($topic['description']) ?>
                        </td>

                        <td>
                            <?= e($topic['created_at']) ?>
                        </td>

                        <td>

                            <a
                                href="<?= BASE_URL ?>admin/topics/edit.php?id=<?= e($topic['id']) ?>"
                            >
                                Edit
                            </a>

                            |

                            <form
                                method="POST"
                                action="<?= BASE_URL ?>admin/topics/delete.php"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this topic?');"
                            >

                                <?= csrf_field() ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= e($topic['id']) ?>"
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