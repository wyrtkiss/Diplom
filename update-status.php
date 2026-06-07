<?php
session_start();

$host = 'localhost';
$dbname = 'diplom';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$allowed = ['Новый', 'На изготовлении', 'Отправлен', 'Доставлен', 'Отменен'];

if ($order_id && in_array($status, $allowed)) {
    // Проверяем текущий статус
    $stmt = $pdo->prepare("SELECT status FROM ordersD WHERE id = ?");
    $stmt->execute([$order_id]);
    $current_status = $stmt->fetchColumn();
    
    if ($current_status === false) {
        echo json_encode(['success' => false, 'error' => 'Заказ не найден']);
        exit;
    }
    
    if ($current_status !== $status) {
        $stmt = $pdo->prepare("UPDATE ordersD SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка обновления']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Статус не изменился']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
}
?>