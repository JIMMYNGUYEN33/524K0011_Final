<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../core/WalletDAL.php';
require_once __DIR__ . '/../core/WalletBLL.php';

require_verified_user();

$user = current_user();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wallet = new WalletBLL(new WalletDAL($pdo), $pdo);
    $result = $wallet->withdraw(
        $user['id'],
        trim($_POST['card_number'] ?? ''),
        trim($_POST['expiration_date'] ?? ''),
        trim($_POST['cvv'] ?? ''),
        (float) ($_POST['amount'] ?? 0),
        trim($_POST['note'] ?? '')
    );

    $message = $result['message'];
    $messageType = !empty($result['success']) ? 'success' : 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Withdraw Money</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_withdraw.css">
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="page-title-group">
                <h1 class="page-title">Withdraw Money</h1>
                <p class="page-subtitle">Transfer money from your wallet to a credit card</p>
            </div>
        </header>

        <div class="withdraw-card">
            <?php if ($message): ?>
                <div class="alert alert-<?= h($messageType) ?> text-center"><?= h($message) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="in_gr">
                    <label>Card Number <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-credit-card"></i>
                        <input required type="text" name="card_number" maxlength="6" placeholder="111111" value="<?= h($_POST['card_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="in_gr mb-0">
                            <label>Expiration Date <span class="required">*</span></label>
                            <div class="in_wrapper">
                                <i class="fa-regular fa-calendar"></i>
                                <input required type="text" name="expiration_date" placeholder="10/10/2022" value="<?= h($_POST['expiration_date'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="in_gr mb-0">
                            <label>CVV <span class="required">*</span></label>
                            <div class="in_wrapper">
                                <i class="fa-solid fa-key"></i>
                                <input required type="password" name="cvv" maxlength="3" placeholder="411">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="in_gr">
                    <label>Amount (VND) <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <input required type="number" name="amount" step="50000" min="50000" placeholder="Enter amount (multiple of 50,000)" value="<?= h($_POST['amount'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Note</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-comment-dots" style="top: 25px;"></i>
                        <textarea name="note" rows="3" class="note-textarea" placeholder="Optional note for this withdrawal"><?= h($_POST['note'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-withdraw">Withdraw</button>
            </form>
        </div>
    </div>
</body>
</html>
