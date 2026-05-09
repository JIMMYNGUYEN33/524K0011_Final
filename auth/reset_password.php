<?php
// Khởi chạy session ở đầu trang (bắt buộc để đọc $_SESSION['reset_user_id'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nhúng file kết nối database và các helper cần thiết
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

$error = '';
$success = false;
$resetUserId = $_SESSION['reset_user_id'] ?? null;

// Kiểm tra nếu chưa yêu cầu OTP thì bắt quay về trang nhập Email/SĐT trước
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
        // Kiểm tra mã OTP trong Database
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
            // Khởi động Transaction để đổi pass và hủy OTP đồng thời
            $pdo->beginTransaction();

            // Cập nhật mật khẩu mới và tắt trạng thái "đăng nhập lần đầu" (is_first_login = FALSE)
            $stmt = $pdo->prepare('UPDATE Users SET password = ?, is_first_login = FALSE WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $resetUserId]);

            // Đánh dấu mã OTP này đã được sử dụng
            $stmt = $pdo->prepare('UPDATE OTP_Codes SET is_used = TRUE WHERE id = ?');
            $stmt->execute([$otpRow['id']]);

            $pdo->commit();

            // Xóa session reset mật khẩu để bảo mật
            unset($_SESSION['reset_user_id']);
            $success = true;
        }
    }
}

// =========================================================================
// NHÚNG GIAO DIỆN TỪ THƯ MỤC NGOÀI (BỎ GIAO DIỆN CŨ TRONG AUTH)
// =========================================================================
// Bạn hãy tạo file giao diện ResetPassword.php trong thư mục "user" hoặc "admin" nhé
// Ở đây tui đang gọi file giao diện nằm ở thư mục "user" làm ví dụ:
require_once __DIR__ . '/../user/ResetPassword.php';
?>