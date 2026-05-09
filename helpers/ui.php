<?php
require_once __DIR__ . '/auth.php';

function render_header($title)
{
    $user = current_user();
    $flash = get_flash();
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> - E-Wallet</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }
        .topbar {
            background: #0f766e;
            color: #fff;
            padding: 14px 22px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        .brand { font-weight: 700; font-size: 20px; }
        .nav { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav a {
            color: #fff;
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.13);
        }
        .container { width: min(1100px, calc(100% - 28px)); margin: 24px auto; }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        h1, h2, h3 { margin-top: 0; }
        label { display: block; font-weight: 700; margin: 12px 0 6px; }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }
        textarea { min-height: 90px; resize: vertical; }
        button, .button {
            display: inline-block;
            border: 0;
            border-radius: 6px;
            background: #0f766e;
            color: #fff;
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
            margin-top: 12px;
        }
        .button.secondary, button.secondary { background: #475569; }
        .button.danger, button.danger { background: #b91c1c; }
        .button.warning, button.warning { background: #b45309; }
        .alert {
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        .alert.success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .alert.error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .alert.info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .actions form { margin: 0; }
        .muted { color: #64748b; }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e2e8f0;
            font-size: 13px;
            font-weight: 700;
        }
        .id-photo {
            max-width: 260px;
            max-height: 180px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            object-fit: contain;
            background: #f8fafc;
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">E-Wallet</div>
    <nav class="nav">
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="<?= base_url('/admin/users.php') ?>">Users</a>
            <a href="<?= base_url('/admin/pending_transactions.php') ?>">Pending Transactions</a>
            <a href="<?= base_url('/auth/logout.php') ?>">Logout</a>
        <?php elseif ($user): ?>
            <a href="<?= base_url('/index.php') ?>">Dashboard</a>
            <a href="<?= base_url('/auth/change_password.php') ?>">Change Password</a>
            <a href="<?= base_url('/auth/logout.php') ?>">Logout</a>
        <?php else: ?>
            <a href="<?= base_url('/auth/login.php') ?>">Login</a>
            <a href="<?= base_url('/auth/register.php') ?>">Register</a>
            <a href="<?= base_url('/auth/forgot_password.php') ?>">Forgot Password</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="alert <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
    <?php
}

function render_footer()
{
    ?>
</main>
</body>
</html>
    <?php
}
