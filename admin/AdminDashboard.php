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

    <header
        class="h-16 bg-indigo-600 flex items-center justify-between px-6 shadow-sm z-10 shrink-0">
        <div class="flex items-center gap-2 text-white">
            <i class="fa-solid fa-shield-halved text-xl"></i>
            <span class="text-xl font-bold">BeePay</span>
        </div>
        <div class="flex items-center gap-6 text-white">
            <div class="text-right">
                <div class="text-sm opacity-90">Administrator</div>
                <div class="text-sm font-semibold">System Administrator</div>
            </div>
            <button class="btn-logout flex items-center gap-2 px-3 py-1.5 rounded">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">

        <aside class="w-64 bg-white shadow-md flex flex-col overflow-y-auto z-0">
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
                        <a href="AdminPending.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600">
                            <i class="fa-regular fa-clock"></i>
                            Pending Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminVerified.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600">
                            <i class="fa-regular fa-user"></i>
                            Verified Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminDisabled.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600">
                            <i class="fa-solid fa-user-xmark"></i>
                            Disabled Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminLocked.php" class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600">
                            <i class="fa-solid fa-lock"></i>
                            Locked Accounts
                        </a>
                    </li>
                    <li>
                        <a href="AdminTransactions.php"
                            class="menu-item flex items-center gap-3 px-6 py-3 text-gray-600">
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
                <div
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center">
                    <div>
                        <p class="card-label hover-blue text-sm text-gray-500 font-medium">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">2</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <a href="AdminPending.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block">
                    <div>
                        <p class="card-label hover-yellow text-sm text-gray-500 font-medium">Pending Verification</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">1</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </a>

                <a href="AdminVerified.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block">
                    <div>
                        <p class="card-label hover-green text-sm text-gray-500 font-medium">Verified Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">1</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-regular fa-user"></i>
                    </div>
                </a>

                <a href="AdminDisabled.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block">
                    <div>
                        <p class="card-label hover-red text-sm text-gray-500 font-medium">Disabled Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">0</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </a>

                <a href="AdminLocked.php"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block">
                    <div>
                        <p class="card-label hover-orange text-sm text-gray-500 font-medium">Locked Accounts</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">0</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </a>
                <a href="AdminTransactions.html"
                    class="card bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-between items-center block">
                    <div>
                        <p class="card-label hover-purple text-sm text-gray-500 font-medium">Pending Transactions</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">0</h3>
                    </div>
                    <div
                        class="card-icon w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center text-white text-xl">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div
                    class="finance-card deposits bg-green-500 rounded-xl shadow-sm p-6 text-white flex flex-col justify-between">
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
                        <h2 class="text-4xl font-bold">0 VND</h2>
                    </div>
                </div>

                <div
                    class="finance-card withdrawals bg-blue-500 rounded-xl shadow-sm p-6 text-white flex flex-col justify-between">
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
                        <h2 class="text-4xl font-bold">0 VND</h2>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>

</html>