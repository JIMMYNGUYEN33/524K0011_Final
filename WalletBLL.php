<?php
class WalletBLL {
    private $dal;

    public function __construct($dal) {
        $this->dal = $dal;
    }

    // --- 1. NẠP TIỀN (DEPOSIT) ---
    public function deposit($userId, $cardNumber, $exp, $cvv, $amount) {
        $card = $this->dal->getCardByNumber($cardNumber);
        if (!$card) return ['success' => false, 'message' => 'Thẻ không được hỗ trợ [cite: 81]'];
        if ($card['expiration_date'] !== $exp || $card['cvv'] !== $cvv) {
            return ['success' => false, 'message' => 'Sai ngày hết hạn hoặc mã CVV [cite: 82]'];
        }
        if ($cardNumber === '333333') return ['success' => false, 'message' => 'Card is out of money [cite: 79]'];
        if ($cardNumber === '222222' && $amount > 1000000) {
            return ['success' => false, 'message' => 'Thẻ này chỉ được nạp tối đa 1 triệu/lần [cite: 79]'];
        }

        $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'deposit', 'amount' => $amount, 'fee' => 0, 'status' => 'completed', 'note' => 'Nạp tiền từ thẻ ' . $cardNumber
        ]);
        $this->dal->updateBalance($userId, $amount);
        return ['success' => true, 'message' => 'Nạp tiền thành công!'];
    }

    // --- 2. RÚT TIỀN (WITHDRAW) ---
    public function withdraw($userId, $cardNumber, $exp, $cvv, $amount, $note) {
        if ($cardNumber !== '111111' || $exp !== '10/10/2022' || $cvv !== '411') {
            return ['success' => false, 'message' => 'Thông tin thẻ rút không hợp lệ [cite: 93]'];
        }
        if ($amount % 50000 !== 0) return ['success' => false, 'message' => 'Tiền rút phải là bội số của 50,000 VND [cite: 96]'];
        if ($this->dal->countWithdrawToday($userId) >= 2) return ['success' => false, 'message' => 'Tối đa 2 lần rút/ngày [cite: 95]'];

        $user = $this->dal->getUserById($userId);
        $fee = $amount * 0.05; // Phí 5% [cite: 97]
        if ($user['balance'] < ($amount + $fee)) return ['success' => false, 'message' => 'Số dư không đủ chi trả cả phí [cite: 101]'];

        $status = ($amount > 5000000) ? 'pending' : 'completed'; // [cite: 98, 99]
        $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'withdraw', 'amount' => $amount, 'fee' => $fee, 'status' => $status, 'note' => $note
        ]);

        if ($status === 'completed') {
            $this->dal->updateBalance($userId, -($amount + $fee));
        }
        return ['success' => true, 'message' => ($status === 'pending') ? 'Đang chờ Admin duyệt [cite: 98]' : 'Rút tiền thành công!'];
    }

    // --- 3. CHUYỂN TIỀN (TRANSFER) ---
    public function transfer($senderId, $receiverPhone, $amount, $note, $isSenderPayFee) {
        $receiver = $this->dal->getUserByPhone($receiverPhone);
        if (!$receiver) return ['success' => false, 'message' => 'Không tìm thấy người nhận'];
        if ($senderId == $receiver['id']) return ['success' => false, 'message' => 'Không thể tự chuyển cho chính mình'];

        $sender = $this->dal->getUserById($senderId);
        $fee = $amount * 0.05; // [cite: 107]
        $totalDeduct = $isSenderPayFee ? ($amount + $fee) : $amount;

        if ($sender['balance'] < $totalDeduct) return ['success' => false, 'message' => 'Số dư không đủ'];

        $status = ($amount > 5000000) ? 'pending' : 'completed'; // [cite: 104]
        $transId = $this->dal->createTransaction([
            'user_id' => $senderId, 'receiver_id' => $receiver['id'], 'type' => 'transfer',
            'amount' => $amount, 'fee' => $fee, 'status' => $status, 'note' => $note
        ]);

        if ($status === 'completed') {
            $this->dal->updateBalance($senderId, -$totalDeduct);
            $receiveAmount = $isSenderPayFee ? $amount : ($amount - $fee);
            $this->dal->updateBalance($receiver['id'], $receiveAmount);
        }
        return ['success' => true, 'message' => 'Giao dịch thành công!', 'otp_required' => true]; // [cite: 108]
    }

    // --- 4. MUA THẺ CÀO (BUY CARD) ---
    public function buyCard($userId, $carrier, $denomination, $quantity) {
        $validDenoms = [10000, 20000, 50000, 100000]; // [cite: 114]
        if (!in_array($denomination, $validDenoms)) return ['success' => false, 'message' => 'Mệnh giá không hợp lệ'];
        if ($quantity < 1 || $quantity > 5) return ['success' => false, 'message' => 'Số lượng từ 1-5 thẻ [cite: 115]'];

        $total = $denomination * $quantity;
        $user = $this->dal->getUserById($userId);
        if ($user['balance'] < $total) return ['success' => false, 'message' => 'Số dư không đủ [cite: 116]'];

        $transId = $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'buy_card', 'amount' => $total, 'fee' => 0, 'status' => 'completed', 'note' => "Mua $quantity thẻ $carrier"
        ]);

        $prefix = ['Viettel' => '11111', 'Mobifone' => '22222', 'Vinaphone' => '33333']; // [cite: 123]
        for ($i = 0; $i < $quantity; $i++) {
            $code = $prefix[$carrier] . rand(10000, 99999); // Sinh mã 10 số [cite: 118]
            $this->dal->savePhoneCard($transId, $carrier, $code, $denomination);
        }

        $this->dal->updateBalance($userId, -$total);
        return ['success' => true, 'message' => 'Mua thẻ thành công! Xem mã trong lịch sử [cite: 119]'];
    }
}

