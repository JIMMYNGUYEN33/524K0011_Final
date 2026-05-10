<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../config/db_config.php';
global $pdo;


require_admin();


$search = trim($_GET['search'] ?? '');
$filter_role = trim($_GET['role'] ?? 'all'); 

try {
    
    $query = "SELECT * FROM Users WHERE 1=1";
    $params = [];

    
    if ($filter_role === 'admin') {
        $query .= " AND role = 'admin'";
    } elseif ($filter_role === 'user') {
        $query .= " AND role = 'user'";
    }

    
    if ($search !== '') {
        $query .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$total_count = count($users);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
                        <a href="AdminTransactions.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-users"></i> Pending Transactions
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto flex flex-col">
            
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <a href="AdminDashboard.php" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-semibold transition-colors mb-2 text-sm text-decoration-none">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 mt-1">User Management</h1>
                    <p class="text-gray-500 mt-1">Total system accounts: <span class="font-bold text-indigo-600"><?= $total_count ?></span></p>
                </div>
                
                <form method="GET" action="" class="flex flex-wrap items-center gap-3 m-0">
                    <select name="role" onchange="this.form.submit()" class="border border-gray-200 rounded-xl px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?= $filter_role === 'all' ? 'selected' : '' ?>>All Roles</option>
                        <option value="user" <?= $filter_role === 'user' ? 'selected' : '' ?>>Users</option>
                        <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admins</option>
                    </select>

                    <div class="relative">
                        <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search name, phone, email..." 
                               class="border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                    </div>

                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
                        Search
                    </button>
                    <?php if ($search !== '' || $filter_role !== 'all'): ?>
                        <a href="AdminUsers.php" class="text-sm text-gray-500 hover:text-indigo-600 font-medium ml-1">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider font-semibold">
                                <th class="py-4 px-6">User Details</th>
                                <th class="py-4 px-6">Phone & Email</th>
                                <th class="py-4 px-6 text-center">Role</th>
                                <th class="py-4 px-6 text-right">Balance</th>
                                <th class="py-4 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        <i class="fa-solid fa-user-slash text-4xl mb-3 text-gray-200"></i>
                                        <p>No users found matching the criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white <?= $u['role'] === 'admin' ? 'bg-indigo-500' : 'bg-emerald-500' ?>">
                                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-900"><?= h($u['full_name']) ?></h4>
                                                    <span class="text-[11px] text-gray-400">ID: <?= h($u['id']) ?> • Joined <?= date('d/m/Y', strtotime($u['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="py-4 px-6">
                                            <p class="font-medium text-gray-800"><?= h($u['phone']) ?></p>
                                            <p class="text-xs text-gray-400"><?= h($u['email']) ?></p>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Admin</span>
                                            <?php else: ?>
                                                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">User</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-4 px-6 text-right font-bold text-gray-900">
                                            <?= h(format_money($u['balance'])) ?>
                                        </td>

                                        <td class="py-4 px-6 text-center">
                                            <?php 
                                                $status = $u['status'] ?? 'pending';
                                                if ($u['is_permanently_locked']) {
                                                    $status_label = 'Locked';
                                                    $status_class = 'bg-rose-100 text-rose-700';
                                                } elseif ($status === 'verified') {
                                                    $status_label = 'Verified';
                                                    $status_class = 'bg-emerald-100 text-emerald-700';
                                                } elseif ($status === 'disabled') {
                                                    $status_label = 'Disabled';
                                                    $status_class = 'bg-gray-100 text-gray-600';
                                                } else {
                                                    $status_label = 'Pending';
                                                    $status_class = 'bg-amber-100 text-amber-700';
                                                }
                                            ?>
                                            <span class="inline-flex items-center gap-1 <?= $status_class ?> text-[11px] font-semibold px-2.5 py-1 rounded-full">
                                                <?= $status_label ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>