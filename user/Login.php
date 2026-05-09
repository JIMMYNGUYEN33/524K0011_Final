<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_login.css">
</head>
<body>
    <div class="app-container">
        <h1 style="text-align: center; color: rgb(255, 200, 0); margin-top: 30px;">BeePay</h1>
        <p style="text-align: center; color: rgb(234, 176, 77);">Secure digital payment solution</p>
        
        <div class="Login-card">
            <form method="POST" action="">
                <h2>Sign In</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="text-align: center; font-size: 14px; padding: 8px; margin-bottom: 15px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="in_gr">
                    <label>Email or Phone Number</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input required type="text" name="username" placeholder="Enter email or phone" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Password</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input required type="password" name="password" placeholder="Enter password">
                    </div>
                </div>

                <div class="options">
                    <label><input type="checkbox" name="remember_me">Remember Me</label>
                    <a href="../auth/forgot_password.php">Forgot password ?</a>
                </div>

                <button type="submit" class="btn-signin w-100">Sign In</button>
            </form>
            
            <p style="text-align: center; margin-top: 15px;">Don't have an account? <a href="../auth/register.php">Register now</a></p>
        </div>
    </div>
</body>
</html>