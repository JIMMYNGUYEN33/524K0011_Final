<?php
require_once __DIR__ . '/../helpers/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/admin/pending_transactions.php');
}

$admin = current_user();
$transactionId = (int) ($_POST['transaction_id'] ?? 0);
$action = $_POST['action'] ?? '';

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM Transactions WHERE id = ? FOR UPDATE');
    $stmt->execute([$transactionId]);
    $tx = $stmt->fetch();

    if (!$tx) {
        throw new RuntimeException('Transaction not found.');
    }

    if ($tx['status'] !== 'pending') {
        throw new RuntimeException('This transaction is not pending anymore.');
    }

    if ($action === 'reject') {
        $stmt = $pdo->prepare(
            'UPDATE Transactions
            SET status = "cancelled",
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ?'
        );
        $stmt->execute([$admin['id'], $transactionId]);
        $pdo->commit();

        set_flash('success', 'Transaction rejected.');
        redirect_to('/admin/pending_transactions.php');
    }

    if ($action !== 'approve') {
        throw new RuntimeException('Invalid action.');
    }

    if ($tx['type'] === 'withdraw') {
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? FOR UPDATE');
        $stmt->execute([$tx['user_id']]);
        $sender = $stmt->fetch();

        if (!$sender) {
            throw new RuntimeException('Sender not found.');
        }

        $totalDebit = (float) $tx['amount'] + (float) $tx['fee'];

        if ((float) $sender['balance'] < $totalDebit) {
            throw new RuntimeException('Sender balance is not enough.');
        }

        $stmt = $pdo->prepare('UPDATE Users SET balance = balance - ? WHERE id = ?');
        $stmt->execute([$totalDebit, $sender['id']]);
    } elseif ($tx['type'] === 'transfer') {
        if (!$tx['receiver_id']) {
            throw new RuntimeException('Receiver is missing.');
        }

        $stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? FOR UPDATE');
        $stmt->execute([$tx['user_id']]);
        $sender = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? FOR UPDATE');
        $stmt->execute([$tx['receiver_id']]);
        $receiver = $stmt->fetch();

        if (!$sender || !$receiver) {
            throw new RuntimeException('Sender or receiver not found.');
        }

        $amount = (float) $tx['amount'];
        $fee = (float) $tx['fee'];
        $feePayer = $tx['fee_payer'] ?: 'sender';

        if ($feePayer === 'receiver') {
            $senderDebit = $amount;
            $receiverCredit = $amount - $fee;

            if ($receiverCredit < 0) {
                throw new RuntimeException('Receiver credit cannot be negative.');
            }
        } else {
            $senderDebit = $amount + $fee;
            $receiverCredit = $amount;
        }

        if ((float) $sender['balance'] < $senderDebit) {
            throw new RuntimeException('Sender balance is not enough.');
        }

        $stmt = $pdo->prepare('UPDATE Users SET balance = balance - ? WHERE id = ?');
        $stmt->execute([$senderDebit, $sender['id']]);

        $stmt = $pdo->prepare('UPDATE Users SET balance = balance + ? WHERE id = ?');
        $stmt->execute([$receiverCredit, $receiver['id']]);
    } else {
        throw new RuntimeException('Only withdraw and transfer can be approved here.');
    }

    $stmt = $pdo->prepare(
        'UPDATE Transactions
        SET status = "completed",
            approved_by = ?,
            approved_at = NOW()
        WHERE id = ?'
    );
    $stmt->execute([$admin['id'], $transactionId]);

    $pdo->commit();
    set_flash('success', 'Transaction approved.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    set_flash('error', $e->getMessage());
}

redirect_to('/admin/pending_transactions.php');
