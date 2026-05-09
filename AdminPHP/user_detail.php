<?php
require_once __DIR__ . '/../helpers/ui.php';

require_admin();

$userId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM Users WHERE id = ? AND role = "user" LIMIT 1');
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    set_flash('error', 'User not found.');
    redirect_to('/admin/users.php');
}

$stmt = $pdo->prepare(
    'SELECT t.*, sender.full_name AS sender_name, receiver.full_name AS receiver_name
    FROM Transactions t
    LEFT JOIN Users sender ON sender.id = t.user_id
    LEFT JOIN Users receiver ON receiver.id = t.receiver_id
    WHERE (t.user_id = ? OR t.receiver_id = ?)
        AND t.created_at >= DATE_FORMAT(CURDATE(), "%Y-%m-01")
    ORDER BY t.created_at DESC'
);
$stmt->execute([$userId, $userId]);
$transactions = $stmt->fetchAll();

function render_user_action_button($userId, $action, $label, $class = '')
{
    ?>
    <form method="post" action="<?= base_url('/admin/user_action.php') ?>" onsubmit="return confirm('Are you sure?');">
        <input type="hidden" name="user_id" value="<?= h($userId) ?>">
        <input type="hidden" name="action" value="<?= h($action) ?>">
        <button class="<?= h($class) ?>" type="submit"><?= h($label) ?></button>
    </form>
    <?php
}

render_header('User Detail');
?>
<section class="card">
    <h1>User detail</h1>
    <p><a class="button secondary" href="<?= base_url('/admin/users.php') ?>">Back to users</a></p>

    <div class="grid">
        <div>
            <h3>Basic information</h3>
            <p>ID: <?= h($targetUser['id']) ?></p>
            <p>Name: <?= h($targetUser['full_name']) ?></p>
            <p>Email: <?= h($targetUser['email']) ?></p>
            <p>Phone: <?= h($targetUser['phone']) ?></p>
            <p>Date of birth: <?= h($targetUser['dob']) ?></p>
            <p>Address: <?= h($targetUser['address']) ?></p>
            <p>Status: <span class="status"><?= h($targetUser['status']) ?></span></p>
            <p>Balance: <?= h(format_money($targetUser['balance'])) ?></p>
            <p>Created at: <?= h($targetUser['created_at']) ?></p>
        </div>
        <div>
            <h3>Login security</h3>
            <p>First login: <?= (int) $targetUser['is_first_login'] === 1 ? 'Yes' : 'No' ?></p>
            <p>Wrong login count: <?= h($targetUser['wrong_login_count']) ?></p>
            <p>Abnormal login count: <?= h($targetUser['abnormal_login_count']) ?></p>
            <p>Temporary locked until: <?= h($targetUser['locked_until']) ?></p>
            <p>Permanently locked: <?= (int) $targetUser['is_permanently_locked'] === 1 ? 'Yes' : 'No' ?></p>
            <p>Permanently locked at: <?= h($targetUser['permanently_locked_at']) ?></p>
        </div>
    </div>
</section>

<section class="card">
    <h2>ID card images</h2>
    <div class="grid">
        <div>
            <h3>Front</h3>
            <?php if ($targetUser['id_front']): ?>
                <img class="id-photo" src="<?= base_url('/' . $targetUser['id_front']) ?>" alt="ID front">
            <?php else: ?>
                <p class="muted">No front image.</p>
            <?php endif; ?>
        </div>
        <div>
            <h3>Back</h3>
            <?php if ($targetUser['id_back']): ?>
                <img class="id-photo" src="<?= base_url('/' . $targetUser['id_back']) ?>" alt="ID back">
            <?php else: ?>
                <p class="muted">No back image.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="card">
    <h2>Admin actions</h2>
    <div class="actions">
        <?php if (in_array($targetUser['status'], ['pending', 'waiting_update'], true)): ?>
            <?php render_user_action_button($targetUser['id'], 'verify', 'Verify'); ?>
            <?php render_user_action_button($targetUser['id'], 'cancel', 'Cancel account', 'danger'); ?>
            <?php render_user_action_button($targetUser['id'], 'request_update', 'Request ID update', 'warning'); ?>
        <?php endif; ?>

        <?php if ((int) $targetUser['is_permanently_locked'] === 1 || $targetUser['permanently_locked_at']): ?>
            <?php render_user_action_button($targetUser['id'], 'unlock', 'Unlock account'); ?>
        <?php endif; ?>

        <?php if (!in_array($targetUser['status'], ['pending', 'waiting_update'], true)
            && (int) $targetUser['is_permanently_locked'] !== 1
            && !$targetUser['permanently_locked_at']): ?>
            <p class="muted">No action is available for this account state.</p>
        <?php endif; ?>
    </div>
</section>

<section class="card">
    <h2>Transactions in current month</h2>
    <?php if (!$transactions): ?>
        <p class="muted">No transactions in current month.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?= h($tx['transaction_code'] ?: $tx['id']) ?></td>
                        <td><?= h($tx['type']) ?></td>
                        <td><?= h(format_money($tx['amount'])) ?></td>
                        <td><?= h(format_money($tx['fee'])) ?></td>
                        <td><span class="status"><?= h($tx['status']) ?></span></td>
                        <td><?= h($tx['sender_name']) ?></td>
                        <td><?= h($tx['receiver_name']) ?></td>
                        <td><?= h($tx['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
