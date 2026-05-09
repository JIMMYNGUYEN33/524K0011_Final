<?php
// 1. Phải khởi động session trước khi muốn xóa nó
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Nhúng file db_config và các helper (để có hàm base_url)
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../helpers/auth.php'; // Nhúng file này để chạy được hàm base_url nếu có

// 3. Xóa sạch và hủy hoàn toàn Session cũ
$_SESSION = array();
session_unset();
session_destroy();

// 4. Chuyển hướng an toàn về trang đăng nhập
// Sử dụng đường dẫn tương đối cực kỳ an toàn nếu hàm base_url bị lỗi
header('Location: login.php');
exit();
?>