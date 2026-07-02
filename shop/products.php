<?php

require_once __DIR__ . '/../includes/bootstrap.php';

$categoryId = 0;
if (isset($_GET['category']) && $_GET['category'] != '') {
    $categoryId = (int) $_GET['category'];
}
$search = trim($_GET['search'] ?? '');

// جلب التصنيفات
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);

// جلب المنتجات مع البحث والفلترة
if ($categoryId > 0 && $search !== '') {
    // فلترة حسب التصنيف والبحث
    $searchParam = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products LEFT JOIN categories ON categories.id = products.category_id
         WHERE products.name LIKE ? AND products.category_id = ?
         ORDER BY products.id DESC"
    );
    $stmt->bind_param('si', $searchParam, $categoryId);
    $stmt->execute();
    $products = $stmt->get_result();
} elseif ($categoryId > 0) {
    // فلترة حسب التصنيف فقط
    $stmt = $conn->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products LEFT JOIN categories ON categories.id = products.category_id
         WHERE products.category_id = ?
         ORDER BY products.id DESC"
    );
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $products = $stmt->get_result();
} elseif ($search !== '') {
    // بحث فقط
    $searchParam = '%' . $search . '%';
    $stmt = $conn->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products LEFT JOIN categories ON categories.id = products.category_id
         WHERE products.name LIKE ?
         ORDER BY products.id DESC"
    );
    $stmt->bind_param('s', $searchParam);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    // كل المنتجات
    $products = $conn->query(
        "SELECT products.*, categories.name AS category_name
         FROM products LEFT JOIN categories ON categories.id = products.category_id
         ORDER BY products.id DESC"
    );
}

$pageTitle = 'Products';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
    <div>
        <p class="text-primary fw-semibold mb-1">CATALOG</p>
        <h1 class="h2 fw-bold mb-1">Products</h1>
        <p class="text-secondary mb-0">Find something useful today.</p>
    </div>
</div>

<!-- نموذج البحث والفلترة -->
<form method="get" class="card border-0 shadow-sm mb-4">
    <div class="card-body row g-3">
        <div class="col-md-7">
            <label class="form-label fw-semibold" for="search">Search</label>
            <input class="form-control" id="search" name="search" value="<?= e($search) ?>" placeholder="Product name">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" for="category">Category</label>
            <select class="form-select" id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<!-- عرض المنتجات -->
<div class="row g-4">
    <?php if ($products->num_rows === 0): ?>
        <div class="col-12">
            <div class="alert alert-light border">No products match your search.</div>
        </div>
    <?php endif; ?>
    <?php while ($product = $products->fetch_assoc()): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card product-card h-100 border-0 shadow-sm">
                <?php if ($product['image']): ?>
                    <img src="<?= e(baseUrl('uploads/products/' . rawurlencode($product['image']))) ?>" class="card-img-top product-card-image" alt="<?= e($product['name']) ?>">
                <?php else: ?>
                    <div class="product-placeholder"><i class="bi bi-box"></i></div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <small class="text-primary fw-semibold"><?= e($product['category_name'] ?: 'Uncategorized') ?></small>
                    <h2 class="h5 fw-bold mt-2"><?= e($product['name']) ?></h2>
                    <p class="text-secondary product-summary"><?= e($product['description'] ?: 'No description available.') ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <div>
                            <strong>$<?= number_format((float) $product['price'], 2) ?></strong>
                            <small class="d-block text-secondary"><?= (int) $product['quantity'] ?> available</small>
                        </div>
                        <a href="<?= e(baseUrl('shop/product.php?id=' . (int) $product['id'])) ?>" class="btn btn-outline-primary">Details</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
