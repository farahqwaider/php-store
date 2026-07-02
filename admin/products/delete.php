<?php

require_once __DIR__ . '/../../includes/bootstrap.php';
requireAdmin();

// التأكد من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?status=error");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if (!$id) {
    header("Location: index.php?status=error");
    exit;
}

// جلب بيانات المنتج للحصول على اسم الصورة
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php?status=not-found");
    exit;
}

// حذف المنتج من قاعدة البيانات
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// حذف صورة المنتج من المجلد
if ($stmt->affected_rows > 0 && $product['image']) {
    $imagePath = __DIR__ . '/../../uploads/products/' . $product['image'];
    if (is_file($imagePath)) {
        unlink($imagePath);
    }
    header("Location: index.php?status=deleted");
} else {
    header("Location: index.php?status=not-found");
}
exit;
