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
        if (!$card) return ['success' => false, 'message' => 'Thẻ không được hỗ trợ'];
        if ($card['expiration_date'] !== $exp || $card['cvv'] !== $cvv) {
            return ['success' => false, 'message' => 'Sai ngày hết hạn hoặc mã CVV'];
        }
        if ($cardNumber === '333333') return ['success' => false, 'message' => 'Card is out of money'];
        if ($cardNumber === '222222' && $amount > 1000000) {
            return ['success' => false, 'message' => 'Thẻ này chỉ được nạp tối đa 1 triệu/lần'];
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
            return ['success' => false, 'message' => 'Thông tin thẻ rút không hợp lệ'];
        }
        if ($amount % 50000 !== 0) return ['success' => false, 'message' => 'Tiền rút phải là bội số của 50,000 VND'];
        if ($this->dal->countWithdrawToday($userId) >= 2) return ['success' => false, 'message' => 'Tối đa 2 lần rút/ngày'];

        $user = $this->dal->getUserById($userId);
        $fee = $amount * 0.05; // Phí 5%
        if ($user['balance'] < ($amount + $fee)) return ['success' => false, 'message' => 'Số dư không đủ chi trả cả phí'];

        $status = ($amount > 5000000) ? 'pending' : 'completed';
        $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'withdraw', 'amount' => $amount, 'fee' => $fee, 'status' => $status, 'note' => $note
        ]);

        if ($status === 'completed') {
            $this->dal->updateBalance($userId, -($amount + $fee));
        }
        return ['success' => true, 'message' => ($status === 'pending') ? 'Đang chờ Admin duyệt' : 'Rút tiền thành công!'];
    }

    // --- 3. CHUYỂN TIỀN (TRANSFER) ---
    public function transfer($senderId, $receiverPhone, $amount, $note, $isSenderPayFee) {
        $receiver = $this->dal->getUserByPhone($receiverPhone);
        if (!$receiver) return ['success' => false, 'message' => 'Không tìm thấy người nhận'];
        if ($senderId == $receiver['id']) return ['success' => false, 'message' => 'Không thể tự chuyển cho chính mình'];

        $sender = $this->dal->getUserById($senderId);
        $fee = $amount * 0.05;
        $totalDeduct = $isSenderPayFee ? ($amount + $fee) : $amount;

        if ($sender['balance'] < $totalDeduct) return ['success' => false, 'message' => 'Số dư không đủ'];

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
        return ['success' => true, 'message' => 'Giao dịch thành công!', 'otp_required' => true];
    }

    // --- 4. MUA THẺ CÀO (BUY CARD) ---
    public function buyCard($userId, $carrier, $denomination, $quantity) {
        $validDenoms = [10000, 20000, 50000, 100000];
        if (!in_array($denomination, $validDenoms)) return ['success' => false, 'message' => 'Mệnh giá không hợp lệ'];
        if ($quantity < 1 || $quantity > 5) return ['success' => false, 'message' => 'Số lượng từ 1-5 thẻ'];

        $total = $denomination * $quantity;
        $user = $this->dal->getUserById($userId);
        if ($user['balance'] < $total) return ['success' => false, 'message' => 'Số dư không đủ'];

        $transId = $this->dal->createTransaction([
            'user_id' => $userId, 'type' => 'buy_card', 'amount' => $total, 'fee' => 0, 'status' => 'completed', 'note' => "Mua $quantity thẻ $carrier"
        ]);

        $prefix = ['Viettel' => '11111', 'Mobifone' => '22222', 'Vinaphone' => '33333'];
        for ($i = 0; $i < $quantity; $i++) {
            $code = $prefix[$carrier] . rand(10000, 99999);
            $this->dal->savePhoneCard($transId, $carrier, $code, $denomination);
        }

        $this->dal->updateBalance($userId, -$total);
        return ['success' => true, 'message' => 'Mua thẻ thành công! Xem mã trong lịch sử'];
    }

    // --- 5. ADMIN: DUYỆT GIAO DỊCH (WITHDRAW/TRANSFER > 5TR) ---
    public function approveTransaction($transactionId, $isApprove) {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Database connection (PDO) is missing in BLL'];
        }

        $stmt = $this->pdo->prepare("SELECT * FROM Transactions WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transactionId]);
        $trans = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trans) return ['success' => false, 'message' => 'Giao dịch không tồn tại hoặc đã được xử lý'];

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
            return ['success' => true, 'message' => 'Đã phê duyệt giao dịch thành công'];
        } else {
            $this->pdo->prepare("UPDATE Transactions SET status = 'cancelled' WHERE id = ?")->execute([$transactionId]);
            return ['success' => true, 'message' => 'Đã từ chối giao dịch'];
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