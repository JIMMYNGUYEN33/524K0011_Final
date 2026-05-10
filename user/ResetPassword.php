<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_cpw.css?v=2">
</head>
<body>
    <div class="app-container">
        <a href="../auth/forgot_password.php" class="btn-back" aria-label="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <header class="brand-header">
            <h1 class="brand-title">BeePay</h1>
            <p class="brand-subtitle">Secure digital payment solution</p>
        </header>

        <div class="password-card">
            <div class="card-heading">
                <h2>Reset Password</h2>
                <p>Enter the OTP sent to your email</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert-message alert-success-custom">
                    Password reset successfully. Please login again.
                </div>
                <div class="logout-action-wrapper mt-3">
                    <a href="../auth/login.php" class="btn-logout-link">Go to login</a>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <?php if (!empty($error)): ?>
                        <div class="alert-message alert-error-custom">
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
