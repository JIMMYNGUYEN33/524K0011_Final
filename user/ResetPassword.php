<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_cpw.css">
</head>
<body>
    <div class="app-container">
        <div class="header-icon-wrapper">
            <div class="wallet-circle">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>

        <h1 class="page-title">Reset Password</h1>
        <p class="page-subtitle">Enter the OTP sent to your email</p>

        <div class="password-card">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success text-center" style="font-size: 14px; padding: 10px; margin-bottom: 15px;">
                    Password reset successfully. Please login again.
                </div>
                <div class="logout-action-wrapper mt-3">
                    <a href="../auth/login.php" class="btn-logout-link">Go to login</a>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center" style="font-size: 14px; padding: 8px; margin-bottom: 15px;">
                            <?= h($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="in_gr">
                        <label>OTP Code</label>
                        <div class="in_wrapper">
                            <i class="fa-solid fa-key"></i>
                            <input required type="text" name="otp" maxlength="6" placeholder="Enter 6-digit OTP">
                        </div>
                    </div>

                    <div class="in_gr mt-3">
                        <label>New Password</label>
                        <div class="in_wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input required type="password" name="new_password" minlength="6" placeholder="Enter new password">
                        </div>
                    </div>

                    <div class="in_gr mt-3">
                        <label>Confirm New Password</label>
                        <div class="in_wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input required type="password" name="confirm_password" minlength="6" placeholder="Confirm new password">
                        </div>
                    </div>

                    <button type="submit" class="btn-change-pw mt-4">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
