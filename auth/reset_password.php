<?php
require_once __DIR__ . '/../helpers/ui.php';

$error = '';
$success = false;
$resetUserId = $_SESSION['reset_user_id'] ?? null;

if (!$resetUserId) {
    set_flash('error', 'Please request an OTP first.');
    redirect_to('/auth/forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = 'New password must have at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password confirmation does not match.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT *
            FROM OTP_Codes
            WHERE user_id = ?
                AND type = "reset_password"
                AND is_used = FALSE
                AND expires_at >= NOW()
            ORDER BY created_at DESC
            LIMIT 1'
        );
        $stmt->execute([$resetUserId]);
        $otpRow = $stmt->fetch();

        if (!$otpRow || !password_verify($otp, $otpRow['otp_hash'])) {
            $error = 'OTP is incorrect or expired.';
        } else {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('UPDATE Users SET password = ?, is_first_login = FALSE WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $resetUserId]);

            $stmt = $pdo->prepare('UPDATE OTP_Codes SET is_used = TRUE WHERE id = ?');
            $stmt->execute([$otpRow['id']]);

            $pdo->commit();

            unset($_SESSION['reset_user_id']);
            $success = true;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/style.css') ?>">
</head>
<body>
    <h1 style="text-align: center; color: aliceblue; margin-top: 30px;">BeePay</h1>
    <p style="text-align: center; color: aliceblue;">Create a new password</p>

    <div class="Login-card">
        <h2>Reset Password</h2>

        <?php if ($success): ?>
            <div class="alert-box alert-success">Password reset successfully. Please login again.</div>
            <p class="auth-link"><a href="<?= base_url('/auth/login.php') ?>">Go to login</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="in_gr">
                    <label for="otp">OTP</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-key"></i>
                        <input id="otp" name="otp" maxlength="6" required>
                    </div>
                </div>

                <div class="in_gr">
                    <label for="new_password">New password</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input id="new_password" type="password" name="new_password" required>
                    </div>
                </div>

                <div class="in_gr">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input id="confirm_password" type="password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn-signin">Reset password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
