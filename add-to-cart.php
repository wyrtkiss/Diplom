<?php
session_start();
header('Content-Type: application/json');

$product_id = (int)$_POST['product_id'];
$material_id = isset($_POST['material_id']) && $_POST['material_id'] ? (int)$_POST['material_id'] : null;
$color_id = isset($_POST['color_id']) && $_POST['color_id'] ? (int)$_POST['color_id'] : null;
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

// Размеры
$size_width = isset($_POST['size_width']) && $_POST['size_width'] ? (int)$_POST['size_width'] : null;
$size_depth = isset($_POST['size_depth']) && $_POST['size_depth'] ? (int)$_POST['size_depth'] : null;
$size_height = isset($_POST['size_height']) && $_POST['size_height'] ? (int)$_POST['size_height'] : null;
$size_price = isset($_POST['size_price']) ? (float)$_POST['size_price'] : null;

if (!$product_id) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['temp_cart_item'] = [
        'product_id' => $product_id,
        'material_id' => $material_id,
        'color_id' => $color_id,
        'quantity' => $quantity,
        'size_width' => $size_width,
        'size_depth' => $size_depth,
        'size_height' => $size_height,
        'size_price' => $size_price
    ];
    echo json_encode(['success' => false, 'error' => 'Не авторизован', 'redirect' => 'login.php']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['product_id'] == $product_id && 
        $item['material_id'] == $material_id && 
        $item['color_id'] == $color_id &&
        $item['size_width'] == $size_width &&
        $item['size_depth'] == $size_depth &&
        $item['size_height'] == $size_height) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $product_id,
        'material_id' => $material_id,
        'color_id' => $color_id,
        'quantity' => $quantity,
        'size_width' => $size_width,
        'size_depth' => $size_depth,
        'size_height' => $size_height,
        'size_price' => $size_price
    ];
}

echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
?>