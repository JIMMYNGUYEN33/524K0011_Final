<?php
require_once __DIR__ . '/../helpers/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/admin/users.php');
}

$userId = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? AND role = "user" LIMIT 1');
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    set_flash('error', 'User not found.');
    redirect_to('/admin/users.php');
}

if ($action === 'verify') {
    $stmt = $pdo->prepare('UPDATE Users SET status = "verified" WHERE id = ?');
    $stmt->execute([$userId]);
    set_flash('success', 'User verified.');
} elseif ($action === 'cancel') {
    $stmt = $pdo->prepare('UPDATE Users SET status = "disabled" WHERE id = ?');
    $stmt->execute([$userId]);
    set_flash('success', 'User disabled.');
} elseif ($action === 'request_update') {
    $stmt = $pdo->prepare('UPDATE Users SET status = "waiting_update" WHERE id = ?');
    $stmt->execute([$userId]);
    set_flash('success', 'ID card update requested.');
} elseif ($action === 'unlock') {
    $stmt = $pdo->prepare(
        'UPDATE Users
        SET wrong_login_count = 0,
            abnormal_login_count = 0,
            locked_until = NULL,
            is_permanently_locked = FALSE,
            permanently_locked_at = NULL
        WHERE id = ?'
    );
    $stmt->execute([$userId]);
    set_flash('success', 'User unlocked.');
} else {
    set_flash('error', 'Invalid action.');
}

redirect_to('/admin/user_detail.php?id=' . $userId);
