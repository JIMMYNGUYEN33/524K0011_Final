<?php
class WalletBLL {
    private $dal;
    private $pdo; // Thêm thuộc tính pdo nếu bạn muốn BLL thực thi truy vấn trực tiếp, hoặc dùng DAL.

    public function __construct($dal, $pdo = null) {
        $this->dal = $dal;
        $this->pdo = $pdo; // Gán PDO để phục vụ các phương thức Admin bên dưới
    }

    // --- 1. NẠP TIỀN (DEPOSIT) ---
    public function deposit($userId, $cardNumber, $exp, $cvv, $amount) {
        $card = $this->dal->getCardByNumber($cardNumber);
        if (!$card) return ['success' => false, 'message' => 'Cards are not supported.'];
        if ($card['expiration_date'] !== $exp || $card['cvv'] !== $cvv) {
            return ['success' => false, 'message' => 'Incorrect expiration date or CVV code'];
        }
        if ($cardNumber === '333333') return ['success' => false, 'message' => 'Card is out of money'];
        if ($cardNumber === '222222' && $amount > 1000000) {
            return ['success' => false, 'message' => 'Maximum deposit amount is 1.000.000 VND per transaction.'];
        }

        $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'deposit', 'amount' => $amount, 'fee' => 0, 'status' => 'completed', 'note' => 'Nạp tiền từ thẻ ' . $cardNumber
        ]);
        $this->dal->updateBalance($userId, $amount);
        return ['success' => true, 'message' => 'Deposit successful'];
    }

    // --- 2. RÚT TIỀN (WITHDRAW) ---
    public function withdraw($userId, $cardNumber, $exp, $cvv, $amount, $note) {
        if ($cardNumber !== '111111' || $exp !== '10/10/2022' || $cvv !== '411') {
            return ['success' => false, 'message' => 'Invalid card information'];
        }
        if ($amount % 50000 !== 0) return ['success' => false, 'message' => 'Withdrawal amount must be a multiple of 50,000 VND'];
        if ($this->dal->countWithdrawToday($userId) >= 2) return ['success' => false, 'message' => 'Maximum 2 withdrawals allowed per day'];

        $user = $this->dal->getUserById($userId);
        $fee = $amount * 0.05; // Phí 5%
        if ($user['balance'] < ($amount + $fee)) return ['success' => false, 'message' => 'Insufficient balance to cover transaction and fee'];

        $status = ($amount > 5000000) ? 'pending' : 'completed';
        $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'withdraw', 'amount' => $amount, 'fee' => $fee, 'status' => $status, 'note' => $note
        ]);

        if ($status === 'completed') {
            $this->dal->updateBalance($userId, -($amount + $fee));
        }
        return ['success' => true, 'message' => ($status === 'pending') ? 'Pending administrator approval' : 'Withdrawal successful!'];
    }

    // --- 3. CHUYỂN TIỀN (TRANSFER) ---
    public function transfer($senderId, $receiverPhone, $amount, $note, $isSenderPayFee) {
        $receiver = $this->dal->getUserByPhone($receiverPhone);
        if (!$receiver) return ['success' => false, 'message' => 'Receiver not found'];
        if ($senderId == $receiver['id']) return ['success' => false, 'message' => 'Cannot transfer money to yourself'];

        $sender = $this->dal->getUserById($senderId);
        $fee = $amount * 0.05;
        $totalDeduct = $isSenderPayFee ? ($amount + $fee) : $amount;

        if ($sender['balance'] < $totalDeduct) return ['success' => false, 'message' => 'Insufficient balance'];

        $status = ($amount > 5000000) ? 'pending' : 'completed';
        $transId = $this->dal->createTransaction([
            'user_id' => $senderId, 'receiver_id' => $receiver['id'], 'type' => 'transfer',
            'amount' => $amount, 'fee' => $fee, 'status' => $status, 'note' => $note
        ]);

        if ($status === 'completed') {
            $this->dal->updateBalance($senderId, -$totalDeduct);
            $receiveAmount = $isSenderPayFee ? $amount : ($amount - $fee);
            $this->dal->updateBalance($receiver['id'], $receiveAmount);
        }
        return ['success' => true, 'message' => 'Transaction successful!', 'otp_required' => true];
    }

    // --- 4. MUA THẺ CÀO (BUY CARD) ---
    public function buyCard($userId, $carrier, $denomination, $quantity) {
        $validDenoms = [10000, 20000, 50000, 100000];
        if (!in_array($denomination, $validDenoms)) return ['success' => false, 'message' => 'Invalid card denomination'];
        if ($quantity < 1 || $quantity > 5) return ['success' => false, 'message' => 'Quantity must be between 1 and 5 cards'];

        $total = $denomination * $quantity;
        $user = $this->dal->getUserById($userId);
        if ($user['balance'] < $total) return ['success' => false, 'message' => 'Insufficient balance'];

        $transId = $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'buy_card', 'amount' => $total, 'fee' => 0, 'status' => 'completed', 'note' => "Mua $quantity thẻ $carrier"
        ]);

        $prefix = ['Viettel' => '11111', 'Mobifone' => '22222', 'Vinaphone' => '33333'];
        for ($i = 0; $i < $quantity; $i++) {
            $code = $prefix[$carrier] . rand(10000, 99999);
            $this->dal->savePhoneCard($transId, $carrier, $code, $denomination);
        }

        $this->dal->updateBalance($userId, -$total);
        return ['success' => true, 'message' => 'Card purchased successfully! View your code in History'];
    }

    // --- 5. ADMIN: DUYỆT GIAO DỊCH (WITHDRAW/TRANSFER > 5TR) ---
    public function approveTransaction($transactionId, $isApprove) {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Database connection (PDO) is missing in BLL'];
        }

        $stmt = $this->pdo->prepare("SELECT * FROM Transactions WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transactionId]);
        $trans = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trans) return ['success' => false, 'message' => 'Transaction does not exist or has already been processed'];

        if ($isApprove) {
            $this->pdo->prepare("UPDATE Transactions SET status = 'completed' WHERE id = ?")->execute([$transactionId]);
            
            $fee = $trans['fee'];
            $amount = $trans['amount'];

            if ($trans['type'] === 'withdraw') {
                $this->dal->updateBalance($trans['user_id'], -($amount + $fee));
            } else if ($trans['type'] === 'transfer') {
                $this->dal->updateBalance($trans['user_id'], -($amount + $fee));
                $this->dal->updateBalance($trans['receiver_id'], $amount);
            }
            return ['success' => true, 'message' => 'Transaction successfully approved'];
        } else {
            $this->pdo->prepare("UPDATE Transactions SET status = 'cancelled' WHERE id = ?")->execute([$transactionId]);
            return ['success' => true, 'message' => 'Transaction rejected'];
        }
    }

    // --- 6. ADMIN: QUẢN LÝ TRẠNG THÁI TÀI KHOẢN ---
    public function updateAccountStatus($userId, $newStatus) {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("UPDATE Users SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $userId]);
    }

    // --- 7. ADMIN: MỞ KHÓA TÀI KHOẢN (DO NHẬP SAI PASS) ---
    public function unlockAccount($userId) {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("UPDATE Users SET 
            wrong_login_count = 0, 
            locked_until = NULL, 
            is_permanently_locked = FALSE 
            WHERE id = ?");
        return $stmt->execute([$userId]);
    }
} 
?>