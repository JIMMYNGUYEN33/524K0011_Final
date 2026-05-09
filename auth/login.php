<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bật hiển thị lỗi PHP để dễ debug trong quá trình làm bài
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Nhúng các file helper trước
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';

// 2. Nhúng file kết nối database SAU CÙNG để tránh bị ghi đè biến $pdo
require __DIR__ . '/../config/db_config.php'; 

// 3. Khai báo global để PHP chắc chắn sử dụng kết nối từ db_config.php
global $pdo;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu "username" (Email hoặc SĐT) và "password" từ form giao diện gửi lên
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Kiểm tra dữ liệu đầu vào không được để trống
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if (!isset($pdo) || !$pdo) {
            $error = 'Database connection lost. Please check db_config.php.';
        } else {
            try {
                // Tìm người dùng bằng Email hoặc Số điện thoại
                $stmt = $pdo->prepare('SELECT * FROM Users WHERE email = ? OR phone = ? LIMIT 1');
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user) {
                    // Kiểm tra trạng thái tài khoản (nếu database của bạn có cột status)
                    if (isset($user['status']) && $user['status'] === 'locked') {
                        $error = 'This account has been locked. Please contact support.';
                    } 
                    // So khớp mật khẩu đã mã hóa bằng password_verify
                    elseif (password_verify($password, $user['password'])) {
                        
                        // Đăng nhập thành công! Lưu thông tin vào Session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['full_name'];
                        $_SESSION['user_role'] = $user['role'];

                        session_write_close();

                        // Chuyển hướng người dùng dựa vào quyền (Role) hoặc trạng thái đăng nhập đầu tiên
                        if (isset($user['is_first_login']) && $user['is_first_login']) {
                            // Nếu là lần đầu đăng nhập, bắt buộc đổi mật khẩu
                            header("Location: first_change_password.php");
                        } else {
                            // Nếu bình thường, chuyển hướng theo role
                            if ($user['role'] === 'admin') {
                                header("Location: ../admin/AdminDashboard.php"); // Sửa đường dẫn trang admin của bạn tại đây
                            } else {
                                header("Location: ../user/Home.php"); // Sửa đường dẫn trang chủ user của bạn tại đây
                            }
                        }
                        exit();
                    } else {
                        $error = 'Invalid username or password.';
                    }
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Gọi file giao diện hiển thị đăng nhập
require_once __DIR__ . '/../user/Login.php';
?>