<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeePay - Scan QR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <link rel="stylesheet" href="../assets/css/style_scan.css">
</head>
<body>
    <div class="app-container">
        <header class="app-top-bar">
            <a href="Home.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="page-title-group">
                <h1 class="page-title">Scan QR Code</h1>
                <p class="page-subtitle">Align QR code within the frame to pay</p>
            </div>
        </header>

        <div class="scan-area-container">
            <div class="camera-wrapper">
                <div id="reader"></div>
                <div class="scan-overlay">
                    <div class="scanner-laser"></div>
                </div>
            </div>
        </div>

        <div class="instruction-box">
            <i class="fa-solid fa-circle-info"></i>
            <p>Scan a partner's BeePay QR code or billing QR code to transfer money instantly.</p>
        </div>

        <nav class="bottom-nav">
            <a href="Home.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span>Home</span>
            </a>
            <a href="Scan.php" class="nav-item active scan-btn">
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
        function onScanSuccess(decodedText, decodedResult) {
       
            html5QrcodeScanner.clear();

            alert("Scanned Successfully: " + decodedText);
            window.location.href = "Transfer.html";
        }

        function onScanFailure(error) {
        }

      
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 220, height: 220 } 
            },
             false
        );
        

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>