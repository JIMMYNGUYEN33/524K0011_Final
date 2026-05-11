<?php
require_once __DIR__ . '/../config/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url($path = '')
{
    return '/524K0011_Final' . $path;
}

function redirect_to($path)
{
    header('Location: ' . base_url($path));
    exit;
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function current_user()
{
    global $pdo;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['role']);
        return null;
    }

    return $user;
}

function require_login()
{
    if (!current_user()) {
        redirect_to('/auth/login.php');
    }
}

function require_admin()
{
    $user = current_user();

    if (!$user || $user['role'] !== 'admin') {
        redirect_to('/auth/login.php');
    }
}

function require_verified_user()
{
    require_login();
    ensure_first_password_changed();

    $user = current_user();

    if (!$user || $user['role'] !== 'user') {
        redirect_to('/auth/login.php');
    }

    if ($user['status'] !== 'verified') {
        set_flash('error', 'This feature is only available for verified accounts.');
        redirect_to('/user/Home.php');
    }
}

function redirect_after_login($user)
{
    if ($user['role'] === 'admin') {
        redirect_to('/admin/users.php');
    }

    if ((int) $user['is_first_login'] === 1) {
        redirect_to('/auth/first_change_password.php');
    }

    redirect_to('/index.php');
}

function ensure_first_password_changed()
{
    $user = current_user();

    if (!$user || $user['role'] === 'admin') {
        return;
    }

    $current = basename($_SERVER['PHP_SELF']);
    $allowed = ['first_change_password.php', 'logout.php'];

    if ((int) $user['is_first_login'] === 1 && !in_array($current, $allowed, true)) {
        redirect_to('/auth/first_change_password.php');
    }
}

function random_string($length = 6)
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return $result;
}

function format_money($amount)
{
    return number_format((float) $amount, 0, '.', ',') . ' VND';
}