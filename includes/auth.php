<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mengecek apakah user sudah login
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Mengharuskan user sudah login
 */
function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Mengambil ID user yang sedang login
 */
function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Mengambil nama user yang sedang login
 */
function current_user_name()
{
    return $_SESSION['user_name'] ?? null;
}

/**
 * Mengambil role user
 */
function current_user_role()
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Login Admin
 */
function is_admin()
{
    return is_logged_in()
        && current_user_role() === 'admin';
}


function require_admin()
{
    if (!is_admin()) {
        http_response_code(403);
        exit('Access denied.');
    }
}