<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bật hiển thị lỗi PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

// Nhúng database sau cùng tránh ghi đè
require __DIR__ . '/../config/db_config.php';
global $pdo;

// Khóa trang này lại, nếu chưa đăng nhập thì bắt quay về login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate dữ liệu
    if (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // 1. Mã hóa mật khẩu mới
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $userId = $_SESSION['user_id'];

            // 2. Cập nhật mật khẩu mới và TẮT cờ đăng nhập lần đầu (is_first_login = 0)
            $stmt = $pdo->prepare('UPDATE Users SET password = ?, is_first_login = FALSE WHERE id = ?');
            $stmt->execute([$newPasswordHash, $userId]);

            // 3. Giải phóng Session Lock trước khi chuyển hướng để tránh bị nghẽn hệ thống
            session_write_close();

            // 4. Chuyển hướng thẳng vào trang chủ Home.php của User
            header("Location: ../user/Home.php");
            exit();

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Nạp file giao diện hiển thị đổi mật khẩu
require_once __DIR__ . '/../user/ChangePassword.php';
?>