<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../core/WalletDAL.php';
require_once __DIR__ . '/../core/WalletBLL.php';

require_verified_user();

$user = current_user();
$message = '';
$messageType = '';
$show_popup = false;
$popup_type = ''; // 'instant_success' hoặc 'pending_approval'
$trans_amount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) ($_POST['amount'] ?? 0);
    $trans_amount = $amount;

    $wallet = new WalletBLL(new WalletDAL($pdo), $pdo);
    
    // Giữ nguyên hàm transfer gốc chuẩn nghiệp vụ BLL của nhóm bà
    $result = $wallet->transfer(
        $user['id'],
        trim($_POST['receiver_phone'] ?? ''),
        $amount,
        trim($_POST['note'] ?? ''),
        ($_POST['fee_payer'] ?? 'sender') === 'sender'
    );

    $message = $result['message'];
    $messageType = !empty($result['success']) ? 'success' : 'danger';

    // Nếu giao dịch được xử lý thành công ở tầng BLL
    if (!empty($result['success'])) {
        $show_popup = true;
        // Phân luồng hiển thị dựa trên hạn mức 5,000,000 VND
        if ($amount < 5000000) {
            $popup_type = 'instant_success';
        } else {
            $popup_type = 'pending_approval';
        }
    }
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
    
    <style>
        /* CSS Popup Hiển thị trạng thái giao dịch Chuyển tiền */
        .transfer-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .transfer-popup-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .transfer-popup-box {
            background: #ffffff;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            padding: 30px 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .transfer-popup-overlay.show .transfer-popup-box {
            transform: scale(1);
        }
        .popup-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            font-size: 28px;
        }
        /* Style cho trạng thái Thành công tức thì (< 5tr) */
        .icon-success {
            background: #dcfce7;
            color: #16a34a;
        }
        /* Style cho trạng thái Chờ duyệt (>= 5tr) */
        .icon-pending {
            background: #fef3c7;
            color: #d97706;
        }
        .popup-status-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .popup-status-desc {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .btn-popup-confirm {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s ease;
        }
        .btn-popup-confirm:hover {
            background: #1d4ed8;
        }
    </style>
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
            <?php if ($message && !$show_popup): ?>
                <div class="alert alert-<?= h($messageType) ?> text-center mb-4"><?= h($message) ?></div>
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

    <?php if ($show_popup): ?>
        <div class="transfer-popup-overlay show" id="transferPopup">
            <div class="transfer-popup-box">
                <?php if ($popup_type === 'instant_success'): ?>
                    <div class="popup-icon-circle icon-success">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h3 class="popup-status-title">Xác thực thành công</h3>
                    <p class="popup-status-desc">
                        Giao dịch chuyển khoản số tiền <strong><?= h(format_money($trans_amount)) ?></strong> đến tài khoản người nhận đã hoàn tất thành công!
                    </p>
                <?php else: ?>
                    <div class="popup-icon-circle icon-pending">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h3 class="popup-status-title">Chờ xác thực</h3>
                    <p class="popup-status-desc">
                        Giao dịch chuyển khoản số tiền lớn (<strong><?= h(format_money($trans_amount)) ?></strong>) đã được ghi nhận và đang <strong>chờ Admin phê duyệt</strong> để đảm bảo an toàn.
                    </p>
                <?php endif; ?>

                <button class="btn-popup-confirm" onclick="closeTransferPopup()">Xác nhận</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function closeTransferPopup() {
            const popup = document.getElementById('transferPopup');
            if (popup) {
                popup.classList.remove('show');
                // Tự động điều hướng về trang chủ sau khi tắt popup để cập nhật số dư mới nhất
                window.location.href = 'Home.php';
            }
        }
    </script>
</body>
</html>