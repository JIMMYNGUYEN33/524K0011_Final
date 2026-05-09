<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Deposit Money</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style_deposit.css">
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="page-title-group">
                <h1 class="page-title">Deposit Money</h1>
                <p class="page-subtitle">Add money to your wallet using credit card</p>
            </div>
        </header>

        <div class="deposit-card">
            <form action="#" method="POST">
                
                <div class="in_gr">
                    <label>Card Number <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-regular fa-credit-card"></i>
                        <input required type="text" maxlength="6" placeholder="Enter 6-digit card number">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="in_gr mb-0">
                            <label>Expiration Date <span class="required">*</span></label>
                            <div class="in_wrapper">
                                <i class="fa-regular fa-calendar"></i>
                                <input required type="text" placeholder="DD/MM/YYYY">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="in_gr mb-0">
                            <label>CVV <span class="required">*</span></label>
                            <div class="in_wrapper">
                                <i class="fa-solid fa-key"></i>
                                <input required type="password" maxlength="3" placeholder="3 digits">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="in_gr">
                    <label>Amount (VND) <span class="required">*</span></label>
                    <div class="in_wrapper">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <input required type="number" min="1000" placeholder="Enter amount">
                    </div>
                </div>

                <button type="submit" class="btn-deposit">Deposit</button>
            </form>
        </div>
    </div>
</body>
</html>