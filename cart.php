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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $index = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        header('Location: cart.php');
        exit;
    }
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        header('Location: cart.php');
        exit;
    }
}

$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $key => $item) {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM productsD WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $material_name = '';
        if ($item['material_id']) {
            $stmt = $pdo->prepare("SELECT name FROM materialsD WHERE id = ?");
            $stmt->execute([$item['material_id']]);
            $material = $stmt->fetch();
            $material_name = $material ? $material['name'] : '';
        }
        
        $color_name = '';
        if ($item['color_id']) {
            $stmt = $pdo->prepare("SELECT name FROM colorsD WHERE id = ?");
            $stmt->execute([$item['color_id']]);
            $color = $stmt->fetch();
            $color_name = $color ? $color['name'] : '';
        }
        
        // Цена с учётом размеров (если есть)
        $item_price = isset($item['size_price']) && $item['size_price'] > 0 ? $item['size_price'] : $product['price'];
        $item_total = $item_price * $item['quantity'];
        $total += $item_total;
        
        // Формируем строку с размерами
        $size_text = '';
        if (!empty($item['size_width']) || !empty($item['size_depth']) || !empty($item['size_height'])) {
            $size_parts = [];
            if (!empty($item['size_width'])) $size_parts[] = 'ширина ' . $item['size_width'] . ' см';
            if (!empty($item['size_depth'])) $size_parts[] = 'глубина ' . $item['size_depth'] . ' см';
            if (!empty($item['size_height'])) $size_parts[] = 'высота ' . $item['size_height'] . ' см';
            $size_text = ' (' . implode(', ', $size_parts) . ')';
        }
        
        $cart_items[] = [
            'key' => $key,
            'product_id' => $product['id'],
            'name' => $product['name'],
            'image' => $product['image'],
            'material' => $material_name,
            'color' => $color_name,
            'size_text' => $size_text,
            'quantity' => $item['quantity'],
            'price' => $item_price,
            'total' => $item_total
        ];
    }
}

$categories = $pdo->query("SELECT id, name FROM categoriesD ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Корзина - ЯрМебель</title>
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
        .search-form:focus-within { border-color: var(--primary); }
        .search-input { flex: 1; padding: 12px 20px; border: none; outline: none; }
        .search-btn { background: var(--primary); border: none; padding: 0 20px; color: white; cursor: pointer; }
        .search-btn:hover { background: var(--primary-dark); }
        .header-actions { display: flex; align-items: center; gap: 25px; }
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
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--dark); }
        
        .cart-page { padding: 60px 0; min-height: 60vh; }
        .cart-title {
            font-size: 32px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .back-to-catalog {
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
        .back-to-catalog:hover {
            background: var(--primary);
            color: white;
        }
        .cart-empty {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
        }
        .cart-empty i { font-size: 60px; color: var(--primary); opacity: 0.4; margin-bottom: 20px; }
        .cart-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--bg-light); font-weight: 600; }
        .product-cell { display: flex; align-items: center; gap: 15px; }
        .product-cell img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .product-options { font-size: 12px; color: var(--text-light); margin-top: 5px; }
        .quantity-input { width: 70px; padding: 8px; border: 1px solid var(--border); border-radius: 8px; text-align: center; }
        .btn-remove { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; }
        .cart-total {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .cart-total h3 { font-size: 24px; }
        .total-price { font-size: 28px; font-weight: 700; color: var(--primary); }
        .btn-clear { background: #95a5a6; color: white; border: none; padding: 12px 25px; border-radius: 40px; cursor: pointer; font-weight: 500; transition: var(--transition); }
        .btn-clear:hover { background: #7f8c8d; }
        .btn-checkout { background: #27ae60; color: white; border: none; padding: 12px 35px; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: var(--transition); }
        .btn-checkout:hover { background: #219a52; transform: scale(1.02); }
        
        .footer {
            background: linear-gradient(rgba(44,62,80,0.7), rgba(44,62,80,0.7)), url('1.jpg');
            color: #f0f0f0;
            padding: 40px 0 20px;
            text-align: center;
            margin-top: 60px;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 70px; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); z-index: 1000; }
            .nav-main ul { flex-direction: column; align-items: center; gap: 15px; }
            .header-search { display: none; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { margin-bottom: 15px; border: 1px solid var(--border); border-radius: 12px; padding: 10px; }
            td { display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid var(--border); }
            td:last-child { border-bottom: none; }
            td::before { content: attr(data-label); font-weight: 600; width: 40%; }
            .product-cell { flex-direction: column; align-items: flex-start; }
            .cart-total { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php">
                    <span class="logo-text">Яр<span class="logo-accent">Мебель</span></span>
                </a>
            </div>
            <div class="header-search">
                <form action="catalog.php" method="get" class="search-form">
                    <input type="text" name="search" placeholder="Поиск товаров..." class="search-input">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="action-icon"><i class="far fa-user"></i></a>
                <a href="cart.php" class="action-icon" style="position: relative;">
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

<section class="cart-page">
    <div class="container">
        <div class="cart-title">
            <span><i class="fas fa-shopping-bag"></i> Корзина</span>
            <a href="catalog.php" class="back-to-catalog"><i class="fas fa-arrow-left"></i> Продолжить покупки</a>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-basket"></i>
                <h2>Ваша корзина пуста</h2>
                <p>Добавьте товары из каталога</p>
                <a href="catalog.php" style="display: inline-block; margin-top: 20px; padding: 12px 30px; background: var(--primary); color: white; border-radius: 40px;">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-table">
                <table>
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Материал/Цвет</th>
                            <th>Размеры</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr data-key="<?php echo $item['key']; ?>">
                            <td data-label="Товар">
                                <div class="product-cell">
                                    <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.src='images/products/default.jpg'">
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td data-label="Материал/Цвет">
                                <?php 
                                    $options = [];
                                    if (!empty($item['material'])) $options[] = $item['material'];
                                    if (!empty($item['color'])) $options[] = $item['color'];
                                    echo htmlspecialchars(implode(' / ', $options)) ?: '—';
                                ?>
                            </td>
                            <td data-label="Размеры" style="font-size: 13px; color: #666;">
                                <?php echo $item['size_text'] ?: '—'; ?>
                            </td>
                            <td data-label="Цена" class="item-price"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</td>
                            <td data-label="Количество">
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-key="<?php echo $item['key']; ?>">
                            </td>
                            <td data-label="Сумма" class="item-total"><?php echo number_format($item['total'], 0, '', ' '); ?> ₽</td>
                            <td data-label="">
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="remove_item" value="<?php echo $item['key']; ?>" class="btn-remove"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-total">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="clear_cart" class="btn-clear" onclick="return confirm('Очистить корзину?')"><i class="fas fa-trash-alt"></i> Очистить корзину</button>
                </form>
                <div>
                    <h3>Итого: <span class="total-price" id="totalPrice"><?php echo number_format($total, 0, '', ' '); ?> ₽</span></h3>
                    <br>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn-checkout"><i class="fas fa-credit-card"></i> Оформить заказ</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-checkout"><i class="fas fa-sign-in-alt"></i> Войти для оформления</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
    
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    async function updateQuantity(key, quantity) {
        try {
            const response = await fetch('update-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `key=${key}&quantity=${quantity}`
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Ошибка:', error);
        }
    }
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const key = this.dataset.key;
            const quantity = parseInt(this.value);
            if (quantity > 0) {
                updateQuantity(key, quantity);
            } else {
                this.value = 1;
                updateQuantity(key, 1);
            }
        });
    });
</script>
</body>
</html>