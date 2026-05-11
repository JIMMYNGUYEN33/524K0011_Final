<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

$error = '';
$success = false;
$resetUserId = $_SESSION['reset_user_id'] ?? null;

if (!$resetUserId) {
    set_flash('error', 'Please request an OTP first.');
    redirect_to('/auth/forgot_password.php');
    exit();
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

require_once __DIR__ . '/../user/ResetPassword.php';
?>