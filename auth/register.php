<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../helpers/mailer.php';
 
require __DIR__ . '/../config/db_config.php';
 
global $pdo;
 
$error = '';
$success = '';
$generated_password = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
 
    if (empty($fullName) || empty($birthday) || empty($email) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address format.';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = 'Phone number must be 10 or 11 digits.';
    } else {
        if (!isset($pdo) || !$pdo) {
            $error = 'Database connection lost. Please check db_config.php.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id FROM Users WHERE email = ? OR phone = ? LIMIT 1');
                $stmt->execute([$email, $phone]);
 
                if ($stmt->fetch()) {
                    $error = 'Email or Phone number is already registered.';
                } else {
                    $uploadDir = __DIR__ . '/../uploads/';
 
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
 
                    $idFrontPath = '';
                    $idBackPath  = '';
                    $uploadSuccess = true;
 
                    if (isset($_FILES['id_front']) && $_FILES['id_front']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['id_front']['name'], PATHINFO_EXTENSION);
                        $newFilename = 'id_front_' . time() . '_' . uniqid() . '.' . $ext;
                        if (move_uploaded_file($_FILES['id_front']['tmp_name'], $uploadDir . $newFilename)) {
                            $idFrontPath = 'uploads/' . $newFilename;
                        } else {
                            $uploadSuccess = false;
                        }
                    }
 
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
                        $generated_password = (string)random_int(100000, 999999);
                        $passwordHash = password_hash($generated_password, PASSWORD_DEFAULT);
 
                        $stmt = $pdo->prepare(
                            'INSERT INTO Users (full_name, dob, email, phone, address, password, role, is_first_login, id_front, id_back, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, "user", TRUE, ?, ?, "pending", NOW())'
                        );
                        $stmt->execute([
                            $fullName,
                            $birthday,
                            $email,
                            $phone,
                            $address,
                            $passwordHash,
                            $idFrontPath,
                            $idBackPath
                        ]);
                        $emailBody = "
                            <div style='font-family: Inter, sans-serif; max-width: 480px; margin: auto; padding: 30px; border-radius: 16px; background: #fffef5; border: 1px solid #ffe57f;'>
                                <h2 style='color: #392802; text-align: center;'>🎉 Welcome to BeePay!</h2>
                                <p style='color: #555;'>Hi <strong>{$fullName}</strong>,</p>
                                <p style='color: #555;'>Your account has been registered successfully. Here is your login information:</p>
                                <table style='width:100%; margin: 20px 0;'>
                                    <tr>
                                        <td style='color:#888; padding: 6px 0;'>Email / Phone</td>
                                        <td style='font-weight:700; color:#333;'>{$email} / {$phone}</td>
                                    </tr>
                                    <tr>
                                        <td style='color:#888; padding: 6px 0;'>Temporary Password</td>
                                        <td>
                                            <span style='font-size: 24px; font-weight: 800; color: #392802; background: #ffea00; padding: 4px 16px; border-radius: 8px; letter-spacing: 4px;'>
                                                {$generated_password}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <p style='color: #e65100; font-size: 13px;'>⚠️ You will be required to change this password on your first login.</p>
                                <p style='color: #555; font-size: 12px;'>Your account is currently pending verification by our admin team. You will be notified once verified.</p>
                                <hr style='border: none; border-top: 1px solid #ffe57f; margin: 20px 0;'>
                                <p style='text-align:center; color: #aaa; font-size: 11px;'>BeePay – Secure digital payment solution</p>
                            </div>
                        ";
 
                        $mailSent = send_mail($email, $fullName, '🐝 BeePay – Your Account Has Been Created', $emailBody);
 
                        if ($mailSent) {
                            $generated_password = '';
                            $success = 'email_sent';
                        } else {
                            $success = 'show_password';
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
 
require_once __DIR__ . '/../user/Register.php';
?>