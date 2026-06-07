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

$site_name = "ЯрМебель";

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

if ($category_id > 0) {
    $stmt_cat = $pdo->prepare("SELECT id, name FROM categoriesD WHERE id = ?");
    $stmt_cat->execute([$category_id]);
    $category = $stmt_cat->fetch();
}

$is_search = !empty($search_query);
$is_category = !$is_search && $category_id > 0 && isset($category);


$order_by = "ORDER BY p.id";
if ($sort == 'price_asc') $order_by = "ORDER BY p.price ASC";
elseif ($sort == 'price_desc') $order_by = "ORDER BY p.price DESC";
elseif ($sort == 'name_asc') $order_by = "ORDER BY p.name ASC";
elseif ($sort == 'name_desc') $order_by = "ORDER BY p.name DESC";


if ($is_search) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.description, p.price, p.old_price, p.image
        FROM productsD p
        WHERE p.name LIKE :search OR p.description LIKE :search
        $order_by
    ");
    $stmt->execute(['search' => "%$search_query%"]);
    $products = $stmt->fetchAll();
    $page_title = "Результаты поиска: «$search_query»";
} elseif ($is_category) {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.description, p.price, p.old_price, p.image
        FROM productsD p
        WHERE p.category_id = ?
        $order_by
    ");
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll();
    $page_title = $category['name'];
} else {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.description, p.price, p.old_price, p.image
        FROM productsD p
        $order_by
    ");
    $products = $stmt->fetchAll();
    $page_title = "Все товары";
}

$stmt_categories = $pdo->query("SELECT id, name FROM categoriesD ORDER BY name");
$all_categories = $stmt_categories->fetchAll();

