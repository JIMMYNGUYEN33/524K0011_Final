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

function reset_login_failures(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare(
        'UPDATE Users
         SET wrong_login_count = 0,
             abnormal_login_count = 0,
             locked_until = NULL
         WHERE id = ?'
    );
    $stmt->execute([$userId]);
}

function clear_expired_temporary_lock(PDO $pdo, array &$user): void
{
    if (empty($user['has_expired_lock'])) {
        return;
    }

    $stmt = $pdo->prepare(
        'UPDATE Users
         SET wrong_login_count = 0,
             locked_until = NULL
         WHERE id = ?'
    );
    $stmt->execute([$user['id']]);

    $user['wrong_login_count'] = 0;
    $user['locked_until'] = null;
    $user['has_expired_lock'] = 0;
    $user['is_temporarily_locked'] = 0;
}

function register_failed_login(PDO $pdo, array $user): string
{
    $wrongCount = (int) ($user['wrong_login_count'] ?? 0) + 1;
    $lockBatchCount = (int) ($user['abnormal_login_count'] ?? 0);

    if ($wrongCount < 3) {
        $stmt = $pdo->prepare('UPDATE Users SET wrong_login_count = ? WHERE id = ?');
        $stmt->execute([$wrongCount, $user['id']]);

        $remaining = 3 - $wrongCount;
        return "Invalid username or password. You have $remaining attempt(s) left before temporary lock.";
    }

    $lockBatchCount++;

    if ($lockBatchCount >= 2) {
        $stmt = $pdo->prepare(
            'UPDATE Users
             SET wrong_login_count = 0,
                 abnormal_login_count = ?,
                 locked_until = NULL,
                 is_permanently_locked = TRUE,
                 permanently_locked_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$lockBatchCount, $user['id']]);

        return 'You entered the wrong password too many times. This account has been permanently locked. Please contact admin.';
    }

    $stmt = $pdo->prepare(
        'UPDATE Users
         SET wrong_login_count = 0,
             abnormal_login_count = ?,
             locked_until = DATE_ADD(NOW(), INTERVAL 1 MINUTE)
         WHERE id = ?'
    );
    $stmt->execute([$lockBatchCount, $user['id']]);

    return 'You entered the wrong password 3 times. This account is locked for 1 minute.';
}

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
                $stmt = $pdo->prepare(
                    'SELECT *,
                            (locked_until IS NOT NULL AND locked_until > NOW()) AS is_temporarily_locked,
                            (locked_until IS NOT NULL AND locked_until <= NOW()) AS has_expired_lock
                     FROM Users
                     WHERE email = ? OR phone = ?
                     LIMIT 1'
                );
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user) {
                    clear_expired_temporary_lock($pdo, $user);
                    // Kiểm tra trạng thái tài khoản (nếu database của bạn có cột status)
                    if (!empty($user['is_permanently_locked'])) {
                        $error = 'This account has been permanently locked. Please contact admin.';
                    } elseif (!empty($user['is_temporarily_locked'])) {
                        $error = 'This account is temporarily locked. Please try again after 1 minute.';
                    }
                    // So khớp mật khẩu đã mã hóa bằng password_verify
                    elseif (password_verify($password, $user['password'])) {
                        reset_login_failures($pdo, (int) $user['id']);
                        
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
                        $error = register_failed_login($pdo, $user);
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
