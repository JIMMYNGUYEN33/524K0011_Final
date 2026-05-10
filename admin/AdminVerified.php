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

// 4. Lấy danh sách tài khoản đã xác minh (Trạng thái verified và không phải admin)
try {
    // Điều chỉnh lại tên cột 'status' hoặc 'role' cho khớp với cấu trúc Database của nhóm Như nhé
    $stmt = $pdo->query(
        "SELECT id, full_name, email, phone, balance, status 
         FROM Users 
         WHERE status = 'verified' 
           AND (role IS NULL OR role != 'admin')
         ORDER BY id DESC"
    );
    $verified_users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Đếm số lượng tài khoản đã xác minh
$verified_count = count($verified_users);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified Accounts - Admin Panel</title>
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
                        <a href="AdminVerified.php"
                            class="menu-item flex items-center gap-3 px-6 py-3 bg-indigo-50 text-indigo-600 font-medium">
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
                        <a href="AdminTransactions.php"
                            class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-users"></i> Pending Transactions
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Verified Accounts</h1>
                <p class="text-gray-500 mt-1">Active verified accounts (<?= $verified_count ?>)</p>
            </div>

            <div class="flex flex-col gap-4">

                <?php if (empty($verified_users)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                        <i class="fa-solid fa-user-slash text-4xl mb-3 text-gray-300"></i>
                        <p>No verified accounts found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($verified_users as $user): ?>
                        <a href="AdVerifiedDetail.php?id=<?= h($user['id']) ?>"
                            class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between cursor-pointer hover:shadow-md hover:border-indigo-200 transition-all duration-200 group block">

                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-regular fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        <?= h($user['full_name']) ?>
                                    </h4>
                                    <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                        <span><?= h($user['email']) ?></span>
                                        <span>•</span>
                                        <span><?= h($user['phone']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900 mb-1.5">
                                        <?= h(format_money($user['balance'] ?? 0)) ?> VND
                                    </div>
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-user-check"></i> Verified
                                    </span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-indigo-500 transition-colors"></i>
                            </div>

                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

        </main>
    </div>

</body>

</html>