function getDiscount($old_price, $price) {
    if ($old_price && $old_price > $price) {
        return round(100 - ($price / $old_price * 100));
    }
    return 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $site_name; ?> - <?php echo htmlspecialchars($page_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --shadow-hover: 0 10px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s ease;
            --sale: #e74c3c;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: var(--text); background-color: var(--bg-white); }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; height: auto; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-up { animation: fadeInUp 0.6s ease forwards; }
        .animate-scale { animation: scaleIn 0.5s ease forwards; }

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

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
        }

        .logo-accent {
            color: var(--primary);
        }

        .header-search {
            flex: 1;
            max-width: 400px;
        }

        .search-form {
            display: flex;
            border: 1px solid var(--border);
            border-radius: 30px;
            overflow: hidden;
            transition: var(--transition);
        }

        .search-form:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: none;
            outline: none;
        }

        .search-btn {
            background: var(--primary);
            border: none;
            padding: 0 20px;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-btn:hover {
            background: var(--primary-dark);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .action-icon {
            position: relative;
            font-size: 22px;
            color: var(--dark);
            transition: var(--transition);
        }

        .action-icon:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

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

        .nav-main {
            border-top: 1px solid var(--border);
            padding: 12px 0;
        }

        .nav-main ul {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .nav-main a {
            font-size: 15px;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-main a:hover,
        .nav-main a.active {
            color: var(--primary);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--dark);
        }


        .page-hero {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('images/1.jpg');
            background-size: cover;
            background-position: center;
            padding: 50px 0;
            text-align: center;
            color: white;
        }
        .page-hero h1 { font-size: 38px; font-family: 'Playfair Display', serif; margin-bottom: 10px; }
        .page-hero p { font-size: 16px; opacity: 0.9; }

        .catalog-page { padding: 60px 0; background: var(--bg-light); }
        .catalog-wrapper { display: flex; gap: 40px; flex-wrap: wrap; }
        .sidebar { width: 260px; flex-shrink: 0; }
        .sidebar h3 { font-size: 18px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary); color: var(--dark); }
        .sidebar-categories { background: var(--bg-white); border-radius: 16px; padding: 20px; box-shadow: var(--shadow); margin-bottom: 25px; }
        .sidebar-categories ul li { margin-bottom: 10px; }
        .sidebar-categories ul li a { display: block; padding: 6px 0; transition: var(--transition); font-size: 14px; }
        .sidebar-categories ul li a:hover, .sidebar-categories ul li a.active { color: var(--primary); padding-left: 10px; }

        .sort-top {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 15px 20px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        .sort-top select { padding: 10px 15px; border: 1px solid var(--border); border-radius: 30px; font-family: inherit; cursor: pointer; }
        .content { flex: 1; }
        .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
        .product-card {
            background: var(--bg-white);
            border-radius: 16px;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .sale-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--sale);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            z-index: 10;
        }
        .product-image { display: block; aspect-ratio: 1 / 1; overflow: hidden; background: var(--bg-light); }
        .product-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .product-card:hover .product-image img { transform: scale(1.05); }
        .product-info { padding: 20px; }
        .product-info h3 { font-size: 16px; margin-bottom: 10px; }
        .product-info h3 a { color: var(--dark); }
        .product-info h3 a:hover { color: var(--primary); }
        .product-price { margin-bottom: 15px; }
        .current-price { font-size: 20px; font-weight: 700; color: var(--primary); }
        .old-price { font-size: 14px; color: var(--text-light); text-decoration: line-through; margin-right: 8px; }
        .btn-details {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-details:hover { background: var(--primary); color: white; transform: scale(1.02); }
        .empty-catalog { text-align: center; padding: 60px; background: var(--bg-white); border-radius: 20px; }
        .empty-catalog i { font-size: 48px; color: var(--primary); opacity: 0.4; margin-bottom: 15px; }

        .newsletter {
            padding: 60px 0;
            background: linear-gradient(rgba(139, 69, 19, 0.7), rgba(139, 69, 19, 0.7)), url('images/1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            text-align: center;
        }
        .newsletter-box { max-width: 500px; margin: 0 auto; }
        .newsletter-box h3 { font-size: 28px; margin-bottom: 10px; }
        .newsletter-form { display: flex; gap: 10px; margin-top: 20px; }
        .newsletter-form input { flex: 1; padding: 12px 20px; border: none; border-radius: 40px; outline: none; }
        .newsletter-form button { padding: 12px 25px; background: var(--dark); border: none; border-radius: 40px; color: white; cursor: pointer; }

        .footer {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('images/1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #f0f0f0;
            padding: 60px 0 20px;
        }
        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; margin-bottom: 40px; }
        .footer-logo span { font-size: 28px; font-weight: 700; font-family: 'Playfair Display', serif; color: white; }
        .footer-logo span span { color: var(--primary); }
        .footer-col p { margin-top: 15px; line-height: 1.6; font-size: 14px; }
        .footer-col h4 { color: white; margin-bottom: 20px; font-size: 18px; }
        .footer-col ul li { margin-bottom: 10px; font-size: 14px; }
        .footer-col ul li a:hover { color: var(--primary); }
        .footer-contacts li { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 14px; }
        .footer-contacts i { width: 20px; color: var(--primary); }
        .footer-social { display: flex; gap: 15px; margin-top: 20px; }
        .footer-social a { width: 38px; height: 38px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition); color: white; }
        .footer-social a:hover { background: var(--primary); transform: translateY(-3px); }
        .footer-bottom { text-align: center; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 13px; }

        .floating-btn { position: fixed; bottom: 30px; right: 30px; background: #25D366; color: white; width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 999; }
        .floating-btn:hover { transform: scale(1.1); }
        .floating-btn i { font-size: 28px; }

        @media (max-width: 1024px) {
            .products-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .header-search { display: none; }
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); z-index: 1000; }
            .nav-main ul { flex-direction: column; gap: 15px; align-items: center; }
            .catalog-wrapper { flex-direction: column; }
            .sidebar { width: 100%; }
            .products-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-social { justify-content: center; }
            .sort-top { flex-direction: column; align-items: stretch; text-align: center; }
        }
        @media (max-width: 480px) {
            .floating-btn { width: 45px; height: 45px; bottom: 20px; right: 20px; }
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
                    <input type="text" name="search" placeholder="Поиск товаров..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="action-icon"><i class="far fa-user"></i></a>
                <a href="cart.php" class="action-icon cart-link"><i class="fas fa-shopping-bag"></i><span class="cart-count">0</span></a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="action-icon"><i class="fas fa-sign-out-alt"></i></a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="nav-main" id="mainNav">
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="catalog.php" class="active">Каталог</a></li>
                <li><a href="about.php">О компании</a></li>
                <li><a href="delivery.php">Доставка и оплата</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="page-hero">
    <div class="container">
        <h1 class="animate-up"><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="animate-up" style="animation-delay: 0.1s;"><?php echo count($products); ?> товаров</p>
    </div>
</section>

<section class="catalog-page">
    <div class="container">
        <div class="catalog-wrapper">
            <aside class="sidebar">
                <div class="sidebar-categories">
                    <h3>Категории</h3>
                    <ul>
                        <li><a href="catalog.php" <?php echo !$is_category && !$is_search ? 'class="active"' : ''; ?>>Все товары</a></li>
                        <?php foreach ($all_categories as $cat): ?>
                            <li><a href="catalog.php?category=<?php echo $cat['id']; ?>" <?php echo ($is_category && $category_id == $cat['id']) ? 'class="active"' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>

            <div class="content">
                <div class="sort-top">
                    <div><i class="fas fa-list"></i> Найдено: <?php echo count($products); ?> товаров</div>
                    <div>
                        <label><i class="fas fa-sort"></i> Сортировать:</label>
                        <select id="sortSelect">
                            <option value="default" <?php echo $sort == 'default' ? 'selected' : ''; ?>>По умолчанию</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Сначала дешевле</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Сначала дороже</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Название А-Я</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Название Я-А</option>
                        </select>
                    </div>
                </div>

                <?php if (count($products) > 0): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): 
                        $discount = getDiscount($product['old_price'], $product['price']);
                    ?>
                    <div class="product-card animate-scale">
                        <?php if ($discount > 0): ?>
                            <div class="sale-badge">-<?php echo $discount; ?>%</div>
                        <?php endif; ?>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-image">
                            <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/products/default.jpg'">
                        </a>
                        <div class="product-info">
                            <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <div class="product-price">
                                <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                                    <span class="old-price"><?php echo number_format($product['old_price'], 0, '', ' '); ?> ₽</span>
                                <?php endif; ?>
                                <span class="current-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                            </div>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-details">
                                <i class="fas fa-info-circle"></i> Подробнее
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-catalog">
                    <i class="fas fa-search"></i>
                    <h3>Товары не найдены</h3>
                    <p>Попробуйте выбрать другую категорию или изменить поисковый запрос</p>
                    <a href="catalog.php" style="display: inline-block; margin-top: 20px; padding: 10px 25px; background: var(--primary); color: white; border-radius: 30px;">Вернуться в каталог</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="newsletter">
    <div class="container">
        <div class="newsletter-box">
            <h3>Будьте в курсе новостей и акций</h3>
            <p>Подпишитесь на рассылку и получайте персональные скидки</p>
            <form class="newsletter-form" method="post">
                <input type="email" name="email" placeholder="Ваш e-mail" required>
                <button type="submit">Подписаться <i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo"><span>Яр<span>Мебель</span></span></div>
                <p>Качественная мебель от производителя в Ярославле. Создаем уют в каждом доме с 2018 года.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-vk"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Каталог</h4>
                <ul>
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM categoriesD ORDER BY name LIMIT 6");
                    while($cat = $stmt->fetch()):
                    ?>
                        <li><a href="catalog.php?category=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Покупателям</h4>
                <ul>
                    <li><a href="delivery.php">Доставка и оплата</a></li>
                    <li><a href="delivery.php">Возврат и обмен</a></li>
                    <li><a href="delivery.php">Гарантия 5 лет</a></li>
                    <li><a href="delivery.php">Сборка мебели</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Контакты</h4>
                <ul class="footer-contacts">
                    <li><i class="fas fa-phone"></i> <a href="tel:+74852212345">+7 (4852) 12-34-56</a></li>
                    <li><i class="fas fa-envelope"></i> <a href="mailto:info@yarmeble.ru">info@yarmeble.ru</a></li>
                    <li><i class="fas fa-map-marker-alt"></i> г. Ярославль, ул. Промышленная, 12</li>
                    <li><i class="fas fa-clock"></i> Пн-Вс: 9:00 - 20:00</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 ЯрМебель. Все права защищены.</p>
        </div>
    </div>
</footer>

<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            mainNav.style.display = mainNav.style.display === 'block' ? 'none' : 'block';
        });
    }

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            window.location.href = url.toString();
        });
    }
</script>

</body>
</html>