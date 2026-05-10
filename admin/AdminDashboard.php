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

// 4. Lấy các số liệu thống kê thực tế từ Database để hiển thị lên Dashboard
try {
    // Đếm tổng số người dùng (loại trừ tài khoản admin)
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user'");
    $total_users = $stmtUsers->fetchColumn();

    // Đếm số tài khoản đang chờ xác minh (pending)
    $stmtPending = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user' AND status = 'pending'");
    $pending_users = $stmtPending->fetchColumn();

    // Đếm số tài khoản đã xác minh (verified)
    $stmtVerified = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user' AND status = 'verified'");
    $verified_users = $stmtVerified->fetchColumn();

    // Đếm số tài khoản bị vô hiệu hóa (disabled)
    $stmtDisabled = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user' AND status = 'disabled'");
    $disabled_users = $stmtDisabled->fetchColumn();

    // Đếm số tài khoản bị khóa vĩnh viễn (permanently locked)
    $stmtLocked = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user' AND is_permanently_locked = TRUE");
    $locked_users = $stmtLocked->fetchColumn();

    // Đếm số lượng giao dịch rút tiền hoặc chuyển khoản trên 5 triệu đang chờ duyệt
    $stmtPendingTx = $pdo->query("
        SELECT COUNT(*) FROM Transactions 
        WHERE status = 'pending' 
          AND type IN ('withdraw', 'transfer') 
          AND amount > 5000000
    ");
    $pending_tx_count = $stmtPendingTx->fetchColumn();

    // Tính tổng số tiền Nạp thành công (Deposit)
    $stmtTotalDeposit = $pdo->query("SELECT SUM(amount) FROM Transactions WHERE type = 'deposit' AND status = 'completed'");
    $total_deposit = $stmtTotalDeposit->fetchColumn() ?: 0;

    // Tính tổng số tiền Rút thành công (Withdraw)
    $stmtTotalWithdraw = $pdo->query("SELECT SUM(amount) FROM Transactions WHERE type = 'withdraw' AND status = 'completed'");
    $total_withdraw = $stmtTotalWithdraw->fetchColumn() ?: 0;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                <button type="submit" class="btn-logout flex items-center gap-2 px-3 py-1.5 rounded bg-indigo-700 hover:bg-indigo-800 transition-colors text-white text-sm font-medium">
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
                        <a href="AdminDashboard.php"
                            class="menu-item flex items-center gap-3 px-6 py-3 bg-indigo-50 text-indigo-600 font-medium">
                            <i class="fa-solid fa-house"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="AdminPending.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-regular fa-clock"></i>
                            Pending Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminVerified.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-regular fa-user"></i>
                            Verified Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminDisabled.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-user-xmark"></i>
                            Disabled Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminLocked.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-lock"></i>
                            Locked Accounts
                        </a>
                    </li>
<li>
                        <a href="AdminTransactions.php"
                            class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-users"></i>
                            Pending Transactions
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-gray-500 mt-1">Manage users and oversee system activity</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                
                <a href="AdminUsers.php" class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-blue-200 transition-all">
            <div>
        <p class="card-label hover-blue text-sm text-gray-500 font-medium">Total Users</p>
        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $total_users ?></h3>
    </div>
    <div class="card-icon w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xl">
        <i class="fa-solid fa-users"></i>
    </div>
</a>

                <a href="AdminPending.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-yellow-200 transition-all">
                    <div>
                        <p class="card-label hover-yellow text-sm text-gray-500 font-medium">Pending Verification</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $pending_users ?></h3>
                    </div>
                    <div class="card-icon w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </a>

                <a href="AdminVerified.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-green-200 transition-all">
                    <div>
                        <p class="card-label hover-green text-sm text-gray-500 font-medium">Verified Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $verified_users ?></h3>
                    </div>
                    <div class="card-icon w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-regular fa-user"></i>
                    </div>
                </a>

                <a href="AdminDisabled.php"
class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-red-200 transition-all">
                    <div>
                        <p class="card-label hover-red text-sm text-gray-500 font-medium">Disabled Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $disabled_users ?></h3>
                    </div>
                    <div class="card-icon w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </a>

                <a href="AdminLocked.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-orange-200 transition-all">
                    <div>
                        <p class="card-label hover-orange text-sm text-gray-500 font-medium">Locked Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $locked_users ?></h3>
                    </div>
                    <div class="card-icon w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </a>

                <a href="AdminTransactions.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block hover:border-purple-200 transition-all">
                    <div>
                        <p class="card-label hover-purple text-sm text-gray-500 font-medium">Pending Transactions</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1"><?= $pending_tx_count ?></h3>
                    </div>
                    <div class="card-icon w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="finance-card deposits bg-emerald-500 rounded-xl shadow-sm p-6 text-white flex flex-col justify-between">
                    <div class="flex items-center gap-4">
                        <div class="finance-icon w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-arrow-down"></i>
                        </div>
                        <div>
                            <h4 class="font-medium">Total Deposits</h4>
                            <p class="text-sm opacity-80">Completed transactions</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h2 class="text-4xl font-bold"><?= h(format_money($total_deposit)) ?></h2>
</div>
                </div>

                <div class="finance-card withdrawals bg-blue-500 rounded-xl shadow-sm p-6 text-white flex flex-col justify-between">
                    <div class="flex items-center gap-4">
                        <div class="finance-icon w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-arrow-up"></i>
                        </div>
                        <div>
                            <h4 class="font-medium">Total Withdrawals</h4>
                            <p class="text-sm opacity-80">Completed transactions</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h2 class="text-4xl font-bold"><?= h(format_money($total_withdraw)) ?></h2>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>

</html>