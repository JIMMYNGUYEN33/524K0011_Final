<?php
class WalletDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserById($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByPhone($phone) {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCardByNumber($cardNumber) {
        $stmt = $this->pdo->prepare("SELECT * FROM Cards WHERE card_number = ?");
        $stmt->execute([$cardNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countWithdrawToday($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Transactions 
                                     WHERE user_id = ? AND type = 'withdraw' 
                                     AND DATE(created_at) = CURDATE()");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function createTransaction($data) {
        $sql = "INSERT INTO Transactions (transaction_code, user_id, receiver_id, type, amount, fee, status, note) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'GD' . time() . rand(100, 999), // [cite: 127]
            $data['user_id'],
            $data['receiver_id'] ?? null,
            $data['type'],
            $data['amount'],
            $data['fee'],
            $data['status'],
            $data['note']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateBalance($userId, $amount) {
        $stmt = $this->pdo->prepare("UPDATE Users SET balance = balance + ? WHERE id = ?");
        return $stmt->execute([$amount, $userId]);
    }

    public function savePhoneCard($transactionId, $carrier, $code, $amount) {
        $stmt = $this->pdo->prepare("INSERT INTO PhoneCards (transaction_id, carrier, card_code, amount) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$transactionId, $carrier, $code, $amount]);
    }
}

// Lấy danh sách tài khoản chờ kích hoạt (mới nhất lên đầu)
public function getPendingAccounts() {
    $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE status = 'pending' ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy danh sách tài khoản bị khóa vô thời hạn do sai pass
public function getLockedAccounts() {
    $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE is_permanently_locked = TRUE ORDER BY locked_until DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy danh sách các giao dịch rút/chuyển trên 5 triệu đang chờ duyệt
public function getPendingTransactions() {
    $stmt = $this->pdo->prepare("SELECT t.*, u.full_name as sender_name 
                                 FROM Transactions t 
                                 JOIN Users u ON t.user_id = u.id 
                                 WHERE t.status = 'pending' AND t.amount > 5000000 
                                 ORDER BY t.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>