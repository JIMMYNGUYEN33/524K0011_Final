<?php
require_once __DIR__ . '/../helpers/auth.php';

require_login();
ensure_first_password_changed();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_profile.css">
</head>
<body>
    <div class="app-container">
        <header class="profile-header">
            <div class="header-top">
                <div class="welcome-text">
                    <p>Welcome back,</p>
                    <h2><?= h($user['full_name'] ?: $user['email']) ?></h2>
                </div>
                <button class="btn-notification"><i class="fa-regular fa-bell"></i></button>
            </div>
        </header>

        <div class="profile-card">
            <h3 class="card-title">Account Information</h3>

            <div class="profile-info-body">
                <div class="info-list">
                    <div class="info-item">
                        <i class="fa-regular fa-envelope info-icon"></i>
                        <div class="info-text">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?= h($user['email']) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-phone info-icon"></i>
                        <div class="info-text">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value"><?= h($user['phone']) ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-regular fa-calendar info-icon"></i>
                        <div class="info-text">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?= h($user['dob'] ?: '-') ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-location-dot info-icon"></i>
                        <div class="info-text">
                            <span class="info-label">Address</span>
                            <span class="info-value"><?= h($user['address'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>

                <div class="status-badge-wrapper">
                    <span class="status-badge verified">
                        <i class="fa-solid fa-circle-check"></i> <?= h(ucfirst($user['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="menu-section">
            <a href="../auth/change_password.php" class="menu-item-link">
                <div class="menu-item">
                    <div class="menu-icon-box purple">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <span class="menu-text">Change Password</span>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
            </a>

            <a href="#" class="menu-item-link">
                <div class="menu-item">
                    <div class="menu-icon-box orange">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <span class="menu-text">Help & Support</span>
                    <i class="fa-solid fa-chevron-right arrow-icon"></i>
                </div>
            </a>
        </div>

        <div class="logout-wrapper">
            <a href="../auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
            </a>
        </div>

        <p class="app-version">E-Wallet v1.0.0<br>Member since <?= h(date('d/m/Y', strtotime($user['created_at']))) ?></p>

        <nav class="bottom-nav">
            <a href="Home.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
            </a>
            <a href="Scan.php" class="nav-item scan-btn">
                <div class="scan-circle">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span>Scan QR</span>
            </a>
            <a href="Profile.php" class="nav-item active">
                <i class="fa-solid fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>
</body>
</html>
