<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();

$statuses = ['pending', 'processing', 'completed', 'cancelled'];

// معالجة تحديث الحالة أو حذف الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $orderId = (int) $_POST['order_id'];

    if ($action === 'status' && in_array($_POST['status'], $statuses)) {
        $status = $_POST['status'];
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $orderId);
        $stmt->execute();
        flash('success', 'Order status updated.');
    }

    if ($action === 'delete' && $orderId > 0) {
        $stmt = $conn->prepare('DELETE FROM orders WHERE id = ?');
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        flash('success', 'Order deleted.');
    }

    redirect(baseUrl('admin/orders.php'));
}

// جلب جميع الطلبات مع بيانات المستخدم
$orders = $conn->query(
    "SELECT orders.*, users.name AS user_name, users.email,
            COUNT(order_details.id) AS line_count
     FROM orders JOIN users ON users.id = orders.user_id
     LEFT JOIN order_details ON order_details.order_id = orders.id
     GROUP BY orders.id, users.name, users.email ORDER BY orders.id DESC"
);

$pageTitle = 'Manage Orders';
$adminLayout = true;
require __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <p class="text-primary fw-semibold mb-1">SALES</p>
    <h1 class="h2 fw-bold">Orders</h1>
    <p class="text-secondary">Review orders and update their fulfillment status.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">No orders have been placed.</td>
                        </tr>
                    <?php endif; ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?= (int) $order['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($order['user_name']) ?></div>
                                <small class="text-secondary"><?= e($order['email']) ?></small>
                            </td>
                            <td><?= (int) $order['line_count'] ?></td>
                            <td class="fw-semibold">$<?= number_format((float) $order['total_price'], 2) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="status">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                <?= e(ucfirst($status)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                            <td class="text-end pe-4">
                                <form method="post" onsubmit="return confirm('Delete this order permanently?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
