<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_forgot.css">
</head>
<body>
    <div class="app-container">
        <div class="back-navigation">
            <a href="../auth/login.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <h1 class="brand-title">BeePay</h1>
        <p class="brand-subtitle">Secure digital payment solution</p>

        <div class="Forgot-card">
            <form action="" method="POST">
                <h2>Forgot Password</h2>
                <p class="forgot-desc">Enter your email and phone number. We will send a one-time password to your email.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center" style="font-size: 14px; padding: 10px; margin-bottom: 15px;">
                        <?= h($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($otpForTesting)): ?>
                    <?php if (!empty($mailSent)): ?>
                        <div class="alert alert-success text-center" style="font-size: 14px; padding: 10px; margin-bottom: 15px;">
                            OTP has been sent to your email. This code expires in 1 minute.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger text-center" style="font-size: 14px; padding: 10px; margin-bottom: 15px;">
                            Cannot send OTP email. OTP for testing:
                            <strong><?= h($otpForTesting) ?></strong>
                        </div>
                    <?php endif; ?>

                    <p class="back-to-login">
                        <a href="../auth/reset_password.php">Continue to reset password</a>
                    </p>
                <?php endif; ?>

                <div class="in_gr">
                    <label>Email</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input required type="email" name="email" placeholder="Enter registered email" value="<?= h($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Phone Number</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-phone"></i>
                        <input required type="text" name="phone" placeholder="Enter registered phone" value="<?= h($_POST['phone'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Send OTP</button>
            </form>

            <p class="back-to-login">
                Remember your password? <a href="../auth/login.php">Sign In</a>
            </p>
        </div>
    </div>
</body>
</html>
