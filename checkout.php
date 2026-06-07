<?php
session_start();

$host = 'localhost';
$dbname = 'diplom';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM usersD WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Собираем данные корзины с учётом размеров
$cart_items = [];
$total = 0;
foreach ($cart as $item) {
    $stmt = $pdo->prepare("SELECT id, name, price FROM productsD WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $item_price = isset($item['size_price']) && $item['size_price'] > 0 ? $item['size_price'] : $product['price'];
        $item_total = $item_price * $item['quantity'];
        $total += $item_total;
        
        $material_name = '';
        if (!empty($item['material_id'])) {
            $stmt2 = $pdo->prepare("SELECT name FROM materialsD WHERE id = ?");
            $stmt2->execute([$item['material_id']]);
            $material = $stmt2->fetch();
            $material_name = $material ? $material['name'] : '';
        }
        
        $color_name = '';
        if (!empty($item['color_id'])) {
            $stmt2 = $pdo->prepare("SELECT name FROM colorsD WHERE id = ?");
            $stmt2->execute([$item['color_id']]);
            $color = $stmt2->fetch();
            $color_name = $color ? $color['name'] : '';
        }
        
        $cart_items[] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $item_price,
            'quantity' => $item['quantity'],
            'size_width' => $item['size_width'] ?? null,
            'size_depth' => $item['size_depth'] ?? null,
            'size_height' => $item['size_height'] ?? null,
            'material_name' => $material_name,
            'color_name' => $color_name
        ];
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['address']);
    $comment = trim($_POST['comment']);
    // Способ оплаты не сохраняем, просто берём для визуала
    $payment_method = $_POST['payment_method'] ?? 'cash';
    
    if (empty($address)) {
        $error = 'Введите адрес доставки';
    } else {
        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Сохраняем заказ БЕЗ payment_method
        $stmt = $pdo->prepare("
            INSERT INTO ordersD (user_id, order_number, status, total, address, comment, created_at) 
            VALUES (?, ?, 'Новый', ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $order_number, $total, $address, $comment]);
        
        $order_id = $pdo->lastInsertId();
        
        // Сохраняем товары с размерами
        $stmt_items = $pdo->prepare("
            INSERT INTO order_itemsD (order_id, product_id, quantity, price, size_width, size_depth, size_height, material_name, color_name) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($cart_items as $item) {
            $stmt_items->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['size_width'],
                $item['size_depth'],
                $item['size_height'],
                $item['material_name'],
                $item['color_name']
            ]);
        }
        
        unset($_SESSION['cart']);
        $success = 'Заказ успешно оформлен! Номер заказа: ' . $order_number;
        header('Refresh: 3; url=profile.php?tab=orders');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Оформление заказа - ЯрМебель</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --primary-dark: #6B3410;
            --dark: #2C3E50;
            --text: #333;
            --text-light: #666;
            --bg-light: #f5f5f5;
            --bg-white: #fff;
            --border: #e0e0e0;
            --shadow: 0 5px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: var(--text); background: var(--bg-light); }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        a { text-decoration: none; color: inherit; }
        
        .header {
            background: var(--bg-white);
            box-shadow: var(--shadow);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .logo a { font-size: 28px; font-weight: 700; font-family: 'Playfair Display', serif; color: var(--dark); }
        .logo span { color: var(--primary); }
        .header-search { flex: 1; max-width: 400px; }
        .search-form { display: flex; border: 1px solid var(--border); border-radius: 30px; overflow: hidden; }
        .search-input { flex: 1; padding: 10px 18px; border: none; outline: none; }
        .search-btn { background: var(--primary); border: none; padding: 0 20px; color: white; cursor: pointer; }
        .header-actions { display: flex; gap: 25px; }
        .action-icon { font-size: 22px; color: var(--dark); transition: var(--transition); }
        .action-icon:hover { color: var(--primary); }
        .nav-main { border-top: 2px solid var(--border); padding: 8px 0; }
        .nav-main ul { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; list-style: none; }
        .nav-main a { font-weight: 500; transition: var(--transition); }
        .nav-main a:hover { color: var(--primary); }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; }
        
        .checkout-page { padding: 60px 0; }
        .page-title { font-size: 32px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .back-to-cart {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .back-to-cart:hover { background: var(--primary); color: white; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 40px; }
        .checkout-form { background: white; border-radius: 20px; padding: 30px; box-shadow: var(--shadow); }
        .checkout-form h2 { margin-bottom: 25px; font-size: 22px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 14px; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); }
        .radio-group { display: flex; gap: 20px; margin-top: 8px; }
        .radio-group label { display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; }
        .radio-group input { width: auto; }
        .btn-submit { width: 100%; padding: 14px; background: #27ae60; color: white; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: var(--transition); }
        .btn-submit:hover { background: #219a52; transform: scale(1.02); }
        
        .order-summary { background: white; border-radius: 20px; padding: 30px; box-shadow: var(--shadow); height: fit-content; }
        .order-summary h2 { margin-bottom: 20px; font-size: 22px; }
        .summary-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .summary-total { font-size: 20px; font-weight: 700; color: var(--primary); margin-top: 15px; padding-top: 15px; border-top: 2px solid var(--border); display: flex; justify-content: space-between; }
        
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        
        .footer {
            background: linear-gradient(rgba(44,62,80,0.7), rgba(44,62,80,0.7)), url('1.jpg');
            color: #f0f0f0;
            padding: 40px 0 20px;
            text-align: center;
            margin-top: 60px;
        }
        
        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 70px; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); }
            .nav-main ul { flex-direction: column; align-items: center; gap: 15px; }
            .header-search { display: none; }
            .page-title { flex-direction: column; align-items: flex-start; }
            .radio-group { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo"><a href="index.php">Яр<span>Мебель</span></a></div>
            <div class="header-search">
                <form action="catalog.php" method="get" class="search-form">
                    <input type="text" name="search" placeholder="Поиск товаров..." class="search-input">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="action-icon"><i class="far fa-user"></i></a>
                <a href="cart.php" class="action-icon"><i class="fas fa-shopping-bag"></i></a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="action-icon"><i class="fas fa-sign-out-alt"></i></a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="nav-main" id="mainNav">
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="about.php">О компании</a></li>
                <li><a href="delivery.php">Доставка и оплата</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="checkout-page">
    <div class="container">
        <div class="page-title">
            <span><i class="fas fa-credit-card"></i> Оформление заказа</span>
            <a href="cart.php" class="back-to-cart"><i class="fas fa-arrow-left"></i> Вернуться в корзину</a>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form">
                <h2><i class="fas fa-user"></i> Контактные данные</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly disabled>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly disabled>
                    </div>
                    <div class="form-group">
                        <label>Адрес доставки *</label>
                        <input type="text" name="address" placeholder="г. Ярославль, ул. ..." required>
                    </div>
                    <div class="form-group">
                        <label>Комментарий к заказу</label>
                        <textarea name="comment" rows="3" placeholder="Пожелания по доставке, время..."></textarea>
                    </div>
                    
                    <!-- СПОСОБ ОПЛАТЫ (только визуально, не сохраняется) -->
                    <div class="form-group">
                        <label>Способ оплаты</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="payment_method" value="cash" checked> 
                                <i class="fas fa-money-bill-wave"></i> Наличными при получении
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="card"> 
                                <i class="fas fa-credit-card"></i> Картой при получении
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="btn-submit">Подтвердить заказ</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2><i class="fas fa-shopping-bag"></i> Ваш заказ</h2>
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <span>
                            <?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?>
                            <?php if ($item['size_width'] || $item['size_depth'] || $item['size_height']): ?>
                                <br><small style="color:#666;">
                                    <?php 
                                        $sizes = [];
                                        if ($item['size_width']) $sizes[] = 'шир.' . $item['size_width'] . 'см';
                                        if ($item['size_depth']) $sizes[] = 'глуб.' . $item['size_depth'] . 'см';
                                        if ($item['size_height']) $sizes[] = 'выс.' . $item['size_height'] . 'см';
                                        echo implode(', ', $sizes);
                                    ?>
                                </small>
                            <?php endif; ?>
                            <?php if ($item['material_name'] || $item['color_name']): ?>
                                <br><small style="color:#666;">
                                    <?php echo htmlspecialchars($item['material_name']); ?>
                                    <?php if ($item['material_name'] && $item['color_name']) echo ' / '; ?>
                                    <?php echo htmlspecialchars($item['color_name']); ?>
                                </small>
                            <?php endif; ?>
                        </span>
                        <span><?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽</span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-total">
                    <span>Итого к оплате:</span>
                    <span><?php echo number_format($total, 0, '', ' '); ?> ₽</span>
                </div>
                <p style="font-size: 13px; color: #666; margin-top: 15px; text-align: center;">
                    <i class="fas fa-info-circle"></i> Оплата производится при получении заказа
                </p>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <p>&copy; 2026 ЯрМебель. Все права защищены.</p>
    </div>
</footer>

<script>
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            mainNav.style.display = mainNav.style.display === 'block' ? 'none' : 'block';
        });
    }
</script>
</body>
</html>