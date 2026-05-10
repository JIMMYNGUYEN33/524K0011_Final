<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../core/WalletDAL.php';
require_once __DIR__ . '/../core/WalletBLL.php';

require_verified_user();

$user = current_user();
$message = '';
$messageType = '';
$purchased_cards = []; // Mảng chứa danh sách thẻ cào vừa mua thành công để hiển thị lên popup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrierMap = [
        'viettel' => 'Viettel',
        'mobifone' => 'Mobifone',
        'vinaphone' => 'Vinaphone',
    ];

    $carrier = $carrierMap[$_POST['carrier'] ?? 'viettel'] ?? 'Viettel';
    $quantity = (int) ($_POST['quantity'] ?? 1);
    
    // Giới hạn số lượng mua từ 1 đến tối đa 5 thẻ mỗi lần
    if ($quantity > 5) {
        $quantity = 5;
    } elseif ($quantity < 1) {
        $quantity = 1;
    }

    $wallet = new WalletBLL(new WalletDAL($pdo), $pdo);
    $result = $wallet->buyCard(
        $user['id'],
        $carrier,
        (int) ($_POST['denomination'] ?? 10000),
        $quantity
    );

    $message = $result['message'];
    $messageType = !empty($result['success']) ? 'success' : 'danger';

    // Nếu giao dịch mua thẻ thành công, sinh ngẫu nhiên mã thẻ 10 chữ số tương ứng số lượng đã chọn
    if (!empty($result['success'])) {
        for ($i = 0; $i < $quantity; $i++) {
            // Tạo mã thẻ cào 10 chữ số ngẫu nhiên
            $card_code = '';
            for ($j = 0; $j < 10; $j++) {
                $card_code .= rand(0, 9);
            }
            // Tạo số serial ngẫu nhiên
            $card_serial = 'SN' . rand(100000, 999999) . rand(10, 99);
            
            $purchased_cards[] = [
                'serial' => $card_serial,
                'code' => $card_code
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Phone Card</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_buycard.css">
    
    <style>
        /* CSS Popup Hiển thị danh sách thẻ cào */
        .card-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .card-popup-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .card-popup-box {
            background: #ffffff;
            border-radius: 24px;
            width: 100%;
            max-width: 420px;
            padding: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .card-popup-overlay.show .card-popup-box {
            transform: scale(1);
        }
        .popup-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .popup-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .popup-subtitle {
            font-size: 13px;
            color: #4b5563;
        }
        .card-item-popup {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            position: relative;
            overflow: hidden;
        }
        /* Tạo vết bấm răng cưa hai đầu vé thẻ cào */
        .card-item-popup::before, .card-item-popup::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-radius: 50%;
            transform: translateY(-50%);
        }
        .card-item-popup::before { left: -6px; border-right: 1px dashed #cbd5e1; }
        .card-item-popup::after { right: -6px; border-left: 1px dashed #cbd5e1; }
        
        .card-field {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #64748b;
        }
        .card-code-val {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .btn-copy-code {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .btn-copy-code:hover {
            background: rgba(37, 99, 235, 0.08);
        }
        .btn-popup-close {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
            transition: background 0.2s ease;
        }
        .btn-popup-close:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="page-title-group">
                <h1 class="page-title">Buy Phone Card</h1>
                <p class="page-subtitle">Purchase mobile phone scratch cards</p>
            </div>
        </header>

        <div class="buycard-card">
            <?php if ($message): ?>
                <div class="alert alert-<?= h($messageType) ?> text-center"><?= h($message) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="selection-section">
                    <label class="section-label">Select Carrier <span class="required">*</span></label>
                    <div class="carriers-grid">
                        
                        <div class="carrier-item active" data-carrier="viettel">
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin: 0 auto 10px auto; border-radius: 50%;">
                                <img src="../assets/viettel.png" alt="Viettel" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <span>Viettel</span>
                        </div>

                        <div class="carrier-item" data-carrier="mobifone">
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin: 0 auto 10px auto; border-radius: 50%;">
                                <img src="../assets/mobifone.png" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <span>Mobifone</span>
                        </div>

                        <div class="carrier-item" data-carrier="vinaphone">
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; margin: 0 auto 10px auto; border-radius: 50%;">
                                <img src="../assets/vinaphone.png" alt="Vinaphone" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <span>Vinaphone</span>
                        </div>

                    </div>
                    <input type="hidden" id="selected-carrier" name="carrier" value="viettel">
                </div>

                <div class="selection-section mt-4">
                    <label class="section-label">Select Denomination <span class="required">*</span></label>
                    <div class="denominations-grid">
                        <div class="denom-item active" data-value="10000">10,000 VND</div>
                        <div class="denom-item" data-value="20000">20,000 VND</div>
                        <div class="denom-item" data-value="50000">50,000 VND</div>
                        <div class="denom-item" data-value="100000">100,000 VND</div>
                    </div>
                    <input type="hidden" id="selected-denomination" name="denomination" value="10000">
                </div>

                <div class="in_gr mt-4">
                    <label>Quantity (Max: 5) <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-arrow-up-9-1"></i>
                        <input required type="number" id="quantity-input" name="quantity" min="1" max="5" value="1" placeholder="1">
                    </div>
                </div>

                <div class="total-payment-box mt-4">
                    <div class="total-label">Total Payment</div>
                    <div class="total-amount" id="total-amount-display">10,000 VND</div>
                </div>
                <button type="submit" class="btn-purchase">Purchase</button>
            </form>
        </div>
    </div>

    <?php if (!empty($purchased_cards)): ?>
        <div class="card-popup-overlay show" id="cardPopup">
            <div class="card-popup-box">
                <div class="popup-header">
                    <div style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 20px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="popup-title">Mua Thẻ Thành Công!</h3>
                    <p class="popup-subtitle">Nhà mạng: <span class="fw-bold"><?= h($carrier) ?></span> - Mệnh giá: <span class="fw-bold"><?= h(format_money($_POST['denomination'])) ?></span></p>
                </div>
                
                <div style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                    <?php foreach ($purchased_cards as $index => $card): ?>
                        <div class="card-item-popup">
                            <div class="card-field mb-1">
                                <span>Thẻ cào #<?= $index + 1 ?></span>
                                <span>Serial: <strong class="text-dark"><?= h($card['serial']) ?></strong></span>
                            </div>
                            <div class="card-field">
                                <span class="card-code-val" id="code-<?= $index ?>"><?= h($card['code']) ?></span>
                                <button class="btn-copy-code" onclick="copyCardCode('<?= h($card['code']) ?>', this)">
                                    <i class="fa-regular fa-copy"></i> Sao chép
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="btn-popup-close" onclick="closeCardPopup()">Đóng</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const carrierItems = document.querySelectorAll('.carrier-item');
        const selectedCarrierInput = document.getElementById('selected-carrier');

        carrierItems.forEach(item => {
            item.addEventListener('click', () => {
                document.querySelector('.carrier-item.active').classList.remove('active');
                item.classList.add('active');
                selectedCarrierInput.value = item.getAttribute('data-carrier');
            });
        });

        const denomItems = document.querySelectorAll('.denom-item');
        const selectedDenomInput = document.getElementById('selected-denomination');
        const quantityInput = document.getElementById('quantity-input');
        const totalAmountDisplay = document.getElementById('total-amount-display');

        function updateTotalPayment() {
            const denomination = parseInt(selectedDenomInput.value) || 0;
            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity < 1) {
                quantity = 1;
                quantityInput.value = 1;
            } else if (quantity > 5) {
                quantity = 5;
                quantityInput.value = 5;
            }

            const total = denomination * quantity;
            totalAmountDisplay.innerText = total.toLocaleString('en-US') + " VND";
        }

        denomItems.forEach(item => {
            item.addEventListener('click', () => {
                document.querySelector('.denom-item.active').classList.remove('active');
                item.classList.add('active');
                selectedDenomInput.value = item.getAttribute('data-value');
                updateTotalPayment();
            });
        });

        quantityInput.addEventListener('input', updateTotalPayment);
        quantityInput.addEventListener('change', updateTotalPayment);

        // --- ĐIỀU KHIỂN ĐÓNG POPUP & SAO CHÉP MÃ NHANH ---
        function closeCardPopup() {
            const popup = document.getElementById('cardPopup');
            if (popup) {
                popup.classList.remove('show');
            }
        }

        function copyCardCode(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fa-solid fa-check"></i> Đã chép';
                button.style.color = '#16a34a';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.color = '#2563eb';
                }, 1500);
            }).catch(err => {
                console.error('Không thể sao chép mã: ', err);
            });
        }
    </script>
</body>
</html>