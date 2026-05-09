<?php
// Khởi chạy session ở đầu trang để kiểm tra trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nhúng file kết nối database và các file helper cần thiết
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

// Kiểm tra quyền: Phải đăng nhập mới được vào trang đổi mật khẩu này
require_login();
ensure_first_password_changed();

$user = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($oldPassword, $user['password'])) {
        $error = 'Old password is not correct.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must have at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password confirmation does not match.';
    } else {
        // Cập nhật mật khẩu mới mã hóa bằng bcrypt vào Database
        $stmt = $pdo->prepare('UPDATE Users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);

        set_flash('success', 'Password changed successfully.');
        redirect_to('/index.php');
        exit();
    }
}

// =========================================================================
// NHÚNG GIAO DIỆN TỪ THƯ MỤC NGOÀI (BỎ GIAO DIỆN CŨ TRONG AUTH)
// =========================================================================
// Nhúng file giao diện ChangePassword.php nằm ở thư mục "user"
require_once __DIR__ . '/../user/ChangePassword.php';
?>