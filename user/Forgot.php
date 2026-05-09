<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_forgot.css">
</head>
<body>
    <div class="app-container">
        <div class="back-navigation">
            <a href="Login.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <h1 class="brand-title">BeePay</h1>
        <p class="brand-subtitle">Secure digital payment solution</p>

        <div class="Forgot-card">
            <form action="#" method="POST">
                <h2>Forgot Password</h2>
                <p class="forgot-desc">Enter your email or phone number below. We will send you instructions to reset your password.</p>
                
                <div class="in_gr">
                    <label>Email or Phone Number</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input required type="text" placeholder="Enter registered email or phone">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Send Reset Link</button>
            </form>

            <p class="back-to-login">
                Remember your password? <a href="Login.php">Sign In</a>
            </p>
        </div>
    </div>
</body>
</html>