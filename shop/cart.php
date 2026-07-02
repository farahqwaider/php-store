<?php

require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();

$userId = (int) currentUser()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    // إضافة منتج إلى السلة
    if ($action === 'add' && $productId > 0) {
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        // التحقق من وجود المنتج ومن الكمية المتاحة
        $stmt = $conn->prepare('SELECT name, quantity FROM products WHERE id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product || (int) $product['quantity'] < 1) {
            flash('warning', 'This product is not available.');
        } else {
            // التأكد من عدم تجاوز الكمية المتاحة
            if ($quantity > (int) $product['quantity']) {
                $quantity = (int) $product['quantity'];
            }

            // التحقق هل المنتج موجود بالسلة أم لا
            $stmt = $conn->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->bind_param('ii', $userId, $productId);
            $stmt->execute();
            $existingItem = $stmt->get_result()->fetch_assoc();

            if ($existingItem) {
                // تحديث الكمية إذا كان المنتج موجود
                $newQuantity = (int) $existingItem['quantity'] + $quantity;
                if ($newQuantity > (int) $product['quantity']) {
                    $newQuantity = (int) $product['quantity'];
                }
                $stmt = $conn->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
                $stmt->bind_param('ii', $newQuantity, $existingItem['id']);
                $stmt->execute();
            } else {
                // إضافة المنتج إلى السلة
                $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
                $stmt->bind_param('iii', $userId, $productId, $quantity);
                $stmt->execute();
            }
            flash('success', $product['name'] . ' was added to your cart.');
        }
        redirect(baseUrl('shop/cart.php'));
    }

    // تحديث كمية منتج في السلة
    if ($action === 'update' && $productId > 0) {
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($quantity <= 0) {
            // إذا الكمية صفر أو أقل، نحذف المنتج من السلة
            $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->bind_param('ii', $userId, $productId);
            $stmt->execute();
            flash('success', 'Product removed from your cart.');
        } else {
            // التحقق من الكمية المتاحة
            $stmt = $conn->prepare('SELECT quantity FROM products WHERE id = ?');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();

            if ($product && $quantity > (int) $product['quantity']) {
                $quantity = (int) $product['quantity'];
            }

            $stmt = $conn->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $stmt->bind_param('iii', $quantity, $userId, $productId);
            $stmt->execute();
            flash('success', 'Cart updated.');
        }
        redirect(baseUrl('shop/cart.php'));
    }

    // حذف منتج من السلة
    if ($action === 'remove' && $productId > 0) {
        $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        flash('success', 'Product removed from your cart.');
        redirect(baseUrl('shop/cart.php'));
    }
}

// جلب محتويات السلة
$stmt = $conn->prepare(
    'SELECT cart.product_id, cart.quantity AS cart_quantity, products.name, products.price,
            products.quantity AS stock, products.image
     FROM cart JOIN products ON products.id = cart.product_id
     WHERE cart.user_id = ? ORDER BY cart.id DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// حساب المجموع الكلي
$total = 0;
foreach ($items as $item) {
    $total = $total + ((float) $item['price'] * (int) $item['cart_quantity']);
}

$pageTitle = 'Cart';
require __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <p class="text-primary fw-semibold mb-1">SHOPPING CART</p>
    <h1 class="h2 fw-bold">Your cart</h1>
</div>

<?php if (empty($items)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-5">
            <div class="empty-icon mx-auto mb-3"><i class="bi bi-cart"></i></div>
            <h2 class="h4">Your cart is empty</h2>
            <p class="text-secondary">Browse the catalog and add a product.</p>
            <a href="<?= e(baseUrl('shop/products.php')) ?>" class="btn btn-primary">Browse Products</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if ($item['image']): ?>
                                                    <img class="product-thumbnail" src="<?= e(baseUrl('uploads/products/' . rawurlencode($item['image']))) ?>" alt="">
                                                <?php else: ?>
                                                    <div class="product-icon"><i class="bi bi-box"></i></div>
                                                <?php endif; ?>
                                                <div class="fw-semibold"><?= e($item['name']) ?></div>
                                            </div>
                                        </td>
                                        <td>$<?= number_format((float) $item['price'], 2) ?></td>
                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                                <input class="form-control form-control-sm quantity-input" type="number" name="quantity" min="0" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['cart_quantity'] ?>">
                                                <button class="btn btn-light btn-sm border">Update</button>
                                            </form>
                                        </td>
                                        <td class="fw-semibold">$<?= number_format((float) $item['price'] * (int) $item['cart_quantity'], 2) ?></td>
                                        <td class="pe-4">
                                            <form method="post">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?= (int) $item['product_id'] ?>">
                                                <button class="btn btn-outline-danger btn-sm" aria-label="Remove"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold">Order summary</h2>
                    <div class="d-flex justify-content-between border-top border-bottom py-3 my-3">
                        <span>Total</span>
                        <strong class="h5 mb-0">$<?= number_format($total, 2) ?></strong>
                    </div>
                    <a href="<?= e(baseUrl('shop/checkout.php')) ?>" class="btn btn-primary btn-lg w-100">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
