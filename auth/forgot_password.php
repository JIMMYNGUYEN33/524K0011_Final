<?php
require_once __DIR__ . '/../helpers/ui.php';

$error = '';
$otpForTesting = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM Users WHERE email = ? AND phone = ? LIMIT 1');
    $stmt->execute([$email, $phone]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No account matches this email and phone.';
    } else {
        $otpForTesting = (string) random_int(100000, 999999);
        $stmt = $pdo->prepare(
            'INSERT INTO OTP_Codes (user_id, otp_hash, type, expires_at)
            VALUES (?, ?, "reset_password", DATE_ADD(NOW(), INTERVAL 1 MINUTE))'
        );
        $stmt->execute([
            $user['id'],
            password_hash($otpForTesting, PASSWORD_DEFAULT),
        ]);

        $_SESSION['reset_user_id'] = $user['id'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/style.css') ?>">
</head>
<body>
    <h1 style="text-align: center; color: aliceblue; margin-top: 30px;">BeePay</h1>
    <p style="text-align: center; color: aliceblue;">Recover your account securely</p>

    <div class="Login-card">
        <form method="post">
            <h2>Forgot Password</h2>

            <?php if ($error): ?>
                <div class="alert-box alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <?php if ($otpForTesting): ?>
                <div class="alert-box alert-success">
                    OTP for testing: <strong><?= h($otpForTesting) ?></strong>. This code expires in 1 minute.
                </div>
                <p class="auth-link"><a href="<?= base_url('/auth/reset_password.php') ?>">Continue to reset password</a></p>
            <?php endif; ?>

            <div class="in_gr">
                <label for="email">Email</label>
                <div class="in_wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input id="email" type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="in_gr">
                <label for="phone">Phone</label>
                <div class="in_wrapper">
                    <i class="fa-solid fa-phone"></i>
                    <input id="phone" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" required>
                </div>
            </div>

            <button type="submit" class="btn-signin">Send OTP</button>
        </form>

        <p class="auth-link"><a href="<?= base_url('/auth/login.php') ?>">Back to login</a></p>
    </div>
</body>
</html>
