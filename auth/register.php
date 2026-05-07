<?php
require_once __DIR__ . '/../helpers/ui.php';

$errors = [];
$generatedPassword = null;

function save_id_card_upload($field)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please upload both ID card images.');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $originalName = $_FILES[$field]['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException('ID card images must be JPG, PNG, or WEBP.');
    }

    $uploadDir = __DIR__ . '/../uploads/id_cards';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = $field . '_' . date('YmdHis') . '_' . random_string(8) . '.' . $extension;
    $target = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new RuntimeException('Cannot save uploaded ID card image.');
    }

    return 'uploads/id_cards/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($phone === '') {
        $errors[] = 'Phone is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM Users WHERE phone = ? OR email = ? LIMIT 1');
        $stmt->execute([$phone, $email]);

        if ($stmt->fetch()) {
            $errors[] = 'Phone or email already exists.';
        }
    }

    if (!$errors) {
        try {
            $frontPath = save_id_card_upload('id_front');
            $backPath = save_id_card_upload('id_back');
            $generatedPassword = random_string(6);
            $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO Users
                (role, phone, email, password, full_name, dob, address, id_front, id_back, status, is_first_login)
                VALUES
                ("user", ?, ?, ?, ?, ?, ?, ?, ?, "pending", TRUE)'
            );
            $stmt->execute([
                $phone,
                $email,
                $passwordHash,
                $fullName,
                $dob !== '' ? $dob : null,
                $address,
                $frontPath,
                $backPath,
            ]);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/style.css') ?>">
</head>
<body>
    <h1 style="text-align: center; color: aliceblue; margin-top: 30px;">BeePay</h1>
    <p style="text-align: center; color: aliceblue;">Create your secure digital wallet</p>

    <div class="Login-card wide">
        <form method="post" enctype="multipart/form-data">
            <h2>Register</h2>

            <?php if ($generatedPassword): ?>
                <div class="alert-box alert-success">
                    Register success. Temporary password: <strong><?= h($generatedPassword) ?></strong>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert-box alert-error"><?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="in_gr">
                        <label for="phone">Phone</label>
                        <div class="in_wrapper">
                            <i class="fa-solid fa-phone"></i>
                            <input id="phone" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="in_gr">
                        <label for="email">Email</label>
                        <div class="in_wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input id="email" type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="in_gr">
                <label for="full_name">Full name</label>
                <div class="in_wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input id="full_name" name="full_name" value="<?= h($_POST['full_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="in_gr">
                <label for="dob">Date of birth</label>
                <div class="in_wrapper">
                    <i class="fa-regular fa-calendar"></i>
                    <input id="dob" type="date" name="dob" value="<?= h($_POST['dob'] ?? '') ?>">
                </div>
            </div>

            <div class="in_gr">
                <label for="address">Address</label>
                <div class="in_wrapper">
                    <i class="fa-solid fa-location-dot"></i>
                    <textarea id="address" name="address"><?= h($_POST['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="in_gr">
                        <label for="id_front">ID card front image</label>
                        <div class="in_wrapper">
                            <i class="fa-regular fa-id-card"></i>
                            <input id="id_front" type="file" name="id_front" accept=".jpg,.jpeg,.png,.webp" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="in_gr">
                        <label for="id_back">ID card back image</label>
                        <div class="in_wrapper">
                            <i class="fa-regular fa-id-card"></i>
                            <input id="id_back" type="file" name="id_back" accept=".jpg,.jpeg,.png,.webp" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-signin">Register</button>
        </form>

        <p class="auth-link">Already have an account? <a href="<?= base_url('/auth/login.php') ?>">Sign in</a></p>
    </div>
</body>
</html>
