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
                </div>
            </div>
        </div>

        <div class="transactions-list-container">
            <div id="history-empty-state" class="empty-state-wrapper text-center" style="margin-top: 80px;">
                <i class="fa-regular fa-folder-open" style="font-size: 50px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                <p style="color: #94a3b8; font-size: 14px; font-weight: 500;">No transactions yet</p>
            </div>

            <div class="transactions-list" id="history-list" style="display: none;">
                </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const emptyState = document.getElementById("history-empty-state");
            const historyList = document.getElementById("history-list");
            const searchInput = document.getElementById("search-input");
            const tabBtns = document.querySelectorAll('.tab-btn');

            // 1. Khởi tạo dữ liệu mẫu trong localStorage để test thử giao diện (Như có thể xóa đoạn này nếu muốn trống hoàn toàn)
            // Nhóm của Như sau này khi nạp/rút/chuyển thành công chỉ cần dùng lệnh: 
            // localStorage.setItem("beepay_transactions", JSON.stringify(mảng_giao_dịch_mới));
            if (!localStorage.getItem("beepay_transactions")) {
                const sampleTransactions = []; // Để trống để kiểm tra trạng thái ban đầu giống Như muốn
                localStorage.setItem("beepay_transactions", JSON.stringify(sampleTransactions));
            }

            // Lấy dữ liệu từ localStorage
            let transactions = JSON.parse(localStorage.getItem("beepay_transactions")) || [];

            // 2. Hàm vẽ giao diện lịch sử dựa trên bộ lọc và tìm kiếm
            function renderHistory(filterType = "all", keyword = "") {
                // Sắp xếp các giao dịch mới nhất lên trên đầu
                let filtered = transactions.slice().reverse();

                // Lọc theo Tab (all, deposit, withdraw, transfer)
                if (filterType !== "all") {
                    filtered = filtered.filter(tx => tx.type === filterType);
                }

                // Lọc theo từ khóa tìm kiếm (Tên giao dịch hoặc ngày giờ)
                if (keyword !== "") {
                    filtered = filtered.filter(tx => 
                        tx.name.toLowerCase().includes(keyword) || 
                        tx.time.toLowerCase().includes(keyword)
                    );
                }

                // Kiểm tra hiển thị trạng thái Trống hay Danh sách
                if (filtered.length === 0) {
                    emptyState.style.display = "block";
                    historyList.style.display = "none";
                } else {
                    emptyState.style.display = "none";
                    historyList.style.display = "block";

                    // Vẽ từng dòng giao dịch ra màn hình
                    historyList.innerHTML = filtered.map(tx => {
                        const isPositive = tx.type === 'deposit' || tx.type === 'received';
                        const amountClass = isPositive ? 'positive' : 'negative';
                        const prefix = isPositive ? '+' : '-';
                        const iconColorClass = tx.iconColor || 'orange'; // Mặc định là màu cam

                        return `
                            <div class="transaction-item" data-type="${tx.type}">
                                <div class="tx-icon-box ${iconColorClass}">
                                    <i class="fa-solid ${tx.icon || 'fa-money-bill-wave'}"></i>
                                </div>
                                <div class="tx-details">
                                    <span class="tx-name">${tx.name}</span>
                                    <span class="tx-time">${tx.time}</span>
                                </div>
                                <div class="tx-amount-status">
                                    <span class="tx-amount ${amountClass}">${prefix}${tx.amount.toLocaleString('en-US')} VND</span>
                                    <span class="tx-status success"><i class="fa-solid fa-circle-check"></i> Completed</span>
                                </div>
                                <i class="fa-solid fa-chevron-right tx-arrow"></i>
                            </div>
                        `;
                    }).join('');
                }
            }

            renderHistory();

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelector('.tab-btn.active').classList.remove('active');
                    btn.classList.add('active');
                    
                    const filter = btn.getAttribute('data-filter');
                    renderHistory(filter, searchInput.value.toLowerCase());
                });
            });

            searchInput.addEventListener('input', (e) => {
                const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-filter');
                renderHistory(activeTab, e.target.value.toLowerCase());
            });
        });
    </script>
</body>
</html>