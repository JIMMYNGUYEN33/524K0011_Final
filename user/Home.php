<?php
require_once __DIR__ . '/../helpers/auth.php';

require_login();
ensure_first_password_changed();

$user = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_home.css">
    <title>Bee-Home</title>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="user-info">
                <p>Welcome Back,</p>
                <h2><?= h($user['full_name'] ?: $user['email']) ?></h2>
            </div>
            <button class="btn-notification"><i class="fa-regular fa-bell"></i></button>
        </header>

        <div class="balance-card">
            <div class="card-top">
                <div class="wallet-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="balance-info">
                    <p>Wallet Status</p>
                    <span><?= h(ucfirst($user['status'])) ?></span>
                </div>
                <button class="btn-eye"><i class="fa-regular fa-eye"></i></button>
            </div>
            <h1 class="amount"><?= h(format_money($user['balance'])) ?></h1>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> text-center" style="font-size: 13px; margin: 14px 0;">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($user['status'] !== 'verified'): ?>
            <div class="alert alert-warning text-center" style="font-size: 13px; margin: 14px 0;">
                Your account is waiting for admin verification. Wallet features may be limited.
            </div>
        <?php endif; ?>

        <div class="services-section">
            <h3 class="section-title">Services</h3>
            <div class="services-grid">
                <a href="Deposit.php" class="service-item-link">
                    <div class="service-item">
                        <div class="icon-box green"><i class="fa-solid fa-download"></i></div>
                        <p>Deposit</p>
                    </div>
                </a>

                <a href="Withdraw.php" class="service-item-link">
                    <div class="service-item">
                        <div class="icon-box blue"><i class="fa-solid fa-upload"></i></div>
                        <p>Withdraw</p>
                    </div>
                </a>

                <a href="Transfer.php" class="service-item-link">
                    <div class="service-item">
                        <div class="icon-box purple"><i class="fa-solid fa-exchange-alt"></i></div>
                        <p>Transfer</p>
                    </div>
                </a>

                <a href="Buycard.php" class="service-item-link">
                    <div class="service-item">
                        <div class="icon-box orange"><i class="fa-solid fa-mobile-screen"></i></div>
                        <p>Buy Card</p>
                    </div>
                </a>

                <a href="History.php" class="service-item-link">
                    <div class="service-item">
                        <div class="icon-box indigo"><i class="fa-solid fa-history"></i></div>
                        <p>History</p>
                    </div>
                </a>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <h3 class="section-title">Recent Transactions</h3>
                <a href="History.php" style="font-size: 11px; color: #ffea00; text-decoration: none; font-weight: 600; margin-top: 5px;">See All</a>
            </div>

            <div id="home-empty-state" class="empty-state">
                <i class="fa-solid fa-wallet"></i>
                <p>No transactions yet</p>
            </div>
        </div>

        <nav class="bottom-nav">
            <a href="Home.php" class="nav-item active">
                <i class="fa-solid fa-house"></i>
                <span>BeePay</span>
            </a>
            <a href="Scan.php" class="nav-item scan-btn">
                <div class="scan-circle">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span>Scan</span>
            </a>
            <a href="Profile.php" class="nav-item">
                <i class="fa-regular fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>
</body>
</html>
