<?php
// Khởi chạy session ở đầu trang (bắt buộc để lưu $_SESSION['reset_user_id'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nhúng file kết nối database và các file helper cần thiết
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

$error = '';
$otpForTesting = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Kiểm tra xem Email và SĐT nhập vào có khớp với tài khoản nào không
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE email = ? AND phone = ? LIMIT 1');
    $stmt->execute([$email, $phone]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No account matches this email and phone.';
    } else {
        // Tạo ngẫu nhiên mã OTP 6 số để test
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

// =========================================================================
// NHÚNG GIAO DIỆN TỪ THƯ MỤC NGOÀI (BỎ GIAO DIỆN CŨ TRONG AUTH)
// =========================================================================
// Gọi file giao diện ForgotPassword.php nằm trong thư mục "user"
require_once __DIR__ . '/../user/Forgot.php';
?>