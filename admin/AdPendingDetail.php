<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../config/db_config.php';
global $pdo;


require_admin();

$error = '';
$success = '';


$userId = $_GET['id'] ?? null;

if (!$userId) {
    header("Location: AdminPending.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'verify') {
            
            $stmt = $pdo->prepare("UPDATE Users SET status = 'verified' WHERE id = ?");
            $stmt->execute([$userId]);
            $success = "Account has been successfully verified!";
        } elseif ($action === 'disable') {
            
            $stmt = $pdo->prepare("UPDATE Users SET status = 'disabled' WHERE id = ?");
            $stmt->execute([$userId]);
            $success = "Account has been disabled.";
        } elseif ($action === 'request_update') {
            // Cập nhật yêu cầu người dùng cập nhật lại chứng minh thư/thông tin
            $stmt = $pdo->prepare("UPDATE Users SET status = 'update_requested' WHERE id = ?");
            $stmt->execute([$userId]);
            $success = "Verification update request sent to the user.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}


try {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Nếu không tìm thấy user, quay ngược về trang danh sách
    if (!$user) {
        header("Location: AdminPending.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - Admin Panel</title>
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
                        <a href="AdminPending.php" class="menu-item flex items-center gap-3 px-6 py-3 bg-indigo-50 text-indigo-600 font-medium">
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
                        <a href="pending_transactions.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="fa-solid fa-users"></i> Pending Transactions
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            
            <div class="mb-6 flex items-center justify-between">
                <a href="AdminPending.php" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
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
                
                <div class="bg-indigo-600 p-8 flex items-center gap-6 text-white">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center text-3xl shrink-0">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold"><?= h($user['full_name']) ?></h2>
                        <p class="text-indigo-200 text-sm mt-1">ID: <?= h($user['id']) ?></p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 pb-8 border-b border-gray-100">
                        
                        <div class="flex items-start gap-4">
                            <i class="fa-regular fa-envelope text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Email</p>
                                <p class="text-gray-800 font-medium mt-0.5"><?= h($user['email'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-phone text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Phone</p>
                                <p class="text-gray-800 font-medium mt-0.5"><?= h($user['phone'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-regular fa-calendar text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Date of Birth</p>
                                <p class="text-gray-800 font-medium mt-0.5">
                                    <?= !empty($user['birth_date']) ? date('Y-m-d', strtotime($user['birth_date'])) : '-' ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-location-dot text-gray-400 text-lg mt-1 w-5 text-center"></i>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Address</p>
                                <p class="text-gray-800 font-medium mt-0.5"><?= h($user['address'] ?? '-') ?></p>
                            </div>
                        </div>

                    </div>

                    <div class="grid grid-cols-2 gap-y-6 gap-x-12 pt-8">
                        <div>
                            <p class="text-sm text-gray-400">Balance</p>
                            <p class="text-gray-800 font-bold mt-1"><?= h(format_money($user['balance'] ?? 0)) ?> VND</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Status</p>
                            <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-full mt-1">
                                <i class="fa-regular fa-clock"></i> <?= ucfirst(h($user['status'])) ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Created</p>
                            <p class="text-gray-800 font-bold mt-1">
                                <?= !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Role</p>
                            <p class="text-gray-800 font-bold mt-1"><?= ucfirst(h($user['role'] ?? 'user')) ?></p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Account Verification</h3>
                
                <div class="flex flex-wrap gap-4">
                    <form method="POST" action="" class="flex-1 min-w-[180px] m-0" onsubmit="return confirm('Do you want to verify this account?');">
                        <input type="hidden" name="action" value="verify">
                        <button type="submit" class="w-100 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors duration-200">
                            <i class="fa-regular fa-circle-check"></i> Verify Account
                        </button>
                    </form>
                    
                    <form method="POST" action="" class="flex-1 min-w-[180px] m-0" onsubmit="return confirm('Do you want to disable this account?');">
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="w-100 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors duration-200">
                            <i class="fa-regular fa-circle-xmark"></i> Disable Account
                        </button>
                    </form>
                    
                    <form method="POST" action="" class="flex-1 min-w-[180px] m-0" onsubmit="return confirm('Do you want to request ID update from this user?');">
                        <input type="hidden" name="action" value="request_update">
                        <button type="submit" class="w-100 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors duration-200">
                            <i class="fa-regular fa-circle-question"></i> Request ID Update
                        </button>
                    </form>
                </div>
            </div>

        </main>
    </div>

</body>
</html>