// --- 5. ADMIN: DUYỆT GIAO DỊCH (WITHDRAW/TRANSFER > 5TR) ---
public function approveTransaction($transactionId, $isApprove) {
    $stmt = $this->pdo->prepare("SELECT * FROM Transactions WHERE id = ? AND status = 'pending'");
    $stmt->execute([$transactionId]);
    $trans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trans) return ['success' => false, 'message' => 'Giao dịch không tồn tại hoặc đã được xử lý'];

    if ($isApprove) {
        // Nếu đồng ý: Chuyển status thành completed và thực hiện trừ/cộng tiền
        $this->pdo->prepare("UPDATE Transactions SET status = 'completed' WHERE id = ?")->execute([$transactionId]);
        
        $fee = $trans['fee'];
        $amount = $trans['amount'];

        if ($trans['type'] === 'withdraw') {
            $this->dal->updateBalance($trans['user_id'], -($amount + $fee));
        } else if ($trans['type'] === 'transfer') {
            // Logic chuyển tiền: Giả định mặc định người gửi chịu phí (có thể tùy biến thêm)
            $this->dal->updateBalance($trans['user_id'], -($amount + $fee));
            $this->dal->updateBalance($trans['receiver_id'], $amount);
        }
        return ['success' => true, 'message' => 'Đã phê duyệt giao dịch thành công'];
    } else {
        // Nếu từ chối: Chỉ cần chuyển status thành cancelled
        $this->pdo->prepare("UPDATE Transactions SET status = 'cancelled' WHERE id = ?")->execute([$transactionId]);
        return ['success' => true, 'message' => ' đã từ chối giao dịch'];
    }
}

// --- 6. ADMIN: QUẢN LÝ TRẠNG THÁI TÀI KHOẢN ---
public function updateAccountStatus($userId, $newStatus) {
    // $newStatus có thể là 'verified', 'disabled', 'waiting_update'
    $stmt = $this->pdo->prepare("UPDATE Users SET status = ? WHERE id = ?");
    return $stmt->execute([$newStatus, $userId]);
}

// --- 7. ADMIN: MỞ KHÓA TÀI KHOẢN (DO NHẬP SAI PASS) ---
public function unlockAccount($userId) {
    $stmt = $this->pdo->prepare("UPDATE Users SET 
        wrong_login_count = 0, 
        locked_until = NULL, 
        is_permanently_locked = FALSE 
        WHERE id = ?");
    return $stmt->execute([$userId]);
}
?>