<?php

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function requireCsrfToken() {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        die('Token CSRF tidak valid. Silakan muat ulang halaman.');
    }
}