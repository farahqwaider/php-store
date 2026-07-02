<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$currentUserId = (int) currentUser()['id'];

// معالجة العمليات (تغيير الدور أو حذف المستخدم)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $userId = (int) $_POST['user_id'];

    // لا يمكن للأدمن تعديل أو حذف نفسه
    if ($userId === $currentUserId) {
        flash('warning', 'You cannot modify or delete your own administrator account here.');
        redirect(baseUrl('admin/users.php'));
    }

    if ($action === 'role') {
        $role = $_POST['role'];
        if ($role === 'admin' || $role === 'user') {
            $stmt = $conn->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->bind_param('si', $role, $userId);
            $stmt->execute();
            flash('success', 'User role updated.');
        }
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        flash('success', 'User deleted.');
    }

    redirect(baseUrl('admin/users.php'));
}

// جلب جميع المستخدمين مع عدد طلباتهم
$users = $conn->query(
    "SELECT users.id, users.name, users.email, users.role, users.created_at, COUNT(orders.id) AS order_count
     FROM users LEFT JOIN orders ON orders.user_id = users.id
     GROUP BY users.id ORDER BY users.id DESC"
);

$pageTitle = 'Manage Users';
$adminLayout = true;
require __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <p class="text-primary fw-semibold mb-1">ACCESS</p>
    <h1 class="h2 fw-bold">Users</h1>
    <p class="text-secondary">Manage roles and customer accounts.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Role</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">
                                <?= e($user['name']) ?>
                                <?php if ((int) $user['id'] === $currentUserId): ?>
                                    (you)
                                <?php endif; ?>
                            </td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= (int) $user['order_count'] ?></td>
                            <td><?= e(date('M j, Y', strtotime($user['created_at']))) ?></td>
                            <td>
                                <?php if ((int) $user['id'] === $currentUserId): ?>
                                    <span class="badge text-bg-primary">Admin</span>
                                <?php else: ?>
                                    <form method="post">
                                        <input type="hidden" name="action" value="role">
                                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                        <select class="form-select form-select-sm" name="role" onchange="this.form.submit()">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ((int) $user['id'] !== $currentUserId): ?>
                                    <form method="post" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
