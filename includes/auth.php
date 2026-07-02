<?php

// دالة لإرجاع بيانات المستخدم الحالي
function currentUser()
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

// دالة للتحقق إذا المستخدم مسجل دخول
function isLoggedIn()
{
    if (currentUser() !== null) {
        return true;
    }
    return false;
}

// دالة للتحقق إذا المستخدم أدمن
function isAdmin()
{
    $user = currentUser();
    if ($user !== null && $user['role'] === 'admin') {
        return true;
    }
    return false;
}

// دالة تجبر المستخدم على تسجيل الدخول
function requireLogin()
{
    if (!isLoggedIn()) {
        flash('warning', 'Please log in to continue.');
        redirect(baseUrl('auth/login.php'));
    }
}

// دالة تجبر المستخدم يكون أدمن
function requireAdmin()
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

// دالة لتحويل المستخدم المسجل بعيداً عن صفحات تسجيل الدخول
function redirectLoggedInUser()
{
    if (!isLoggedIn()) {
        return;
    }

    if (isAdmin()) {
        redirect(baseUrl('admin/dashboard.php'));
    } else {
        redirect(baseUrl('index.php'));
    }
}
