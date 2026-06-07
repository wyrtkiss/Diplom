<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$key = (int)$_POST['key'];
$quantity = max(1, (int)$_POST['quantity']);

if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['quantity'] = $quantity;
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
} else {
    echo json_encode(['success' => false, 'error' => 'Товар не найден']);
}
?>