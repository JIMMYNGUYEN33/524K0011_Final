<?php
require_once __DIR__ . '/helpers/auth.php';

require_login();
ensure_first_password_changed();

$user = current_user();

if ($user['role'] === 'admin') {
    redirect_to('/admin/users.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('/style_home.css') ?>">
    <title>BeePay Home</title>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="brand-logo">
                <i class="fa-solid fa-wallet"></i> <span>BeePay</span>
            </div>
            <nav class="menu">
                <a class="active" href="<?= base_url('/index.php') ?>"><i class="fa-solid fa-house"></i>Home</a>
                <a href="<?= base_url('/Deposit.html') ?>"><i class="fa-solid fa-download"></i> Deposit</a>
                <a href="<?= base_url('/Withdraw.html') ?>"><i class="fa-solid fa-upload"></i> Withdraw</a>
                <a href="<?= base_url('/Transfer.html') ?>"><i class="fa-solid fa-exchange"></i> Transfer</a>
                <a href="<?= base_url('/BuyPhoneCard.html') ?>"><i class="fa-solid fa-mobile-screen"></i> Buy Phone Card</a>
                <a href="<?= base_url('/Transactions.html') ?>"><i class="fa-solid fa-history"></i> Transactions</a>
                <a href="<?= base_url('/Profile.html') ?>"><i class="fa-solid fa-user"></i> Profile</a>
                <a href="<?= base_url('/auth/change_password.php') ?>"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="user-info">
                    <p>Welcome back, <strong><?= h($user['full_name']) ?></strong></p>
                    <a href="<?= base_url('/auth/logout.php') ?>" class="logout"><i class="fa-solid fa-sign-out"></i> Logout</a>
                </div>
            </header>

            <section class="content-body">
                <h2>Welcome back</h2>
                <div class="balance-card">
                    <div class="card-header">
                        <div class="card-logo"><i class="fa-solid fa-wallet"></i></div>
                        <span class="status-tag"><?= h(ucfirst($user['status'])) ?></span>
                    </div>
                    <div class="card-info">
                        <p>Total Balance</p>
                        <h3><?= h(format_money($user['balance'])) ?></h3>
                        <p class="acc-id">Account ID: user-<?= h($user['id']) ?></p>
                    </div>
                </div>

                <div class="quick-actions">
                    <a class="action-item" href="<?= base_url('/Deposit.html') ?>"><i class="fa-solid fa-download" style="color: #2ecc71;"></i><p>Deposit</p></a>
                    <a class="action-item" href="<?= base_url('/Withdraw.html') ?>"><i class="fa-solid fa-upload" style="color: #3498db;"></i><p>Withdraw</p></a>
                    <a class="action-item" href="<?= base_url('/Transfer.html') ?>"><i class="fa-solid fa-exchange" style="color: #9b59b6;"></i><p>Transfer</p></a>
                    <a class="action-item" href="<?= base_url('/BuyPhoneCard.html') ?>"><i class="fa-solid fa-mobile-screen" style="color: #e67e22;"></i><p>Phone Card</p></a>
                </div>

                <?php if ($user['status'] !== 'verified'): ?>
                    <div class="notice-box">
                        Your account is not verified yet. Wallet features are available after admin approval.
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
