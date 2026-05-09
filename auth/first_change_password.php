<?php
require_once __DIR__ . '/../helpers/ui.php';

require_login();

$user = current_user();

if ($user['role'] === 'admin' || (int) $user['is_first_login'] === 0) {
    redirect_after_login($user);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        $error = 'New password must have at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password confirmation does not match.';
    } else {
        $stmt = $pdo->prepare('UPDATE Users SET password = ?, is_first_login = FALSE WHERE id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);

        set_flash('success', 'Password changed. You can use the system now.');
        redirect_to('/index.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - First Password Change</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/style.css') ?>">
</head>
<body>
    <h1 style="text-align: center; color: aliceblue; margin-top: 30px;">BeePay</h1>
    <p style="text-align: center; color: aliceblue;">Change your temporary password</p>

    <div class="Login-card">
        <form method="post">
            <h2>First Password Change</h2>

            <?php if ($error): ?>
                <div class="alert-box alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <div class="in_gr">
                <label for="new_password">New password</label>
                <div class="in_wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input id="new_password" type="password" name="new_password" required>
                </div>
            </div>

            <div class="in_gr">
                <label for="confirm_password">Confirm new password</label>
                <div class="in_wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input id="confirm_password" type="password" name="confirm_password" required>
                </div>
            </div>

            <button type="submit" class="btn-signin">Change password</button>
        </form>
        <p class="auth-link"><a href="<?= base_url('/auth/logout.php') ?>">Logout</a></p>
    </div>
</body>
</html>
