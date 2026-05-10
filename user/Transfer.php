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
    $result = $wallet->transfer(
        $user['id'],
        trim($_POST['receiver_phone'] ?? ''),
        (float) ($_POST['amount'] ?? 0),
        trim($_POST['note'] ?? ''),
        ($_POST['fee_payer'] ?? 'sender') === 'sender'
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
    <title>BeePay - Transfer Money</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_transfer.css">
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="page-title-group">
                <h1 class="page-title">Transfer Money</h1>
                <p class="page-subtitle">Send money instantly to another BeePay wallet</p>
            </div>
        </header>

        <div class="transfer-card">
            <?php if ($message): ?>
                <div class="alert alert-<?= h($messageType) ?> text-center"><?= h($message) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="in_gr">
                    <label>Receiver Phone <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-user-pen"></i>
                        <input required type="text" name="receiver_phone" placeholder="Enter receiver phone number" value="<?= h($_POST['receiver_phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Amount (VND) <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <input required type="number" name="amount" min="1000" placeholder="Enter amount to transfer" value="<?= h($_POST['amount'] ?? '') ?>">
                    </div>
                </div>

                <div class="in_gr">
                    <label>Fee Payer</label>
                    <select name="fee_payer" class="form-select">
                        <option value="sender" <?= ($_POST['fee_payer'] ?? 'sender') === 'sender' ? 'selected' : '' ?>>Sender pays fee</option>
                        <option value="receiver" <?= ($_POST['fee_payer'] ?? '') === 'receiver' ? 'selected' : '' ?>>Receiver pays fee</option>
                    </select>
                </div>

                <div class="in_gr">
                    <label>Note</label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-comment-dots" style="top: 25px;"></i>
                        <textarea name="note" rows="3" class="note-textarea" placeholder="Optional message for receiver"><?= h($_POST['note'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-transfer">Transfer</button>
            </form>
        </div>
    </div>
</body>
</html>
