<?php
require_once __DIR__ . '/../helpers/ui.php';

if (current_user()) {
    redirect_after_login(current_user());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM Users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Invalid username or password.';
    } else {
        $isAdmin = $user['role'] === 'admin';

        if (!$isAdmin && $user['status'] === 'disabled') {
            $error = 'This account has been disabled, please contact the hotline 18001008.';
        } elseif (!$isAdmin && (int) $user['is_permanently_locked'] === 1) {
            $error = 'Account has been locked due to entering the wrong password many times, please contact the administrator for support.';
        } elseif (!$isAdmin && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $error = 'Account is currently locked, please try again in 1 minute.';
        } elseif (password_verify($passwordInput, $user['password'])) {
            if (!$isAdmin) {
                $stmt = $pdo->prepare(
                    'UPDATE Users
                    SET wrong_login_count = 0,
                        abnormal_login_count = 0,
                        locked_until = NULL
                    WHERE id = ?'
                );
                $stmt->execute([$user['id']]);
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            $stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            redirect_after_login($stmt->fetch());
        } elseif ($isAdmin) {
            $error = 'Invalid username or password.';
        } else {
            $newWrongCount = (int) $user['wrong_login_count'] + 1;

            if ($newWrongCount >= 3) {
                if ((int) $user['abnormal_login_count'] >= 1) {
                    $stmt = $pdo->prepare(
                        'UPDATE Users
                        SET wrong_login_count = 0,
                            locked_until = NULL,
                            is_permanently_locked = TRUE,
                            permanently_locked_at = NOW()
                        WHERE id = ?'
                    );
                    $stmt->execute([$user['id']]);
                    $error = 'Account has been locked permanently because of too many failed logins.';
                } else {
                    $stmt = $pdo->prepare(
                        'UPDATE Users
                        SET wrong_login_count = 0,
                            abnormal_login_count = abnormal_login_count + 1,
                            locked_until = DATE_ADD(NOW(), INTERVAL 1 MINUTE)
                        WHERE id = ?'
                    );
                    $stmt->execute([$user['id']]);
                    $error = 'Wrong password 3 times. Account is locked for 1 minute.';
                }
            } else {
                $stmt = $pdo->prepare('UPDATE Users SET wrong_login_count = ? WHERE id = ?');
                $stmt->execute([$newWrongCount, $user['id']]);
                $error = 'Invalid username or password.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/style.css') ?>">
</head>
<body>
    <h1 style="text-align: center; color: aliceblue; margin-top: 30px;">BeePay</h1>
    <p style="text-align: center; color: aliceblue;">Secure digital payment solution</p>

    <div class="Login-card">
        <form method="post">
            <h2>Sign In</h2>

            <?php if ($error): ?>
                <div class="alert-box alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <div class="in_gr">
                <label for="username">Email or Phone Number</label>
                <div class="in_wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input id="username" required type="text" name="username" placeholder="Enter email or phone" value="<?= h($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="in_gr">
                <label for="password">Password</label>
                <div class="in_wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password" required type="password" name="password" placeholder="Enter password">
                </div>
            </div>

            <div class="options">
                <label><input type="checkbox"> Remember Me</label>
                <a href="<?= base_url('/auth/forgot_password.php') ?>">Forgot password?</a>
            </div>

            <button type="submit" class="btn-signin">Sign In</button>
        </form>

        <p class="auth-link">Don't have an account? <a href="<?= base_url('/auth/register.php') ?>">Register now</a></p>
    </div>
</body>
</html>
