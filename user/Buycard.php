<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../core/WalletDAL.php';
require_once __DIR__ . '/../core/WalletBLL.php';

require_verified_user();

$user = current_user();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrierMap = [
        'viettel' => 'Viettel',
        'mobifone' => 'Mobifone',
        'vinaphone' => 'Vinaphone',
    ];

    $carrier = $carrierMap[$_POST['carrier'] ?? 'viettel'] ?? 'Viettel';
    $wallet = new WalletBLL(new WalletDAL($pdo), $pdo);
    $result = $wallet->buyCard(
        $user['id'],
        $carrier,
        (int) ($_POST['denomination'] ?? 10000),
        (int) ($_POST['quantity'] ?? 1)
    );

    $message = $result['message'];
    $messageType = !empty($result['success']) ? 'success' : 'danger';
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
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center;">
                                <img src="../assets/viettel.png" alt="Viettel" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <span>Viettel</span>
                        </div>

                        <div class="carrier-item" data-carrier="mobifone">
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center;">
                                <img src="../assets/mobifone.png" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <span>Mobifone</span>
                        </div>

                        <div class="carrier-item" data-carrier="vinaphone">
                            <div class="carrier-icon" style="background: #fff; border: 1px solid #f3f4f6; padding: 4px; display: flex; align-items: center; justify-content: center;">
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
    </script>
</body>
</html>