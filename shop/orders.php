<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$userId = (int) currentUser()['id'];
$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$details = [];
if ($orders) {
    $detailStmt = $conn->prepare('SELECT product_name, quantity, price FROM order_details WHERE order_id = ? ORDER BY id');
    foreach ($orders as $order) {
        $orderId = (int) $order['id'];
        $detailStmt->bind_param('i', $orderId);
        $detailStmt->execute();
        $details[$orderId] = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$statusClasses = ['pending' => 'warning', 'processing' => 'primary', 'completed' => 'success', 'cancelled' => 'secondary'];
$pageTitle = 'My Orders';
require __DIR__ . '/../includes/header.php';
?>
<div class="mb-4"><p class="text-primary fw-semibold mb-1">ACCOUNT</p><h1 class="h2 fw-bold">My orders</h1></div>
<?php if (!$orders): ?><div class="card border-0 shadow-sm"><div class="card-body text-center p-5"><div class="empty-icon mx-auto mb-3"><i class="bi bi-receipt"></i></div><h2 class="h4">No orders yet</h2><p class="text-secondary">Your completed checkouts will appear here.</p><a href="<?= e(baseUrl('shop/products.php')) ?>" class="btn btn-primary">Shop Now</a></div></div><?php endif; ?>
<div class="accordion shadow-sm" id="ordersAccordion">
<?php foreach ($orders as $index => $order): ?><div class="accordion-item border-0 mb-3 rounded overflow-hidden">
    <h2 class="accordion-header"><button class="accordion-button <?= $index ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#order<?= (int) $order['id'] ?>"><span class="fw-bold me-3">Order #<?= (int) $order['id'] ?></span><span class="badge text-bg-<?= e($statusClasses[$order['status']] ?? 'secondary') ?> me-3"><?= e(ucfirst($order['status'])) ?></span><span class="text-secondary ms-auto me-3">$<?= number_format((float) $order['total_price'], 2) ?></span></button></h2>
    <div id="order<?= (int) $order['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#ordersAccordion"><div class="accordion-body">
        <p class="text-secondary small">Placed <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
        <?php foreach ($details[(int) $order['id']] as $item): ?><div class="d-flex justify-content-between border-top py-2"><span><?= e($item['product_name']) ?> &times; <?= (int) $item['quantity'] ?></span><span>$<?= number_format((float) $item['price'] * (int) $item['quantity'], 2) ?></span></div><?php endforeach; ?>
    </div></div>
</div><?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
