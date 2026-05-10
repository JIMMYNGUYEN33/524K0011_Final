<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Change Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_cpw.css">
</head>
<body>
    <div class="app-container">
        <div class="header-icon-wrapper">
            <div class="wallet-circle">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <h1 class="page-title">Change Password</h1>
        <p class="page-subtitle"><?= !empty($isFirstPasswordChange) ? 'You must change your password to continue' : 'Update your BeePay password' ?></p>

        <div class="password-card">
            <?php if (!empty($isFirstPasswordChange)): ?>
                <div class="warning-alert-box">
                    <strong>First Time Login:</strong> For security reasons, you must change your password before accessing the system. If you don't want to change it now, you can logout.
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center" style="font-size: 14px; padding: 8px; margin-bottom: 15px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($isFirstPasswordChange)): ?>
                    <div class="in_gr">
                        <label>Old Password</label>
                        <div class="in_wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input required type="password" name="old_password" placeholder="Enter old password">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="in_gr <?= empty($isFirstPasswordChange) ? 'mt-3' : '' ?>">
                    <label>New Password</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input required type="password" name="new_password" minlength="6" placeholder="Enter new password">
                    </div>
                    <span class="input-hint">Minimum 6 characters</span>
                </div>

                <div class="in_gr mt-3">
                    <label>Confirm New Password</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input required type="password" name="confirm_password" minlength="6" placeholder="Confirm new password">
                    </div>
                </div>

                <button type="submit" class="btn-change-pw mt-4"><?= !empty($isFirstPasswordChange) ? 'Change Password & Continue' : 'Change Password' ?></button>
            </form>

            <div class="logout-action-wrapper mt-3">
                <a href="../auth/logout.php" class="btn-logout-link">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>
