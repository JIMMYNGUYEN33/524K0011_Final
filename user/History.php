<?php
require_once __DIR__ . '/../helpers/auth.php';

require_login();
ensure_first_password_changed();

$user = current_user();
$stmt = $pdo->prepare(
    'SELECT t.*, sender.full_name AS sender_name, receiver.full_name AS receiver_name
     FROM Transactions t
     LEFT JOIN Users sender ON sender.id = t.user_id
     LEFT JOIN Users receiver ON receiver.id = t.receiver_id
     WHERE t.user_id = ?
        OR (t.receiver_id = ? AND NOT (t.type = "transfer" AND t.status IN ("pending", "cancelled")))
     ORDER BY t.created_at DESC'
);
$stmt->execute([$user['id'], $user['id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Transaction History</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_history.css">
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="page-title-group">
                <h1 class="page-title">Transaction History</h1>
                <p class="page-subtitle">View all your wallet transactions</p>
            </div>
        </header>

        <div class="filter-card">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="search-input" placeholder="Search by transaction ID or note">
            </div>

            <div class="tabs-scroll-container">
                <div class="tabs-wrapper">
                    <button class="tab-btn active" data-filter="all">All</button>
                    <button class="tab-btn" data-filter="deposit">Deposit</button>
                    <button class="tab-btn" data-filter="withdraw">Withdraw</button>
                    <button class="tab-btn" data-filter="transfer">Transfer</button>
                    <button class="tab-btn" data-filter="buy_card">Buy Card</button>
                </div>
            </div>
        </div>

        <div class="transactions-list-container">
            <?php if (!$transactions): ?>
                <div id="history-empty-state" class="empty-state-wrapper text-center" style="margin-top: 80px;">
                    <i class="fa-regular fa-folder-open" style="font-size: 50px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    <p style="color: #94a3b8; font-size: 14px; font-weight: 500;">No transactions yet</p>
                </div>
            <?php else: ?>
                <div class="transactions-list" id="history-list">
                    <?php foreach ($transactions as $tx): ?>
                        <?php
                        $isIncomingTransfer = $tx['type'] === 'transfer' && (int) $tx['receiver_id'] === (int) $user['id'];
                        $isPositive = $tx['type'] === 'deposit' || $isIncomingTransfer;
                        $amountClass = $isPositive ? 'positive' : 'negative';
                        $prefix = $isPositive ? '+' : '-';
                        $label = $tx['type'];
                        if ($isIncomingTransfer) {
                            $label = 'received';
                        }
                        ?>
                        <div class="transaction-item" data-type="<?= h($tx['type']) ?>" data-search="<?= h(strtolower(($tx['transaction_code'] ?? '') . ' ' . ($tx['note'] ?? '') . ' ' . $label)) ?>">
                            <div class="tx-icon-box orange">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div class="tx-details">
                                <span class="tx-name"><?= h(ucwords(str_replace('_', ' ', $label))) ?></span>
                                <span class="tx-time"><?= h($tx['created_at']) ?></span>
                                <?php if (!empty($tx['note'])): ?>
                                    <span class="tx-time"><?= h($tx['note']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="tx-amount-status">
                                <span class="tx-amount <?= h($amountClass) ?>"><?= h($prefix . format_money($tx['amount'])) ?></span>
                                <span class="tx-status success"><i class="fa-solid fa-circle-check"></i> <?= h(ucfirst($tx['status'])) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-right tx-arrow"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById("search-input");
        const tabBtns = document.querySelectorAll('.tab-btn');
        const rows = Array.from(document.querySelectorAll('.transaction-item'));

        function filterHistory() {
            const activeFilter = document.querySelector('.tab-btn.active').getAttribute('data-filter');
            const keyword = searchInput.value.toLowerCase();

            rows.forEach(row => {
                const typeMatches = activeFilter === 'all' || row.dataset.type === activeFilter;
                const searchMatches = !keyword || row.dataset.search.includes(keyword);
                row.style.display = typeMatches && searchMatches ? '' : 'none';
            });
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('.tab-btn.active').classList.remove('active');
                btn.classList.add('active');
                filterHistory();
            });
        });

        searchInput.addEventListener('input', filterHistory);
    </script>
</body>
</html>
