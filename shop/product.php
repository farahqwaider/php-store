<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);

// جلب بيانات المنتج مع اسم التصنيف
$stmt = $conn->prepare(
    'SELECT products.*, categories.name AS category_name
     FROM products LEFT JOIN categories ON categories.id = products.category_id
     WHERE products.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}

$pageTitle = $product['name'];
require __DIR__ . '/../includes/header.php';
?>

<a href="<?= e(baseUrl('shop/products.php')) ?>" class="text-decoration-none d-inline-block mb-3">
    <i class="bi bi-arrow-left me-2"></i>Back to products
</a>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="row g-0">
        <div class="col-lg-6">
            <?php if ($product['image']): ?>
                <img src="<?= e(baseUrl('uploads/products/' . rawurlencode($product['image']))) ?>" class="detail-image" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <div class="detail-placeholder"><i class="bi bi-box"></i></div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <div class="p-4 p-md-5">
                <span class="badge category-badge rounded-pill"><?= e($product['category_name'] ?: 'Uncategorized') ?></span>
                <h1 class="display-6 fw-bold mt-3"><?= e($product['name']) ?></h1>
                <p class="text-secondary fs-5"><?= nl2br(e($product['description'] ?: 'No description available.')) ?></p>
                <div class="display-6 fw-bold text-primary my-4">$<?= number_format((float) $product['price'], 2) ?></div>
                <?php if ((int) $product['quantity'] > 0): ?>
                    <p class="text-success"><i class="bi bi-check-circle me-1"></i><?= (int) $product['quantity'] ?> in stock</p>
                    <form method="post" action="<?= e(baseUrl('shop/cart.php')) ?>" class="d-flex gap-2">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['quantity'] ?>" class="form-control form-control-lg quantity-input" aria-label="Quantity">
                        <button class="btn btn-primary btn-lg flex-grow-1"><i class="bi bi-cart-plus me-2"></i>Add to Cart</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary">This product is currently out of stock.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
