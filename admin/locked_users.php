<?php
require_once __DIR__ . '/../helpers/ui.php';

require_admin();

$stmt = $pdo->query(
    'SELECT *
    FROM Users
    WHERE role = "user" AND (is_permanently_locked = TRUE OR permanently_locked_at IS NOT NULL)
    ORDER BY permanently_locked_at DESC'
);
$users = $stmt->fetchAll();

render_header('Locked Users');
?>
<section class="card">
    <h1>Permanently locked users</h1>
    <p><a class="button secondary" href="<?= base_url('/admin/users.php') ?>">Back to all users</a></p>

    <?php if (!$users): ?>
        <p class="muted">No locked accounts.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Locked at</th>
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
                        <td><?= h($user['permanently_locked_at']) ?></td>
                        <td>
                            <a class="button" href="<?= base_url('/admin/user_detail.php?id=' . $user['id']) ?>">Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
