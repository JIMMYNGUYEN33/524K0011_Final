<?php
// 1. Khởi động session sạch sẽ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Nhúng các helper và kết nối database
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../config/db_config.php';
global $pdo;

// 3. Khóa trang bảo vệ quyền Admin
require_admin();

$error = '';
$success = '';
$redirect_url = ''; // Dùng biến này để định hướng chuyển trang tự động

// 4. Lấy ID giao dịch từ tham số GET trên URL
$transactionId = $_GET['id'] ?? null;

if (!$transactionId) {
    header("Location: AdminTransactions.php");
    exit();
}

// 5. Xử lý phê duyệt (Approve) hoặc từ chối (Reject) giao dịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        // Bắt đầu một Transaction trong Database để đảm bảo an toàn dữ liệu
        $pdo->beginTransaction();

        // Lấy thông tin chi tiết giao dịch hiện tại để xử lý tiền tệ
        $stmtTx = $pdo->prepare("SELECT * FROM Transactions WHERE id = ? FOR UPDATE");
        $stmtTx->execute([$transactionId]);
        $tx = $stmtTx->fetch();

        if (!$tx) {
            throw new Exception("Transaction not found.");
        }

        if ($tx['status'] !== 'pending') {
            throw new Exception("This transaction has already been processed.");
        }

        if ($action === 'approve') {
            // --- XỬ LÝ KHI PHÊ DUYỆT ---
            
            if ($tx['type'] === 'transfer') {
                // 1. Tính tổng tiền người gửi phải trả (Tiền chuyển + Phí nếu người gửi trả)
                $total_deduct = $tx['amount'];
                if ($tx['fee_payer'] === 'sender') {
                    $total_deduct += $tx['fee'];
                }

                // Kiểm tra xem số dư người gửi có đủ tại thời điểm duyệt không
                $stmtCheckSender = $pdo->prepare("SELECT balance FROM Users WHERE id = ?");
                $stmtCheckSender->execute([$tx['user_id']]);
                $sender = $stmtCheckSender->fetch();

                if (!$sender || $sender['balance'] < $total_deduct) {
                    throw new Exception("Sender does not have enough balance to complete this transaction.");
                }

                // 2. TRỪ TIỀN NGƯỜI GỬI (Lỗi cũ ở đây: Quên chưa trừ tiền)
                $stmtDeductSender = $pdo->prepare("UPDATE Users SET balance = balance - ? WHERE id = ?");
                $stmtDeductSender->execute([$total_deduct, $tx['user_id']]);

                // 3. Tính số tiền người nhận được hưởng (Trừ phí nếu người nhận chịu phí)
                $received_amount = $tx['amount'];
                if ($tx['fee_payer'] === 'receiver') {
                    $received_amount -= $tx['fee'];
                }

                // 4. CỘNG TIỀN NGƯỜI NHẬN
                $stmtUpdateReceiver = $pdo->prepare("UPDATE Users SET balance = balance + ? WHERE id = ?");
                $stmtUpdateReceiver->execute([$received_amount, $tx['receiver_id']]);

            } elseif ($tx['type'] === 'withdraw') {
                // Nếu rút tiền: Đã trừ tiền người rút từ lúc tạo lệnh pending thì ở đây chỉ cập nhật trạng thái.
                // Nếu chưa trừ lúc tạo lệnh, bà chạy câu lệnh trừ tiền ở đây:
                /*
                $total_withdraw_deduct = $tx['amount'] + $tx['fee'];
                $stmtDeductWithdraw = $pdo->prepare("UPDATE Users SET balance = balance - ? WHERE id = ?");
                $stmtDeductWithdraw->execute([$total_withdraw_deduct, $tx['user_id']]);
                */
            }

            // Cập nhật trạng thái giao dịch thành hoàn tất (completed/success tùy DB)
            $stmtUpdateTx = $pdo->prepare("UPDATE Transactions SET status = 'completed', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmtUpdateTx->execute([$_SESSION['user_id'] ?? null, $transactionId]);

            $success = "Transaction approved successfully! Sender's balance deducted and receiver's balance credited.";
            $redirect_url = 'AdminTransactions.php'; // Chuyển hướng về danh sách
            
        } elseif ($action === 'reject') {
            // --- XỬ LÝ KHI TỪ CHỐI GIAO DỊCH ---

            // Cập nhật trạng thái giao dịch thành Hủy bỏ (cancelled/failed tùy DB)
            $stmtUpdateTx = $pdo->prepare("UPDATE Transactions SET status = 'cancelled', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmtUpdateTx->execute([$_SESSION['user_id'] ?? null, $transactionId]);

            // HOÀN TIỀN: Chỉ thực hiện hoàn tiền nếu hệ thống đã trừ tiền của họ từ lúc tạo lệnh pending!
            // Do chuyển khoản nhóm Như không trừ tiền trước, nên khi từ chối KHÔNG cần cộng hoàn lại (tránh trùng lặp tiền).
            // Nếu là rút tiền (đã trừ từ trước khi pending) thì hoàn lại tiền rút + phí cho họ:
            if ($tx['type'] === 'withdraw') {
                $refund_amount = $tx['amount'] + $tx['fee'];
                $stmtRefund = $pdo->prepare("UPDATE Users SET balance = balance + ? WHERE id = ?");
                $stmtRefund->execute([$refund_amount, $tx['user_id']]);
            }

            $success = "Transaction rejected successfully! No funds were deducted from the sender.";
            $redirect_url = 'AdminTransactions.php'; // Chuyển hướng về danh sách
        }

        // Commit (Lưu) mọi thay đổi vào Database
        $pdo->commit();
    } catch (Exception $e) {
        // Hoàn tác nếu có bất kỳ lỗi nào xảy ra trong quá trình xử lý
        $pdo->rollBack();
        $error = "Error processing transaction: " . $e->getMessage();
    }
}

