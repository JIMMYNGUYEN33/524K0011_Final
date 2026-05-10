<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../config/db_config.php';
global $pdo;


require_admin();


try {
    $stmt = $pdo->query(
        'SELECT t.*, sender.full_name AS sender_name, sender.phone AS sender_phone,
                receiver.full_name AS receiver_name, receiver.phone AS receiver_phone
        FROM Transactions t
        LEFT JOIN Users sender ON sender.id = t.user_id
        LEFT JOIN Users receiver ON receiver.id = t.receiver_id
        WHERE t.status = "pending"
            AND t.type IN ("withdraw", "transfer")
            AND t.amount > 5000000
        ORDER BY t.created_at DESC'
    );
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


$pending_count = count($transactions);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Transactions - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Pending Transactions</h1>
                <p class="text-gray-500 mt-1">Withdrawals and transfers awaiting approval (<?= $pending_count ?>)</p>
            </div>

            <div class="flex flex-col gap-4">
                
                <?php if (empty($transactions)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                        <i class="fa-solid fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p>No pending transactions over 5,000,000 VND.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <?php 
                            // Xác định icon và màu sắc cho từng loại giao dịch
                            $is_withdraw = ($tx['type'] === 'withdraw');
                            $type_label = $is_withdraw ? 'Withdrawal (Rút tiền)' : 'Transfer (Chuyển tiền)';
                            $icon_class = $is_withdraw ? 'fa-building-columns' : 'fa-money-bill-transfer';
                            $bg_icon_color = $is_withdraw ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600';
                            $text_amount_color = $is_withdraw ? 'text-blue-600' : 'text-purple-600';
                            
                            $receiver_info = !empty($tx['receiver_name']) ? " • To: " . h($tx['receiver_name']) : "";
                        ?>
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col md:flex-row md:items-center justify-between hover:shadow-md hover:border-indigo-200 transition-all duration-200 group gap-4">
                            
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full <?= $bg_icon_color ?> flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid <?= $icon_class ?>"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        <?= h($tx['sender_name']) ?>
                                    </h4>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 mt-1">
                                        <span class="font-medium text-gray-700"><?= $type_label ?></span>
                                        <span>•</span>
                                        <span>SĐT: <?= h($tx['sender_phone']) ?></span>
                                        <?php if (!empty($receiver_info)): ?>
                                            <span>•</span>
                                            <span class="text-indigo-500"><?= $receiver_info ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-3 md:pt-0">
                                <div class="text-left md:text-right">
                                    <div class="text-lg font-bold <?= $text_amount_color ?> mb-1">
                                        - <?= h(format_money($tx['amount'])) ?> VND
                                    </div>
                                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="fa-regular fa-clock"></i> Pending
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <a href="AdPendingTransactionsDetail.php?id=<?= h($tx['id']) ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
                                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Review & Action
                                    </a>
                                    <a href="AdPendingTransactionsDetail.php?id=<?= h($tx['id']) ?>" class="p-2 text-gray-400 hover:text-indigo-500 transition-colors">
                                        <i class="fa-solid fa-chevron-right text-lg"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </div>

        </main>
    </div>

</body>
</html>