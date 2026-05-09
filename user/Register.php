<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_register.css">
</head>
<body>
    <div class="app-container">
        <div class="back-navigation">
            <a href="login.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <h1 class="brand-title">BeePay</h1>
        <p class="brand-subtitle">Secure digital payment solution</p>

        <div class="Register-card">
            <form action="" method="POST" enctype="multipart/form-data">
                <h2>Create Account</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center" style="font-size: 14px; padding: 10px; margin-bottom: 15px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success text-center" style="font-size: 14px; padding: 15px; margin-bottom: 15px;">
                        <h5>🎉 <?= htmlspecialchars($success) ?></h5>
                        <p style="margin-bottom: 5px;">Your temporary password for the first login is:</p>
                        <strong style="font-size: 20px; color: #155724; background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block;">
                            <?= htmlspecialchars($generated_password) ?>
                        </strong>
                        <p class="mt-2" style="font-size: 12px; color: #666;">(Please save this password to log in and change it.)</p>
                    </div>
                <?php endif; ?>
                
                <div class="in_gr">
                    <label>Full Name</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input required type="text" name="full_name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Birthday</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-calendar"></i>
                        <input required type="date" name="birthday" placeholder="Select your birthdate" value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Email Address</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input required type="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Phone Number</label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-phone"></i>
                        <input required type="tel" name="phone" placeholder="Enter your phone number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="id-verification-section">
                    <h3 class="verification-title">ID Verification</h3>
                    
                    <div class="upload-box" id="upload-front" onclick="document.getElementById('file-front').click();" style="cursor: pointer;">
                        <input type="file" id="file-front" name="id_front" accept="image/*" required hidden onchange="displayFileName(this, 'front-text')">
                        <div class="upload-content">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <p class="upload-text" id="front-text">Upload ID Card - Front Side</p>
                            <span class="upload-hint">PNG, JPG up to 5MB</span>
                        </div>
                    </div>

                    <div class="upload-box mt-3" id="upload-back" onclick="document.getElementById('file-back').click();" style="cursor: pointer;">
                        <input type="file" id="file-back" name="id_back" accept="image/*" required hidden onchange="displayFileName(this, 'back-text')">
                        <div class="upload-content">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <p class="upload-text" id="back-text">Upload ID Card - Back Side</p>
                            <span class="upload-hint">PNG, JPG up to 5MB</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register">Register</button>
            </form>

            <p class="signin-text">
                Already have an account? <a href="login.php">Sign In</a>
            </p>
        </div>
    </div>

    <script>
        function displayFileName(input, targetId) {
            const fileName = input.files[0] ? input.files[0].name : "Upload ID Card";
            document.getElementById(targetId).innerHTML = `<strong>Selected:</strong> ${fileName}`;
            document.getElementById(targetId).style.color = "#FFC800"; // Đổi màu chữ highlight
        }
    </script>
</body>
</html>