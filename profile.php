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

$stmt = $pdo->prepare("SELECT * FROM usersD WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$update_success = '';
$update_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $update_error = 'Пожалуйста, заполните обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_error = 'Введите корректный email';
    } else {
        $stmt = $pdo->prepare("UPDATE usersD SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$first_name, $last_name, $phone, $email, $user_id])) {
            $update_success = 'Данные успешно обновлены';
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $stmt = $pdo->prepare("SELECT * FROM usersD WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $update_error = 'Ошибка при обновлении данных';
        }
    }
}

// Отзывы
$review_success = '';
$review_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $order_id = (int)$_POST['order_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        $review_error = 'Оценка должна быть от 1 до 5';
    } elseif (empty($comment)) {
        $review_error = 'Пожалуйста, напишите текст отзыва';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM reviewsD WHERE user_id = ? AND order_id = ?");
        $stmt->execute([$user_id, $order_id]);
        if ($stmt->fetch()) {
            $review_error = 'Вы уже оставили отзыв на этот заказ';
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviewsD (user_id, order_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$user_id, $order_id, $rating, $comment])) {
                $review_success = true;
            } else {
                $review_error = 'Ошибка при сохранении отзыва';
            }
        }
    }
}

$stmt = $pdo->prepare("
    SELECT DISTINCT o.id, o.order_number, o.created_at, o.status, o.total, o.address, o.comment
    FROM ordersD o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

foreach ($orders as &$order) {
    $stmt_items = $pdo->prepare("
        SELECT oi.product_id, oi.quantity, oi.price, 
               oi.size_width, oi.size_depth, oi.size_height,
               oi.material_name, oi.color_name,
               p.name as product_name
        FROM order_itemsD oi
        JOIN productsD p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt_items->execute([$order['id']]);
    $order['items'] = $stmt_items->fetchAll();

    $products_list = [];
    foreach ($order['items'] as $item) {
        $item_text = $item['product_name'] . ' (' . $item['quantity'] . ' шт.)';

        $sizes = [];
        if ($item['size_width']) $sizes[] = 'шир.' . $item['size_width'] . 'см';
        if ($item['size_depth']) $sizes[] = 'глуб.' . $item['size_depth'] . 'см';
        if ($item['size_height']) $sizes[] = 'выс.' . $item['size_height'] . 'см';
        if (!empty($sizes)) {
            $item_text .= ' [' . implode(', ', $sizes) . ']';
        }

        $options = [];
        if ($item['material_name']) $options[] = $item['material_name'];
        if ($item['color_name']) $options[] = $item['color_name'];
        if (!empty($options)) {
            $item_text .= ' (' . implode(' / ', $options) . ')';
        }
        
        $products_list[] = $item_text;
    }
    $order['products_list'] = implode(', ', $products_list);

    $stmt_review = $pdo->prepare("SELECT * FROM reviewsD WHERE user_id = ? AND order_id = ?");
    $stmt_review->execute([$user_id, $order['id']]);
    $order['review'] = $stmt_review->fetch();
}

$stmt_reviews = $pdo->prepare("
    SELECT r.*, o.order_number 
    FROM reviewsD r
    JOIN ordersD o ON r.order_id = o.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt_reviews->execute([$user_id]);
$user_reviews = $stmt_reviews->fetchAll();

$status_styles = [
    'Новый' => ['class' => 'status-new', 'icon' => 'fa-clock'],
    'На изготовлении' => ['class' => 'status-production', 'icon' => 'fa-gear'],
    'Отправлен' => ['class' => 'status-shipped', 'icon' => 'fa-truck'],
    'Доставлен' => ['class' => 'status-delivered', 'icon' => 'fa-check-circle'],
    'Отменен' => ['class' => 'status-cancelled', 'icon' => 'fa-ban']
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Личный кабинет - ЯрМебель</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --primary-dark: #6B3410;
            --dark: #2C3E50;
            --text: #333;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --bg-white: #fff;
            --border: #e0e0e0;
            --shadow: 0 5px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: var(--text); background-color: var(--bg-light); }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        .header {
            background: var(--bg-white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            gap: 20px;
            flex-wrap: wrap;
        }
        .logo-text { font-size: 28px; font-weight: 700; font-family: 'Playfair Display', serif; color: var(--dark); }
        .logo-accent { color: var(--primary); }
        .header-search { flex: 1; max-width: 400px; }
        .search-form { display: flex; border: 1px solid var(--border); border-radius: 30px; overflow: hidden; }
        .search-input { flex: 1; padding: 12px 20px; border: none; outline: none; }
        .search-btn { background: var(--primary); border: none; padding: 0 20px; color: white; cursor: pointer; }
        .header-actions { display: flex; gap: 25px; }
        .action-icon { position: relative; font-size: 22px; color: var(--dark); transition: var(--transition); }
        .action-icon:hover { color: var(--primary); }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -12px;
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-main { border-top: 1px solid var(--border); padding: 12px 0; }
        .nav-main ul { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; }
        .nav-main a { font-size: 15px; font-weight: 500; transition: var(--transition); }
        .nav-main a:hover, .nav-main a.active { color: var(--primary); }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; }

        .profile-section { padding: 40px 0; }
        .profile-grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        
        .sidebar-card {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
        }
        .avatar { text-align: center; margin-bottom: 20px; }
        .avatar i { font-size: 80px; color: var(--primary); }
        .user-name { font-size: 20px; font-weight: 600; text-align: center; margin-bottom: 5px; }
        .user-email { text-align: center; color: var(--text-light); margin-bottom: 20px; font-size: 14px; }
        .sidebar-menu { border-top: 1px solid var(--border); padding-top: 20px; }
        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
        }
        .sidebar-menu-item:hover { background: var(--bg-light); }
        .sidebar-menu-item.active { background: var(--primary); color: white; }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            color: #e74c3c;
            margin-top: 15px;
            transition: var(--transition);
        }
        .logout-btn:hover { background: #f8d7da; }
        
        .edit-form-card {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        .edit-form-card h2 { font-size: 22px; margin-bottom: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 14px; }
        input:focus { outline: none; border-color: var(--primary); }
        .btn-save { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 30px; cursor: pointer; font-weight: 600; transition: var(--transition); }
        .btn-save:hover { background: var(--primary-dark); transform: scale(1.02); }
        .success-msg { background: #d4edda; color: #155724; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        
        .orders-title { font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .order-card {
            background: var(--bg-white);
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .order-header {
            background: var(--bg-light);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
        }
        .order-number { font-weight: 700; font-size: 16px; }
        .order-number i { color: var(--primary); margin-right: 5px; }
        .order-date { font-size: 13px; color: var(--text-light); }
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-new { background: #3498db20; color: #3498db; }
        .status-production { background: #e67e2220; color: #e67e22; }
        .status-shipped { background: #9b59b620; color: #9b59b6; }
        .status-delivered { background: #27ae6020; color: #27ae60; }
        .status-cancelled { background: #e74c3c20; color: #e74c3c; }
        .order-body {
            padding: 20px;
            display: none;
            border-top: 1px solid var(--border);
        }
        .order-body.active { display: block; }
        .order-address { margin-bottom: 15px; color: var(--text-light); font-size: 14px; }
        .order-address i { width: 20px; color: var(--primary); margin-right: 8px; }
        .order-products-summary { margin-bottom: 15px; font-size: 14px; background: #f9f9f9; padding: 10px; border-radius: 12px; }
        .order-products-summary ul { margin-left: 20px; margin-top: 5px; }
        .order-products-summary li { margin-bottom: 8px; font-size: 13px; }
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            margin-top: 10px;
        }
        .order-total { font-size: 18px; font-weight: 700; color: var(--primary); }
        .btn-invoice {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }
        .btn-invoice:hover { background: var(--primary); color: white; }
        
        .review-form-card {
            background: #fef5e8;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 12px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #f5a623 !important; }
        .star-rating input:checked ~ label { color: #f5a623 !important; }
        
        .no-orders { text-align: center; padding: 50px; background: var(--bg-white); border-radius: 20px; }
        .no-orders i { font-size: 50px; color: var(--primary); opacity: 0.5; margin-bottom: 15px; }
        
        .footer {
            background: linear-gradient(rgba(44,62,80,0.7), rgba(44,62,80,0.7)), url('images/1.jpg');
            color: #f0f0f0;
            padding: 40px 0 20px;
            text-align: center;
            margin-top: 40px;
        }
        
        @media (max-width: 900px) {
            .profile-grid { grid-template-columns: 1fr; }
            .sidebar-card { position: static; margin-bottom: 20px; }
        }
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 70px; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); }
            .order-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo"><a href="index.php"><span class="logo-text">Яр<span class="logo-accent">Мебель</span></span></a></div>
            <div class="header-search">
                <form action="catalog.php" method="get" class="search-form">
                    <input type="text" name="search" placeholder="Поиск товаров..." class="search-input">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="action-icon"><i class="far fa-user"></i></a>
                <a href="cart.php" class="action-icon">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                </a>
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

<section class="profile-section">
    <div class="container">
        <?php if ($update_success): ?>
            <div class="success-msg"><?php echo $update_success; ?></div>
        <?php endif; ?>
        <?php if ($update_error): ?>
            <div class="error-msg"><?php echo $update_error; ?></div>
        <?php endif; ?>
        <?php if ($review_success === true): ?>
            <div class="success-msg">Спасибо за ваш отзыв!</div>
        <?php endif; ?>
        <?php if ($review_error): ?>
            <div class="error-msg"><?php echo $review_error; ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <div class="sidebar-card">
                <div class="avatar"><i class="fas fa-user-circle"></i></div>
                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="sidebar-menu">
                    <div class="sidebar-menu-item active" data-tab="edit"><i class="fas fa-user-edit"></i> <span>Редактировать профиль</span></div>
                    <div class="sidebar-menu-item" data-tab="orders"><i class="fas fa-shopping-bag"></i> <span>Мои заказы</span></div>
                    <div class="sidebar-menu-item" data-tab="reviews"><i class="fas fa-star"></i> <span>Мои отзывы</span></div>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Выйти</span></a>
            </div>
            
            <div class="right-content">
                <div id="tab-edit" class="tab-content">
                    <div class="edit-form-card">
                        <h2><i class="fas fa-user-edit"></i> Редактирование профиля</h2>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group"><label>Имя</label><input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required></div>
                                <div class="form-group"><label>Фамилия</label><input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required></div>
                            </div>
                            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
                            <div class="form-group"><label>Телефон</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"></div>
                            <button type="submit" name="update_profile" class="btn-save">Сохранить изменения</button>
                        </form>
                    </div>
                </div>
                <div id="tab-orders" class="tab-content" style="display: none;">
                    <div class="orders-title"><i class="fas fa-shopping-bag"></i> Мои заказы</div>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header" onclick="this.nextElementSibling.classList.toggle('active')">
                                <div class="order-number"><i class="fas fa-receipt"></i> Заказ № <?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="order-date"><i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                                <div class="order-status <?php echo $status_styles[$order['status']]['class'] ?? 'status-new'; ?>">
                                    <i class="fas <?php echo $status_styles[$order['status']]['icon'] ?? 'fa-clock'; ?>"></i> 
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </div>
                            </div>
                            <div class="order-body">
                                <div class="order-address"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($order['address']); ?></div>
                                
                                <div class="order-products-summary">
                                    <strong>📦 Состав заказа:</strong>
                                    <ul>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li>
                                                <?php echo htmlspecialchars($item['product_name']); ?> — <?php echo $item['quantity']; ?> шт. — <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                                                <?php if ($item['size_width'] || $item['size_depth'] || $item['size_height']): ?>
                                                    <br><small style="color:#666;">
                                                        📐 
                                                        <?php 
                                                            $sizes = [];
                                                            if ($item['size_width']) $sizes[] = 'ширина ' . $item['size_width'] . ' см';
                                                            if ($item['size_depth']) $sizes[] = 'глубина ' . $item['size_depth'] . ' см';
                                                            if ($item['size_height']) $sizes[] = 'высота ' . $item['size_height'] . ' см';
                                                            echo implode(', ', $sizes);
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                                <?php if ($item['material_name'] || $item['color_name']): ?>
                                                    <br><small style="color:#666;">
                                                        🎨 
                                                        <?php 
                                                            $options = [];
                                                            if ($item['material_name']) $options[] = $item['material_name'];
                                                            if ($item['color_name']) $options[] = $item['color_name'];
                                                            echo implode(' / ', $options);
                                                        ?>
                                                    </small>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="order-footer">
                                    <div class="order-total">Итого: <?php echo number_format($order['total'], 0, '', ' '); ?> ₽</div>
                                    <button class="btn-invoice" data-order='<?php echo json_encode([
                                        'order_number' => $order['order_number'],
                                        'created_at' => $order['created_at'],
                                        'address' => $order['address'],
                                        'status' => $order['status'],
                                        'total' => $order['total'],
                                        'items' => $order['items']
                                    ]); ?>'><i class="fas fa-file-invoice"></i> Счет</button>
                                </div>
                                <?php if (!empty($order['comment'])): ?>
                                <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid var(--border); font-size: 13px; color: var(--text-light);">
                                    <i class="fas fa-comment"></i> Комментарий: <?php echo htmlspecialchars($order['comment']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-shopping-basket"></i>
                            <p>У вас пока нет заказов</p>
                            <a href="catalog.php" style="display: inline-block; margin-top: 15px; padding: 10px 25px; background: var(--primary); color: white; border-radius: 30px;">Перейти в каталог</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="tab-reviews" class="tab-content" style="display: none;">
                    <div class="orders-title"><i class="fas fa-star"></i> Мои отзывы</div>
                    
                    <div class="review-form-card">
                        <h3 style="margin-bottom: 15px;"><i class="fas fa-heart" style="color: #e91e63;"></i> Оставить отзыв о заказе</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label>Выберите доставленный заказ</label>
                                <select name="order_id" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 12px;">
                                    <option value="">-- Выберите заказ --</option>
                                    <?php
                                    $stmt_delivered = $pdo->prepare("
                                        SELECT o.id, o.order_number, o.created_at, o.total 
                                        FROM ordersD o
                                        WHERE o.user_id = ? AND o.status = 'Доставлен'
                                        AND NOT EXISTS (SELECT 1 FROM reviewsD r WHERE r.order_id = o.id AND r.user_id = ?)
                                        ORDER BY o.created_at DESC
                                    ");
                                    $stmt_delivered->execute([$user_id, $user_id]);
                                    $delivered_orders = $stmt_delivered->fetchAll();
                                    ?>
                                    <?php foreach ($delivered_orders as $del_order): ?>
                                        <option value="<?php echo $del_order['id']; ?>">
                                            Заказ № <?php echo htmlspecialchars($del_order['order_number']); ?> от <?php echo date('d.m.Y', strtotime($del_order['created_at'])); ?> (<?php echo number_format($del_order['total'], 0, '', ' '); ?> ₽)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ваша оценка</label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" value="5" id="star5"><label for="star5">★</label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">★</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Ваш отзыв</label>
                                <textarea name="comment" rows="4" placeholder="Поделитесь впечатлениями о заказе..." required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 12px;"></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn-save">Отправить отзыв</button>
                        </form>
                    </div>
                    
                    <div class="reviews-list" style="background: white; border-radius: 20px; padding: 25px;">
                        <h3 style="margin-bottom: 20px;"><i class="fas fa-list"></i> Мои отзывы</h3>
                        <?php if (count($user_reviews) > 0): ?>
                            <?php foreach ($user_reviews as $review): ?>
                            <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                                <div style="font-size: 13px; color: #666; margin-bottom: 8px;">
                                    Заказ № <?php echo htmlspecialchars($review['order_number']); ?> от <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star" style="color: #f5a623;"></i>
                                        <?php else: ?>
                                            <i class="far fa-star" style="color: #f5a623;"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div style="font-style: italic;">"<?php echo htmlspecialchars($review['comment']); ?>"</div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-star" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px; display: block;"></i>
                                <p>У вас пока нет отзывов</p>
                                <p style="font-size: 13px; margin-top: 10px;">Оставьте первый отзыв о доставленном заказе!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    const tabs = document.querySelectorAll('.sidebar-menu-item');
    const tabContents = { 
        edit: document.getElementById('tab-edit'), 
        orders: document.getElementById('tab-orders'),
        reviews: document.getElementById('tab-reviews')
    };
    
    function switchTab(tabName) {
        Object.values(tabContents).forEach(content => {
            if (content) content.style.display = 'none';
        });
        if (tabContents[tabName]) tabContents[tabName].style.display = 'block';
        tabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
        localStorage.setItem('active_profile_tab', tabName);
    }
    
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = tab.dataset.tab;
            if (tabName) switchTab(tabName);
        });
    });
    
    const savedTab = localStorage.getItem('active_profile_tab');
    if (savedTab && tabContents[savedTab]) {
        switchTab(savedTab);
    } else {
        switchTab('edit');
    }
    document.querySelectorAll('.btn-invoice').forEach(btn => {
        btn.addEventListener('click', function() {
            const order = JSON.parse(this.dataset.order);
            
            let itemsHtml = '';
            let calculatedTotal = 0;
            if (order.items && order.items.length > 0) {
                order.items.forEach(item => {
                    const price = Number(item.price) || 0;
                    const quantity = Number(item.quantity) || 0;
                    const sum = price * quantity;
                    calculatedTotal += sum;
                    
                    let detailsHtml = '';
                    if (item.size_width || item.size_depth || item.size_height) {
                        let sizes = [];
                        if (item.size_width) sizes.push('ширина ' + item.size_width + ' см');
                        if (item.size_depth) sizes.push('глубина ' + item.size_depth + ' см');
                        if (item.size_height) sizes.push('высота ' + item.size_height + ' см');
                        if (sizes.length) detailsHtml += '<br><small>' + sizes.join(', ') + '</small>';
                    }
                    if (item.material_name || item.color_name) {
                        let options = [];
                        if (item.material_name) options.push(item.material_name);
                        if (item.color_name) options.push(item.color_name);
                        if (options.length) detailsHtml += '<br><small>' + options.join(' / ') + '</small>';
                    }
                    
                    itemsHtml += `<tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">${item.product_name}${detailsHtml}</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">${quantity} шт.</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">${price.toLocaleString('ru-RU')} ₽</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">${sum.toLocaleString('ru-RU')} ₽</td>
                    </tr>`;
                });
            } else {
                itemsHtml = '<tr><td colspan="4" style="padding: 20px; text-align: center;">Нет информации о товарах</td></tr>';
            }
            
            const displayTotal = calculatedTotal > 0 ? calculatedTotal : Number(order.total);
            const statusClass = {
                'Новый': 'status-new', 'На изготовлении': 'status-production',
                'Отправлен': 'status-shipped', 'Доставлен': 'status-delivered', 'Отменен': 'status-cancelled'
            }[order.status] || 'status-new';
            
            const win = window.open('', '_blank', 'width=900,height=700');
            win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Счет №${order.order_number}</title><style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Inter', sans-serif; padding: 40px; background: #f5f5f5; }
                .invoice-container { max-width: 900px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; }
                .invoice-header { background: linear-gradient(135deg, #8B4513 0%, #6B3410 100%); color: white; padding: 30px; text-align: center; }
                .invoice-header .logo { font-size: 32px; font-weight: 700; font-family: 'Playfair Display', serif; }
                .invoice-header .logo span { color: #F0E68C; }
                .invoice-header h1 { font-size: 24px; margin-top: 15px; }
                .invoice-body { padding: 30px; }
                .info-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee; }
                .info-box h3 { color: #8B4513; margin-bottom: 10px; font-size: 16px; }
                .info-box p { color: #666; margin-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background: #8B4513; color: white; padding: 12px; text-align: left; }
                td { padding: 10px; border-bottom: 1px solid #eee; }
                .total-section { text-align: right; padding-top: 20px; margin-top: 20px; border-top: 2px solid #eee; }
                .total-label { font-size: 18px; font-weight: 600; }
                .total-amount { font-size: 24px; font-weight: 700; color: #8B4513; }
                .invoice-footer { background: #f8f9fa; padding: 20px 30px; text-align: center; font-size: 12px; color: #999; }
                .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-left: 10px; }
                .status-delivered { background: #27ae6020; color: #27ae60; }
            </style></head><body>
                <div class="invoice-container">
                    <div class="invoice-header"><div class="logo">Яр<span>Мебель</span></div><h1>СЧЕТ № ${order.order_number}</h1></div>
                    <div class="invoice-body">
                        <div class="info-section">
                            <div class="info-box"><h3>📄 Информация о заказе</h3><p><strong>Номер заказа:</strong> ${order.order_number}</p><p><strong>Дата заказа:</strong> ${new Date(order.created_at).toLocaleString('ru-RU')}</p><p><strong>Статус:</strong> <span class="status-badge ${statusClass}">${order.status}</span></p></div>
                            <div class="info-box"><h3>🏠 Адрес доставки</h3><p>${order.address}</p></div>
                        </div>
                        <h3 style="margin: 20px 0 10px;">🛒 Состав заказа</h3>
                        <table><thead><tr><th>Наименование товара</th><th>Количество</th><th>Цена</th><th>Сумма</th></tr></thead><tbody>${itemsHtml}</tbody></table>
                        <div class="total-section"><span class="total-label">Итого к оплате:</span> <span class="total-amount">${displayTotal.toLocaleString('ru-RU')} ₽</span></div>
                    </div>
                    <div class="invoice-footer"><p>Спасибо за покупку в магазине ЯрМебель!</p><p>По всем вопросам: info@yarmeble.ru | +7 (4852) 12-34-56</p></div>
                </div>
            </body></html>`);
            win.document.close();
        });
    });
</script>
</body>
</html>