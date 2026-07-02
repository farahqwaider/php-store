<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$userId = (int) currentUser()['id'];
$stmt = $conn->prepare(
    'SELECT cart.product_id, cart.quantity AS cart_quantity, products.name, products.price, products.quantity AS stock
     FROM cart JOIN products ON products.id = cart.product_id WHERE cart.user_id = ?'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$items) {
    flash('warning', 'Your cart is empty.');
    redirect(baseUrl('shop/cart.php'));
}

$total = array_reduce($items, fn (float $sum, array $item): float => $sum + ((float) $item['price'] * (int) $item['cart_quantity']), 0.0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    try {
        $conn->begin_transaction();
        $lockedItems = [];
        $lock = $conn->prepare('SELECT id, name, price, quantity FROM products WHERE id = ? FOR UPDATE');

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $lock->bind_param('i', $productId);
            $lock->execute();
            $product = $lock->get_result()->fetch_assoc();

            if (!$product || (int) $product['quantity'] < (int) $item['cart_quantity']) {
                throw new RuntimeException(($product['name'] ?? 'A product') . ' no longer has enough stock.');
            }
            $product['cart_quantity'] = (int) $item['cart_quantity'];
            $lockedItems[] = $product;
        }

        $total = array_reduce($lockedItems, fn (float $sum, array $item): float => $sum + ((float) $item['price'] * $item['cart_quantity']), 0.0);
        $status = 'pending';
        $stmt = $conn->prepare('INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, ?)');
        $stmt->bind_param('ids', $userId, $total, $status);
        $stmt->execute();
        $orderId = $conn->insert_id;

        $detail = $conn->prepare('INSERT INTO order_details (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)');
        $reduceStock = $conn->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');

        foreach ($lockedItems as $item) {
            $productId = (int) $item['id'];
            $quantity = (int) $item['cart_quantity'];
            $price = (float) $item['price'];
            $detail->bind_param('iisid', $orderId, $productId, $item['name'], $quantity, $price);
            $detail->execute();
            $reduceStock->bind_param('ii', $quantity, $productId);
            $reduceStock->execute();
        }

        $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $conn->commit();

        flash('success', 'Order #' . $orderId . ' was placed successfully.');
        redirect(baseUrl('shop/orders.php'));
    } catch (Throwable $exception) {
        $conn->rollback();
        $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'The order could not be placed. Please try again.';
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/../includes/header.php';
?>
<div class="form-wrapper mx-auto">
    <div class="mb-4"><p class="text-primary fw-semibold mb-1">CHECKOUT</p><h1 class="h2 fw-bold">Confirm your order</h1></div>
    <?php if ($errors): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <?php foreach ($items as $item): ?><div class="d-flex justify-content-between py-3 border-bottom"><div><strong><?= e($item['name']) ?></strong><div class="text-secondary small">Quantity: <?= (int) $item['cart_quantity'] ?></div></div><span>$<?= number_format((float) $item['price'] * (int) $item['cart_quantity'], 2) ?></span></div><?php endforeach; ?>
        <div class="d-flex justify-content-between py-4 fs-5"><strong>Total</strong><strong>$<?= number_format($total, 2) ?></strong></div>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="btn btn-primary btn-lg w-100"><i class="bi bi-check-circle me-2"></i>Place Order</button></form>
    </div></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
