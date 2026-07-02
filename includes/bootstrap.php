<?php

// بدء الجلسة
session_start();

// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../config/database.php';

// تضمين ملف التحقق من المستخدم
require_once __DIR__ . '/auth.php';

// دالة لإنشاء رابط المشروع الرئيسي
function baseUrl($path = '')
{
    $script = $_SERVER['SCRIPT_NAME'];
    $base = '';

    if (strpos($script, '/admin/') !== false) {
        $base = substr($script, 0, strpos($script, '/admin/'));
    } elseif (strpos($script, '/auth/') !== false) {
        $base = substr($script, 0, strpos($script, '/auth/'));
    } elseif (strpos($script, '/shop/') !== false) {
        $base = substr($script, 0, strpos($script, '/shop/'));
    }

    return $base . '/' . ltrim($path, '/');
}

// دالة لإعادة التوجيه
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// دالة لتنظيف النصوص قبل عرضها في HTML
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// دالة لتخزين رسالة مؤقتة في الجلسة
function flash($type, $message)
{
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

// دالة لعرض الرسالة المؤقتة ثم حذفها
function pullFlash()
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $flash = [
        'type' => $_SESSION['flash_type'],
        'message' => $_SESSION['flash_message']
    ];

    unset($_SESSION['flash_type'], $_SESSION['flash_message']);

    return $flash;
}

// دالة لحساب عدد المنتجات في سلة المشتريات
function cartCount($conn)
{
    if (empty($_SESSION['user'])) {
        return 0;
    }

    $userId = $_SESSION['user']['id'];
    $stmt = $conn->prepare('SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total'] ?? 0;
}
