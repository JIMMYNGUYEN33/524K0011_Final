<?php
require_once __DIR__ . '/../helpers/ui.php';

require_admin();

function get_users_by_sql($sql)
{
    global $pdo;

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function render_users_table($title, $users)
{
    ?>
    <section class="card">
        <h2><?= h($title) ?></h2>

        <?php if (!$users): ?>
            <p class="muted">No accounts found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= h($user['id']) ?></td>
                            <td><?= h($user['full_name']) ?></td>
                            <td><?= h($user['email']) ?></td>
                            <td><?= h($user['phone']) ?></td>
                            <td><span class="status"><?= h($user['status']) ?></span></td>
                            <td><?= h($user['created_at']) ?></td>
                            <td>
                                <a class="button" href="<?= base_url('/admin/user_detail.php?id=' . $user['id']) ?>">Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
    <?php
}

$pendingUsers = get_users_by_sql(
    'SELECT * FROM Users
    WHERE role = "user" AND status IN ("pending", "waiting_update")
    ORDER BY created_at DESC'
);

$verifiedUsers = get_users_by_sql(
    'SELECT * FROM Users
    WHERE role = "user" AND status = "verified"
    ORDER BY created_at DESC'
);

$disabledUsers = get_users_by_sql(
    'SELECT * FROM Users
    WHERE role = "user" AND status = "disabled"
    ORDER BY created_at DESC'
);

$lockedUsers = get_users_by_sql(
    'SELECT * FROM Users
    WHERE role = "user" AND (is_permanently_locked = TRUE OR permanently_locked_at IS NOT NULL)
    ORDER BY permanently_locked_at DESC'
);

render_header('Admin Users');
?>
<section class="card">
    <h1>Admin user management</h1>
    <div class="actions">
        <a class="button" href="<?= base_url('/admin/pending_transactions.php') ?>">Pending transactions</a>
        <a class="button secondary" href="<?= base_url('/admin/locked_users.php') ?>">Locked users only</a>
    </div>
</section>

<?php
render_users_table('Waiting for activation', $pendingUsers);
render_users_table('Verified accounts', $verifiedUsers);
render_users_table('Disabled accounts', $disabledUsers);
render_users_table('Permanently locked accounts', $lockedUsers);
render_footer();
?>
