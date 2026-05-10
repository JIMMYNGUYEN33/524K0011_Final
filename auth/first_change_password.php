<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';


require __DIR__ . '/../config/db_config.php';
global $pdo;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $userId = $_SESSION['user_id'];

            
            $stmt = $pdo->prepare('UPDATE Users SET password = ?, is_first_login = FALSE WHERE id = ?');
            $stmt->execute([$newPasswordHash, $userId]);

            
            session_write_close();

            
            header("Location: ../user/Home.php");
            exit();

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$isFirstPasswordChange = true;
require_once __DIR__ . '/../user/ChangePassword.php';
?>
