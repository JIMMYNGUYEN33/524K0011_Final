<?php

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/db_config.php'; 
global $pdo;


require_login();
ensure_first_password_changed();

$user = current_user();
$flash = get_flash();


$userId = $user['id'] ?? null;
$recent_transactions = [];

if ($userId) {
    try {
        
        $stmt_tx = $pdo->prepare("
            SELECT t.*, 
                   sender.full_name AS sender_name,
                   receiver.full_name AS receiver_name
            FROM Transactions t
            LEFT JOIN Users sender ON t.user_id = sender.id
            LEFT JOIN Users receiver ON t.receiver_id = receiver.id
            WHERE t.user_id = ? OR t.receiver_id = ?
            ORDER BY t.created_at DESC 
            LIMIT 2
        ");
        $stmt_tx->execute([$userId, $userId]);
        $recent_transactions = $stmt_tx->fetchAll();
    } catch (PDOException $e) {
        
        error_log("Error fetching recent transactions: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_home.css?v=6">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bee-Home</title>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="user-info">
                <p>Welcome Back,</p>
                <h2><?= h($user['full_name'] ?: $user['email']) ?></h2>
            </div>
            <button class="btn-notification"><i class="fa-regular fa-bell"></i></button>
        </header>

        <div class="balance-card">
            <div class="card-top">
                <div class="wallet-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="balance-info">
                    <p>Wallet Status</p>
                    <span><?= h(ucfirst($user['status'])) ?></span>
                </div>
                <button class="btn-eye" id="toggle-balance" type="button" aria-label="Hide balance" aria-pressed="false">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            <h1 class="amount" id="wallet-balance" data-balance="<?= h(format_money($user['balance'])) ?>">
                <?= h(format_money($user['balance'])) ?>
            </h1>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> text-center" style="font-size: 13px; margin: 14px 0;">
                <?= h($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($user['status'] !== 'verified'): ?>
            <div class="verification-alert">
                <div class="alert-icon">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="alert-content">
                    <p class="alert-title">Account Verification</p>
                    <p class="alert-desc">Your account is waiting for admin verification. Wallet features may be limited.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="services-section">
            <h3 class="section-title">Services</h3>
            <div class="services-grid">
                <a href="Deposit.php" onclick="checkVerification(event, 'Deposit.php')" class="service-item">
                    <div class="icon-box green"><i class="fa-solid fa-download"></i></div>
                    <p>Deposit</p>
                </a>

                <a href="Withdraw.php" onclick="checkVerification(event, 'Withdraw.php')" class="service-item">
                    <div class="icon-box blue"><i class="fa-solid fa-upload"></i></div>
                    <p>Withdraw</p>
                </a>

                <a href="Transfer.php" onclick="checkVerification(event, 'Transfer.php')" class="service-item">
                    <div class="icon-box purple"><i class="fa-solid fa-exchange-alt"></i></div>
                    <p>Transfer</p>
                </a>

                <a href="Buycard.php" onclick="checkVerification(event, 'Buycard.php')" class="service-item">
                    <div class="icon-box orange"><i class="fa-regular fa-credit-card"></i></div>
                    <p>Buy Card</p>
                </a>

                <a href="History.php" onclick="checkVerification(event, 'History.php')" class="service-item">
                    <div class="icon-box indigo"><i class="fa-solid fa-history"></i></div>
                    <p>History</p>
                </a>
            </div>
            <div class="services-scrollbar" aria-hidden="true">
                <div class="services-scrollbar-thumb"></div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <h3 class="section-title">Recent Transactions</h3>
                <a href="History.php" style="font-size: 11px; color: #ffea00; text-decoration: none; font-weight: 600; margin-top: 5px;">See All</a>
            </div>

            <?php if (empty($recent_transactions)): ?>
                <div id="home-empty-state" class="empty-state">
                    <i class="fa-solid fa-wallet"></i>
                    <p>No transactions yet</p>
                </div>
            <?php else: ?>
                <div class="transactions-list-wrapper" style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px; margin-bottom: 20px;">
                    <?php foreach ($recent_transactions as $tx): ?>
                        <?php
                            $is_sender = ($tx['user_id'] == $userId);
                            $type = $tx['type'];
                            
                            if ($type === 'deposit') {
                                $icon = 'fa-arrow-down';
                                $icon_color = '#10b981'; 
                                $icon_bg = 'rgba(16, 185, 129, 0.1)';
                                $title = 'Nạp tiền vào tài khoản';
                                $amount_prefix = '+';
                                $amount_color = '#10b981';
                            } elseif ($type === 'withdraw') {
                                $icon = 'fa-arrow-up';
                                $icon_color = '#ef4444'; 
                                $icon_bg = 'rgba(239, 68, 68, 0.1)';
                                $title = 'Rút tiền tài khoản';
                                $amount_prefix = '-';
                                $amount_color = '#ef4444';
                            } else { 
                                if ($is_sender) {
                                    $icon = 'fa-paper-plane';
                                    $icon_color = '#6366f1'; 
                                    $icon_bg = 'rgba(99, 102, 241, 0.1)';
                                    $title = 'Chuyển đến ' . h($tx['receiver_name'] ?? 'Người dùng');
                                    $amount_prefix = '-';
                                    $amount_color = '#6366f1';
                                } else {
                                    $icon = 'fa-wallet';
                                    $icon_color = '#10b981'; 
                                    $icon_bg = 'rgba(16, 185, 129, 0.1)';
                                    $title = 'Nhận từ ' . h($tx['sender_name'] ?? 'Người dùng');
                                    $amount_prefix = '+';
                                    $amount_color = '#10b981';
                                }
                            }
                        ?>
                        <div class="transaction-item-box" style="background: #ffffff; padding: 14px 16px; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #f3f4f6; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $icon_bg ?>; color: <?= $icon_color ?>; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1f2937;"><?= h($title) ?></h4>
                                    <p style="margin: 3px 0 0 0; font-size: 10px; color: #9ca3af; display: flex; align-items: center; gap: 6px;">
                                        <?= !empty($tx['created_at']) ? date('d/m/Y - H:i', strtotime($tx['created_at'])) : '-' ?>
                                        <?php if ($tx['status'] === 'pending'): ?>
                                            <span style="background: #fffbeb; color: #d97706; padding: 1px 5px; border-radius: 4px; font-weight: 600; font-size: 9px;">Chờ duyệt</span>
                                        <?php elseif ($tx['status'] === 'cancelled'): ?>
                                            <span style="background: #fef2f2; color: #dc2626; padding: 1px 5px; border-radius: 4px; font-weight: 600; font-size: 9px;">Bị từ chối</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 14px; font-weight: 800; color: <?= $amount_color ?>;">
                                    <?= $amount_prefix ?><?= h(format_money($tx['amount'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <nav class="bottom-nav">
            <a href="Home.php" class="nav-item active">
                <i class="fa-solid fa-house"></i>
                <span>BeePay</span>
            </a>
            <a href="Scan.php" class="nav-item scan-btn">
                <div class="scan-circle">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <span>Scan</span>
            </a>
            <a href="Profile.php" class="nav-item">
                <i class="fa-regular fa-user"></i>
                <span>Profile</span>
            </a>
        </nav>
    </div>

    <div id="verification-modal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black/40 hidden">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl border border-amber-100 transform scale-95 transition-transform">
            <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900">Feature Restricted</h3>
            <p class="text-gray-500 text-xs mt-2 leading-relaxed">
                This feature is only available for fully verified accounts. Please wait for administrator verification.
            </p>
            <button onclick="closeModal()" class="w-full mt-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                Got it, thanks!
            </button>
        </div>
    </div>

    <script>
        // Lấy trạng thái của tài khoản từ DB qua PHP
        const userStatus = "<?= h($user['status'] ?? 'pending') ?>";

        // Hàm kiểm tra verify khi bấm vào bất kỳ tính năng dịch vụ nào
        function checkVerification(event, targetUrl) {
            if (userStatus === 'pending') {
                event.preventDefault(); // Chặn chuyển trang
                const modal = document.getElementById('verification-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                // Nếu đã được duyệt thì cho phép chuyển hướng bình thường
                window.location.href = targetUrl;
            }
        }

        // Đóng Popup
        function closeModal() {
            const modal = document.getElementById('verification-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        // --- CÁC ĐOẠN JS GỐC CỦA BÀ GIỮ NGUYÊN 100% ---
        const servicesGrid = document.querySelector('.services-grid');
        const servicesScrollbar = document.querySelector('.services-scrollbar');
        const servicesThumb = document.querySelector('.services-scrollbar-thumb');

        function updateServicesScrollbar() {
            const maxScroll = servicesGrid.scrollWidth - servicesGrid.clientWidth;
            const maxThumbMove = servicesScrollbar.clientWidth - servicesThumb.offsetWidth;
            const thumbX = maxScroll > 0 ? (servicesGrid.scrollLeft / maxScroll) * maxThumbMove : 0;

            servicesThumb.style.transform = `translateX(${thumbX}px)`;
        }

        function moveServicesScroll(clientX) {
            const maxScroll = servicesGrid.scrollWidth - servicesGrid.clientWidth;
            const maxThumbMove = servicesScrollbar.clientWidth - servicesThumb.offsetWidth;

            if (maxScroll <= 0 || maxThumbMove <= 0) {
                return;
            }

            const rect = servicesScrollbar.getBoundingClientRect();
            const rawX = clientX - rect.left - servicesThumb.offsetWidth / 2;
            const thumbX = Math.max(0, Math.min(rawX, maxThumbMove));

            servicesGrid.scrollLeft = (thumbX / maxThumbMove) * maxScroll;
        }

        servicesGrid.addEventListener('scroll', updateServicesScrollbar, { passive: true });
        servicesScrollbar.addEventListener('pointerdown', (event) => {
            moveServicesScroll(event.clientX);
            servicesScrollbar.setPointerCapture(event.pointerId);
        });
        servicesScrollbar.addEventListener('pointermove', (event) => {
            if (event.buttons !== 1) {
                return;
            }

            moveServicesScroll(event.clientX);
        });
        window.addEventListener('resize', updateServicesScrollbar);
        updateServicesScrollbar();

        const balanceText = document.querySelector('#wallet-balance');
        const toggleBalance = document.querySelector('#toggle-balance');
        const toggleBalanceIcon = toggleBalance.querySelector('i');

        toggleBalance.addEventListener('click', () => {
            const isHidden = toggleBalance.getAttribute('aria-pressed') === 'true';

            if (isHidden) {
                balanceText.textContent = balanceText.dataset.balance;
                toggleBalanceIcon.className = 'fa-regular fa-eye';
                toggleBalance.setAttribute('aria-label', 'Hide balance');
                toggleBalance.setAttribute('aria-pressed', 'false');
                return;
            }

            balanceText.textContent = '•••••••• VND';
            toggleBalanceIcon.className = 'fa-regular fa-eye-slash';
            toggleBalance.setAttribute('aria-label', 'Show balance');
            toggleBalance.setAttribute('aria-pressed', 'true');
        });
    </script>
</body>
</html>