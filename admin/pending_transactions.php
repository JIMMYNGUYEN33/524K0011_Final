<?php
require_once __DIR__ . '/../helpers/ui.php';

require_admin();

$stmt = $pdo->query(
    'SELECT t.*, sender.full_name AS sender_name, sender.phone AS sender_phone,
            receiver.full_name AS receiver_name, receiver.phone AS receiver_phone
    FROM Transactions t
    LEFT JOIN Users sender ON sender.id = t.user_id
    LEFT JOIN Users receiver ON receiver.id = t.receiver_id
    WHERE t.status = "pending"
        AND t.type IN ("withdraw", "transfer")
        AND t.amount > 5000000
    ORDER BY t.created_at DESC'
);
$transactions = $stmt->fetchAll();

render_header('Pending Transactions');
?>
<section class="card">
    <h1>Pending transactions over 5,000,000 VND</h1>
    <p><a class="button secondary" href="<?= base_url('/admin/users.php') ?>">Back to users</a></p>

    <?php if (!$transactions): ?>
        <p class="muted">No pending transactions.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Fee payer</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Note</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?= h($tx['transaction_code'] ?: $tx['id']) ?></td>
                        <td><?= h($tx['type']) ?></td>
                        <td><?= h(format_money($tx['amount'])) ?></td>
                        <td><?= h(format_money($tx['fee'])) ?></td>
                        <td><?= h($tx['fee_payer'] ?: '-') ?></td>
                        <td>
                            <?= h($tx['sender_name']) ?><br>
                            <span class="muted"><?= h($tx['sender_phone']) ?></span>
                        </td>
                        <td>
                            <?= h($tx['receiver_name'] ?: '-') ?><br>
                            <span class="muted"><?= h($tx['receiver_phone'] ?: '') ?></span>
                        </td>
                        <td><?= h($tx['note']) ?></td>
                        <td><?= h($tx['created_at']) ?></td>
                        <td>
                            <div class="actions">
                                <form method="post" action="<?= base_url('/admin/transaction_action.php') ?>" onsubmit="return confirm('Approve this transaction?');">
                                    <input type="hidden" name="transaction_id" value="<?= h($tx['id']) ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit">Approve</button>
                                </form>
                                <form method="post" action="<?= base_url('/admin/transaction_action.php') ?>" onsubmit="return confirm('Reject this transaction?');">
                                    <input type="hidden" name="transaction_id" value="<?= h($tx['id']) ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="danger" type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
