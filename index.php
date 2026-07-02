<?php

require_once __DIR__ . '/includes/bootstrap.php';

$featured = $conn->query(
    "SELECT products.*, categories.name AS category_name
     FROM products
     LEFT JOIN categories ON categories.id = products.category_id
     ORDER BY products.id DESC
     LIMIT 6"
);
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="p-5 mb-5 bg-light rounded-3 border">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <span class="badge bg-primary mb-3">Simple. Secure. Convenient.</span>
            <h1 class="display-5 fw-bold mb-3">Everything you need, in one small store.</h1>
            <p class="lead text-muted mb-4">Browse our latest products, manage your cart, and place your order in a few clicks.</p>
            <a href="<?= e(baseUrl('shop/products.php')) ?>" class="btn btn-primary btn-lg">
                Shop Products <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="col-lg-4 text-center d-none d-lg-block">
            <i class="bi bi-bag-check text-primary" style="font-size: 6rem;"></i>
        </div>
    </div>
</section>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <p class="text-primary fw-semibold mb-1">NEW ARRIVALS</p>
        <h2 class="h3 fw-bold mb-0">Featured products</h2>
    </div>
    <a href="<?= e(baseUrl('shop/products.php')) ?>" class="text-decoration-none">View all</a>
</div>

<div class="row g-4">
    <?php if ($featured->num_rows === 0): ?>
        <div class="col-12"><div class="alert alert-light border">Products will appear here when the administrator adds them.</div></div>
    <?php endif; ?>
    <?php while ($product = $featured->fetch_assoc()): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card product-card h-100 border-0 shadow-sm">
                <?php if ($product['image']): ?>
                    <img src="<?= e(baseUrl('uploads/products/' . rawurlencode($product['image']))) ?>"
                         class="card-img-top product-card-image" alt="<?= e($product['name']) ?>">
                <?php else: ?>
                    <div class="product-placeholder"><i class="bi bi-box"></i></div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <small class="text-primary fw-semibold"><?= e($product['category_name'] ?: 'Uncategorized') ?></small>
                    <h3 class="h5 fw-bold mt-2"><?= e($product['name']) ?></h3>
                    <p class="text-secondary product-summary"><?= e($product['description'] ?: 'No description available.') ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="h5 fw-bold mb-0">$<?= number_format((float) $product['price'], 2) ?></span>
                        <a href="<?= e(baseUrl('shop/product.php?id=' . (int) $product['id'])) ?>" class="btn btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
