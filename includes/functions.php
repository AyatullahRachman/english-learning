<?php

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


function redirect($url)
{
    header("Location: " . $url);
    exit;
}


function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );

    }

    return $_SESSION['csrf_token'];
}


function csrf_field()
{
    return '
        <input
            type="hidden"
            name="csrf_token"
            value="' . e(csrf_token()) . '"
        >
    ';
}


function verify_csrf_token()
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        empty($token) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {

        http_response_code(403);

        exit('Invalid CSRF token.');

    }

    return true;
}