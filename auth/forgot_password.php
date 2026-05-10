<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../helpers/mailer.php';

$error = '';
$otpForTesting = null;
$mailSent = false;

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

        $mailSent = send_mail(
            $user['email'],
            $user['full_name'],
            'BeePay - Reset Password OTP',
            '
            <h2>BeePay password reset</h2>
            <p>Your OTP code is:</p>
            <h1 style="letter-spacing: 4px;">' . h($otpForTesting) . '</h1>
            <p>This code expires in 1 minute.</p>
            '
        );

        $_SESSION['reset_user_id'] = $user['id'];
    }
}

require_once __DIR__ . '/../user/Forgot.php';
