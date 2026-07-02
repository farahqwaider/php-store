<?php

require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin();

// جلب التصنيفات
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$errors = [];
$data = [
    'category_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'quantity' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => $_POST['category_id'] ?? '',
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => $_POST['price'] ?? '',
        'quantity' => $_POST['quantity'] ?? '',
    ];

    // التحقق من البيانات
    if ($data['name'] === '') {
        $errors[] = 'Product name is required.';
    }
    if ($data['category_id'] === '') {
        $errors[] = 'Please select a category.';
    }
    if ($data['price'] === '' || (float) $data['price'] <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }
    if ($data['quantity'] === '' || (int) $data['quantity'] < 0) {
        $errors[] = 'Quantity must be 0 or more.';
    }

    // رفع الصورة
    $image = null;
    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/products/';

        // إنشاء المجلد إذا لم يكن موجوداً
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // التحقق من نوع الملف
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Product image must be a JPG, PNG, WebP, or GIF file.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Product image must be 2 MB or smaller.';
        } else {
            // إنشاء اسم فريد للصورة
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = time() . '_' . rand(1000, 9999) . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }
    }

    // إدخال المنتج في قاعدة البيانات
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO products (category_id, image, name, description, price, quantity)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $categoryId = (int) $data['category_id'];
        $price = (float) $data['price'];
        $quantity = (int) $data['quantity'];
        $stmt->bind_param(
            "isssdi",
            $categoryId,
            $image,
            $data['name'],
            $data['description'],
            $price,
            $quantity
        );
        $stmt->execute();

        header("Location: index.php?status=created");
        exit;
    }
}

$pageTitle = 'Add Product';
$adminLayout = true;
require __DIR__ . '/../../includes/header.php';
?>

<div class="form-wrapper mx-auto">
    <a href="index.php" class="text-decoration-none d-inline-flex align-items-center mb-3">
        <i class="bi bi-arrow-left me-2"></i>Back to products
    </a>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="mb-4">
                <p class="text-primary fw-semibold mb-1">NEW PRODUCT</p>
                <h1 class="h3 fw-bold">Add product</h1>
                <p class="text-secondary mb-0">Enter the product details below.</p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Please fix:</div>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Product name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-lg"
                           maxlength="255" value="<?= e($data['name']) ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label fw-semibold">Category</label>
                    <select id="category_id" name="category_id" class="form-select form-select-lg" required>
                        <option value="">Choose a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"
                                <?= (int) $data['category_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              maxlength="2000"
                              placeholder="Optional product description"><?= e($data['description']) ?></textarea>
                    <div class="form-text">Maximum 2,000 characters.</div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Product image</label>
                    <input type="file" id="image" name="image" class="form-control form-control-lg"
                           accept=".jpg,.jpeg,.png,.webp,.gif">
                    <div class="form-text">JPG, PNG, WebP, or GIF. Maximum size: 2 MB.</div>
                    <img id="imagePreview" class="image-preview mt-3 d-none" alt="Product image preview">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-semibold">Price</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" id="price" name="price" min="0.01" step="0.01"
                                   max="99999999.99" class="form-control"
                                   value="<?= e($data['price']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-semibold">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="0" step="1"
                               max="1000000" class="form-control form-control-lg"
                               value="<?= e($data['quantity']) ?>" required>
                    </div>
                </div>

                <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                    <a href="index.php" class="btn btn-light border btn-lg">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('image').addEventListener('change', function(event) {
        var preview = document.getElementById('imagePreview');
        var file = event.target.files[0];

        if (!file) {
            preview.classList.add('d-none');
            preview.removeAttribute('src');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