// 6. Truy vấn lấy thông tin chi tiết giao dịch cùng thông tin người gửi & người nhận
try {
    $stmt = $pdo->prepare(
        'SELECT t.*, 
                sender.full_name AS sender_name, sender.phone AS sender_phone, sender.email AS sender_email,
                receiver.full_name AS receiver_name, receiver.phone AS receiver_phone
         FROM Transactions t
         LEFT JOIN Users sender ON sender.id = t.user_id
         LEFT JOIN Users receiver ON receiver.id = t.receiver_id
         WHERE t.id = ? 
         LIMIT 1'
    );
    $stmt->execute([$transactionId]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        header("Location: AdminTransactions.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Định cấu hình giao diện theo loại giao dịch
$is_withdraw = ($transaction['type'] === 'withdraw');
$type_label = $is_withdraw ? 'Withdrawal (Rút tiền)' : 'Transfer (Chuyển tiền)';
$header_bg = $is_withdraw ? 'bg-blue-600' : 'bg-purple-600';
$icon_class = $is_withdraw ? 'fa-building-columns' : 'fa-money-bill-transfer';
$text_amount_color = $is_withdraw ? 'text-blue-600' : 'text-purple-600';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <?php if (!empty($redirect_url)): ?>
        <script>
            setTimeout(function() {
                window.location.href = '<?= $redirect_url ?>';
            }, 1500);
        </script>
    <?php endif; ?>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen flex flex-col overflow-hidden">

    <header class="h-16 bg-indigo-600 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
        <div class="flex items-center gap-2 text-white">
            <i class="fa-solid fa-shield-halved text-xl"></i>
            <span class="text-xl font-bold">BeePay</span>
        </div>
        <div class="flex items-center gap-6 text-white">
            <div class="text-right">
                <div class="text-sm opacity-90">Administrator</div>
                <div class="text-sm font-semibold"><?= h($_SESSION['user_name'] ?? 'System Administrator') ?></div>
            </div>
            <form action="<?= base_url('/auth/logout.php') ?>" method="POST" class="m-0">
                <button type="submit" class="btn-logout flex items-center gap-2 px-3 py-1.5 rounded bg-indigo-700 hover:bg-indigo-800 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <aside class="w-64 bg-white shadow-md flex flex-col overflow-y-auto z-0 shrink-0">
            <nav class="flex-1 py-4">
                <ul class="space-y-1">
                    <li>
                        <a href="AdminDashboard.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-house"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="AdminPending.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-regular fa-clock"></i> Pending Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminVerified.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-regular fa-user"></i> Verified Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminDisabled.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-user-xmark"></i> Disabled Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminLocked.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-lock"></i> Locked Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminTransactions.php" class="menu-item flex items-center gap-3 px-6 py-3 bg-indigo-50 text-indigo-600 font-medium">
                            <i class="fa-solid fa-users"></i> Pending Transactions
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            
            <div class="mb-6 flex items-center justify-between">
                <a href="AdminTransactions.php" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <?php if ($success): ?>
                    <span class="bg-emerald-100 text-emerald-800 text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i> <?= $success ?>
                    </span>
                <?php endif; ?>
                <?php if ($error): ?>
                    <span class="bg-rose-100 text-rose-800 text-sm font-semibold px-4 py-2 rounded-xl flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                
                <div class="<?= $header_bg ?> p-8 flex items-center gap-6 text-white">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center text-3xl shrink-0">
                        <i class="fa-solid <?= $icon_class ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Transaction Request</h2>
                        <p class="text-white/80 text-sm mt-1">ID: <?= h($transaction['transaction_code'] ?: $transaction['id']) ?></p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 pb-8 border-b border-gray-100">
                        
                        <div class="flex items-start gap-4">
                            <i class="fa-regular fa-user text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">User Account (Người gửi)</p>
                                <p class="text-gray-800 font-medium mt-0.5">
                                    <?= h($transaction['sender_name']) ?> (ID: <?= h($transaction['user_id']) ?>) - SĐT: <?= h($transaction['sender_phone']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-money-bill-transfer text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Transaction Type</p>
                                <p class="text-gray-800 font-medium mt-0.5"><?= $type_label ?></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-building-columns text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">
                                    <?= $is_withdraw ? 'Withdrawal Method (Ngân hàng nhận)' : 'Receiver (Người nhận)' ?>
                                </p>
                                <p class="text-gray-800 font-medium mt-0.5">
                                    <?php if ($is_withdraw): ?>
                                        <?= h($transaction['bank_name'] ?? 'Vietcombank') ?> - <?= h($transaction['card_number'] ?? '10123456789') ?>
                                    <?php else: ?>
                                        <?= h($transaction['receiver_name'] ?? '-') ?> (SĐT: <?= h($transaction['receiver_phone'] ?? '-') ?>)
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-regular fa-comment-dots text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Memo (Nội dung)</p>
                                <p class="text-gray-800 font-medium mt-0.5"><?= h($transaction['note'] ?: 'No memo provided') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="grid grid-cols-2 gap-y-6 gap-x-12 pt-8">
                        <div>
                            <p class="text-sm text-gray-400">Amount</p>
                            <p class="<?= $text_amount_color ?> font-bold text-2xl mt-1">
                                <?= h(format_money($transaction['amount'])) ?> VND
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Status</p>
                            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full mt-1">
                                <i class="fa-regular fa-clock"></i> <?= ucfirst(h($transaction['status'])) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Requested Time</p>
                            <p class="text-gray-800 font-bold mt-1">
                                <?= !empty($transaction['created_at']) ? date('d/m/Y - H:i', strtotime($transaction['created_at'])) : '-' ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Fee</p>
                            <p class="text-gray-800 font-bold mt-1"><?= h(format_money($transaction['fee'] ?? 0)) ?> VND</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Transaction Actions</h3>
                
                <?php if ($transaction['status'] === 'pending'): ?>
                    <div class="flex flex-wrap gap-4">
                        <form method="POST" action="" class="flex-1 min-w-[180px] m-0" onsubmit="return confirm('Do you want to APPROVE this transaction? This will deduct from the sender and credit the receiver.');">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors duration-200">
                                <i class="fa-regular fa-circle-check"></i> Approve Transaction
                            </button>
                        </form>
                        
                        <form method="POST" action="" class="flex-1 min-w-[180px] m-0" onsubmit="return confirm('Do you want to REJECT this transaction? No money will be deducted.');">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors duration-200">
                                <i class="fa-regular fa-circle-xmark"></i> Reject Transaction
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-100 text-gray-600 text-center p-4 rounded-xl font-semibold">
                        This transaction has been processed (Status: <?= ucfirst(h($transaction['status'])) ?>)
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>