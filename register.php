<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = '';

$name = '';
$email = '';

if (is_post()) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi nama
    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    // Validasi email
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validasi password
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    // Konfirmasi password
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Cek email jika validasi dasar berhasil
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $errors[] = 'Email is already registered.';

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                INSERT INTO users
                (
                    name,
                    email,
                    password,
                    role,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'student',
                    'active'
                )
            ");

            $stmt->execute([
                $name,
                $email,
                $hashed_password
            ]);

            $success = 'Registration successful. You can now login.';

            $name = '';
            $email = '';
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

    <title>Register - <?= e(APP_NAME) ?></title>

</head>

<body>

    <h1>Create Account</h1>

    <?php if (!empty($errors)): ?>

        <div>

            <?php foreach ($errors as $error): ?>

                <p>
                    <?= e($error) ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div>

            <p>
                <?= e($success) ?>
            </p>

            <a href="<?= BASE_URL ?>login.php">
                Login
            </a>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div>

            <label for="name">
                Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= e($name) ?>"
                required
            >

        </div>


        <div>

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                required
            >

        </div>


        <div>

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>


        <div>

            <label for="confirm_password">
                Confirm Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
            >

        </div>


        <button type="submit">
            Register
        </button>

    </form>


    <p>

        Already have an account?

        <a href="<?= BASE_URL ?>login.php">
            Login
        </a>

    </p>

</body>

</html>