<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$stmt = $pdo->query("
    SELECT
        id,
        name,
        slug,
        description,
        created_at
    FROM categories
    ORDER BY id ASC
");

$categories = $stmt->fetchAll();

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
        Categories - <?= e(APP_NAME) ?>
    </title>

</head>

<body>

    <h1>Categories</h1>

    <p>
        <a href="<?= BASE_URL ?>admin/">
            ← Admin Dashboard
        </a>
    </p>

    <p>
        <a href="<?= BASE_URL ?>admin/categories/create.php">
            + Add Category
        </a>
    </p>

    <?php if (empty($categories)): ?>

        <p>
            No categories found.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($categories as $category): ?>

                    <tr>

                        <td>
                            <?= e($category['id']) ?>
                        </td>

                        <td>
                            <?= e($category['name']) ?>
                        </td>

                        <td>
                            <?= e($category['slug']) ?>
                        </td>

                        <td>
                            <?= e($category['description']) ?>
                        </td>

                        <td>
                            <?= e($category['created_at']) ?>
                        </td>

                        <td>

                            <a href="<?= BASE_URL ?>admin/categories/edit.php?id=<?= e($category['id']) ?>">
                                Edit
                            </a>

                            |

                        <form
                            method="POST"
                            action="<?= BASE_URL ?>admin/categories/delete.php"
                            style="display: inline;"
                            onsubmit="return confirm('Are you sure you want to delete this category?');"
                        >
                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= e($category['id']) ?>"
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