<?php
require_once __DIR__ . '/../helpers/auth.php';

require_login();
ensure_first_password_changed();

$user = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style_home.css?v=6">
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
                <a href="Deposit.php" class="service-item">
                    <div class="icon-box green"><i class="fa-solid fa-download"></i></div>
                    <p>Deposit</p>
                </a>

                <a href="Withdraw.php" class="service-item">
                    <div class="icon-box blue"><i class="fa-solid fa-upload"></i></div>
                    <p>Withdraw</p>
                </a>

                <a href="Transfer.php" class="service-item">
                    <div class="icon-box purple"><i class="fa-solid fa-exchange-alt"></i></div>
                    <p>Transfer</p>
                </a>

                <a href="Buycard.php" class="service-item">
                    <div class="icon-box orange"><i class="fa-regular fa-credit-card"></i></div>
                    <p>Buy Card</p>
                </a>

                <a href="History.php" class="service-item">
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

            <div id="home-empty-state" class="empty-state">
                <i class="fa-solid fa-wallet"></i>
                <p>No transactions yet</p>
            </div>
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
    <script>
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
