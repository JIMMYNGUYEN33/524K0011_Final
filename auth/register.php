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

// 2. Nhúng file kết nối database SAU CÙNG để đảm bảo không bị file khác ghi đè biến $pdo
require __DIR__ . '/../config/db_config.php'; 

// 3. Khai báo global $pdo để PHP chắc chắn sử dụng biến kết nối từ db_config.php
global $pdo;

$error = '';
$success = '';
$generated_password = ''; // Dùng để hiển thị mật khẩu tạm thời cho người dùng khi đăng ký thành công để test

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu đầu vào và làm sạch (Trim)
    $fullName = trim($_POST['full_name'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validate kiểm tra dữ liệu bắt buộc
    if (empty($fullName) || empty($birthday) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address format.';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = 'Phone number must be 10 or 11 digits.';
    } else {
        // Kiểm tra chắc chắn một lần nữa xem kết nối pdo có tồn tại không
        if (!isset($pdo) || !$pdo) {
            $error = 'Database connection lost. Please check db_config.php.';
        } else {
            try {
                // Kiểm tra xem Email hoặc Số điện thoại đã tồn tại trong database chưa
                $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ? OR phone = ? LIMIT 1');
                $stmt->execute([$email, $phone]);
                
                if ($stmt->fetch()) {
                    $error = 'Email or Phone number is already registered.';
                } else {
                    // Xử lý upload ảnh ID Card (Mặt trước & Mặt sau)
                    $uploadDir = __DIR__ . '/../uploads/';
                    
                    // Tự động tạo thư mục uploads nếu chưa tồn tại
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $idFrontPath = '';
                    $idBackPath = '';
                    $uploadSuccess = true;

                    // Xử lý upload ảnh mặt trước
                    if (isset($_FILES['id_front']) && $_FILES['id_front']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['id_front']['name'], PATHINFO_EXTENSION);
                        $newFilename = 'id_front_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['id_front']['tmp_name'], $uploadDir . $newFilename)) {
                            $idFrontPath = 'uploads/' . $newFilename;
                        } else {
                            $uploadSuccess = false;
                        }
                    }

                    // Xử lý upload ảnh mặt sau
                    if (isset($_FILES['id_back']) && $_FILES['id_back']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['id_back']['name'], PATHINFO_EXTENSION);
                        $newFilename = 'id_back_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['id_back']['tmp_name'], $uploadDir . $newFilename)) {
                            $idBackPath = 'uploads/' . $newFilename;
                        } else {
                            $uploadSuccess = false;
                        }
                    }

                    if (!$uploadSuccess || empty($idFrontPath) || empty($idBackPath)) {
                        $error = 'Failed to upload ID Card images. Please try again.';
                    } else {
                        // Phát sinh mật khẩu ngẫu nhiên 6 số để người dùng đăng nhập lần đầu
                        $generated_password = (string)random_int(100000, 999999);
                        $passwordHash = password_hash($generated_password, PASSWORD_DEFAULT);

                        // Thực hiện INSERT tài khoản mới vào database
                        $stmt = $pdo->prepare(
                            'INSERT INTO Users (full_name, dob, email, phone, password, role, is_first_login, id_front, id_back, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, "user", TRUE, ?, ?, "pending", NOW())'
                        );
                        $stmt->execute([
                            $fullName,
                            $birthday,
                            $email,
                            $phone,
                            $passwordHash,
                            $idFrontPath,
                            $idBackPath
                        ]);

                        $success = 'Account registered successfully!';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Gọi file giao diện hiển thị
require_once __DIR__ . '/../user/Register.php';
?>
