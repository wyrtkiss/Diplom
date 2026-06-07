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

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id == 0) {
    header('Location: catalog.php');
    exit;
}

// Получаем товар
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM productsD p
    LEFT JOIN categoriesD c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: catalog.php');
    exit;
}

// Получаем ТОЛЬКО те материалы, которые есть в наличии (in_stock_sklad > 0)
$stmt_materials = $pdo->prepare("
    SELECT DISTINCT m.id, m.name
    FROM product_variantsD pv
    JOIN materialsD m ON pv.material_id = m.id
    WHERE pv.product_id = ? AND pv.in_stock_sklad > 0 AND pv.material_id IS NOT NULL
    ORDER BY m.name
");
$stmt_materials->execute([$product_id]);
$materials = $stmt_materials->fetchAll();

// Получаем ТОЛЬКО те цвета, которые есть в наличии (in_stock_sklad > 0)
$stmt_colors = $pdo->prepare("
    SELECT DISTINCT c.id, c.name
    FROM product_variantsD pv
    JOIN colorsD c ON pv.color_id = c.id
    WHERE pv.product_id = ? AND pv.in_stock_sklad > 0 AND pv.color_id IS NOT NULL
    ORDER BY c.name
");
$stmt_colors->execute([$product_id]);
$colors = $stmt_colors->fetchAll();

// Проверяем, есть ли вообще хоть один вариант в наличии
$has_variants = (count($materials) > 0 || count($colors) > 0);

// Получаем похожие товары
$stmt_similar = $pdo->prepare("
    SELECT id, name, price, old_price, image
    FROM productsD
    WHERE category_id = ? AND id != ?
    LIMIT 4
");
$stmt_similar->execute([$product['category_id'], $product_id]);
$similar_products = $stmt_similar->fetchAll();

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$discount = ($product['old_price'] && $product['old_price'] > $product['price']) ? round(100 - ($product['price'] / $product['old_price'] * 100)) : 0;

// Вычисляем базовую площадь (минимальную) для отображения
$base_area_text = '';
if ($product['size_enabled'] == 1) {
    if ($product['size_width_min'] && $product['size_depth_min']) {
        $base_area = ($product['size_width_min'] * $product['size_depth_min']) / 10000;
        $base_area_text = 'Базовая цена за ' . $product['size_width_min'] . '×' . $product['size_depth_min'] . ' см (' . number_format($base_area, 2) . ' м²)';
    } elseif ($product['size_width_min'] && $product['size_height_min']) {
        $base_area = ($product['size_width_min'] * $product['size_height_min']) / 10000;
        $base_area_text = 'Базовая цена за ' . $product['size_width_min'] . '×' . $product['size_height_min'] . ' см (' . number_format($base_area, 2) . ' м²)';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЯрМебель - <?php echo htmlspecialchars($product['name']); ?></title>
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
        .nav-main a:hover { color: var(--primary); }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; }

        .product-page { padding: 60px 0; }
        .product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: var(--bg-white); border-radius: 24px; padding: 40px; box-shadow: var(--shadow); }
        .product-image img { width: 100%; border-radius: 20px; }
        .product-info h1 { font-size: 32px; font-family: 'Playfair Display', serif; margin-bottom: 15px; }
        .product-category { color: var(--primary); display: inline-block; margin-bottom: 15px; }
        .product-price { margin: 20px 0; }
        .current-price { font-size: 36px; font-weight: 700; color: var(--primary); }
        .old-price { font-size: 20px; color: var(--text-light); text-decoration: line-through; margin-right: 15px; }
        .sale-badge { background: #e74c3c; color: white; padding: 5px 15px; border-radius: 30px; display: inline-block; margin-left: 15px; }
        .price-note { font-size: 12px; color: var(--text-light); margin-top: 5px; }
        
        .size-section {
            margin: 25px 0;
            padding: 20px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .size-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .size-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 15px;
        }
        .size-field label {
            display: block;
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        .size-field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
        }
        .size-field input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .size-note {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 10px;
            text-align: center;
            background: #f8f6f4;
            padding: 8px;
            border-radius: 12px;
        }
        
        .options-section { margin: 25px 0; }
        .option-group { margin-bottom: 20px; }
        .option-group label { display: block; font-weight: 600; margin-bottom: 10px; }
        .option-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            cursor: pointer;
        }
        
        .btn-add-to-cart {
    width: 100%;
    padding: 15px;
    background: #ccc;
    color: white;
    border: none;
    border-radius: 40px;
    font-weight: 600;
    font-size: 16px;
    cursor: not-allowed;
    transition: var(--transition);
    margin-top: 35px; 
    margin-bottom: 10px;
}

.product-description {
    margin-bottom: 15px;
}
        .btn-add-to-cart.active {
            background: var(--primary);
            cursor: pointer;
        }
        .btn-add-to-cart.active:hover { background: var(--primary-dark); transform: scale(1.02); }
        
        .no-variants {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
        }
        
        .similar-section { padding: 60px 0; }
        .similar-section h2 { text-align: center; font-size: 28px; margin-bottom: 30px; }
        .similar-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
        .similar-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: var(--shadow); transition: var(--transition); }
        .similar-card:hover { transform: translateY(-5px); }
        .similar-card img { width: 100%; aspect-ratio: 1/1; object-fit: cover; }
        .similar-card h3 { font-size: 14px; padding: 12px; text-align: center; }
        .similar-price { text-align: center; padding-bottom: 12px; color: var(--primary); font-weight: 700; }
        
        .footer {
            background: linear-gradient(rgba(44,62,80,0.7), rgba(44,62,80,0.7)), url('images/1.jpg');
            color: #f0f0f0;
            padding: 40px 0 20px;
            text-align: center;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .product-grid { grid-template-columns: 1fr; padding: 20px; }
            .similar-grid { grid-template-columns: repeat(2, 1fr); }
            .size-row { grid-template-columns: 1fr; gap: 10px; }
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); }
            .header-search { display: none; }
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
                    <span class="cart-count"><?php echo $cart_count; ?></span>
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

<section class="product-page">
    <div class="container">
        <div class="product-grid">
            <div class="product-image">
                <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/products/default.jpg'">
            </div>
            <div class="product-info">
                <a href="catalog.php?category=<?php echo $product['category_id']; ?>" class="product-category"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($product['category_name']); ?></a>
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price">
                    <?php if ($discount > 0): ?>
                        <span class="old-price"><?php echo number_format($product['old_price'], 0, '', ' '); ?> ₽</span>
                        <span class="current-price" id="basePrice"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                        <span class="sale-badge">-<?php echo $discount; ?>%</span>
                    <?php else: ?>
                        <span class="current-price" id="basePrice"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                    <?php endif; ?>
                    <?php if ($product['size_enabled'] == 1 && !empty($base_area_text)): ?>
                        <div class="price-note"><?php echo $base_area_text; ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($product['size_enabled'] == 1): ?>
                <div class="size-section">
                    <div class="size-title">
                        <i class="fas fa-ruler-combined"></i> Изготовление по вашим размерам
                    </div>
                    <div class="size-row">
                        <?php if ($product['size_width_min'] && $product['size_width_max']): ?>
                        <div class="size-field">
                            <label>Ширина от <?php echo $product['size_width_min']; ?> до <?php echo $product['size_width_max']; ?></label>
                            <input type="number" id="sizeWidth" min="<?php echo $product['size_width_min']; ?>" max="<?php echo $product['size_width_max']; ?>" step="<?php echo $product['size_step'] ?: 1; ?>" value="<?php echo $product['size_width_min']; ?>">
                        </div>
                        <?php endif; ?>
                        <?php if ($product['size_depth_min'] && $product['size_depth_max']): ?>
                        <div class="size-field">
                            <label>Глубина от <?php echo $product['size_depth_min']; ?> до <?php echo $product['size_depth_max']; ?></label>
                            <input type="number" id="sizeDepth" min="<?php echo $product['size_depth_min']; ?>" max="<?php echo $product['size_depth_max']; ?>" step="<?php echo $product['size_step'] ?: 1; ?>" value="<?php echo $product['size_depth_min']; ?>">
                        </div>
                        <?php endif; ?>
                        <?php if ($product['size_height_min'] && $product['size_height_max']): ?>
                        <div class="size-field">
                            <label>Высота от <?php echo $product['size_height_min']; ?> до <?php echo $product['size_height_max']; ?></label>
                            <input type="number" id="sizeHeight" min="<?php echo $product['size_height_min']; ?>" max="<?php echo $product['size_height_max']; ?>" step="<?php echo $product['size_step'] ?: 1; ?>" value="<?php echo $product['size_height_min']; ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="size-note">
                        <i class="fas fa-info-circle"></i> 
                        Базовая цена указана за минимальный размер. При увеличении размера добавляется доплата.
                        <?php if ($product['price_per_square'] > 0): ?>
                            Доплата: <strong><?php echo number_format($product['price_per_square'], 0, '', ' '); ?> ₽/м²</strong>
                        <?php elseif ($product['price_per_size'] > 0): ?>
                            Доплата: <strong><?php echo number_format($product['price_per_size'], 0, '', ' '); ?> ₽/см</strong>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($has_variants): ?>
                <div class="options-section">
                    <?php if (count($materials) > 0): ?>
                    <div class="option-group">
                        <label><i class="fas fa-microphone-alt"></i> Материал:</label>
                        <select class="option-select" id="materialSelect">
                            <option value="">Выберите материал</option>
                            <?php foreach ($materials as $m): ?>
                                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <?php if (count($colors) > 0): ?>
                    <div class="option-group">
                        <label><i class="fas fa-palette"></i> Цвет:</label>
                        <select class="option-select" id="colorSelect">
                            <option value="">Выберите цвет</option>
                            <?php foreach ($colors as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="no-variants">
                        <i class="fas fa-exclamation-triangle"></i> 
                        К сожалению, данный товар временно отсутствует в наличии. Пожалуйста, свяжитесь с нами для уточнения.
                    </div>
                <?php endif; ?>

                <div class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                
                <?php if ($has_variants): ?>
                <button class="btn-add-to-cart" id="addToCartBtn" data-id="<?php echo $product['id']; ?>" disabled>
                    <i class="fas fa-shopping-cart"></i> Выберите материал и цвет
                </button>
                <?php else: ?>
                <button class="btn-add-to-cart" disabled style="background:#ccc; cursor:not-allowed;">
                    <i class="fas fa-times-circle"></i> Нет в наличии
                </button>
                <?php endif; ?>
                
                <input type="hidden" id="basePriceValue" value="<?php echo $product['price']; ?>">
                <input type="hidden" id="pricePerSize" value="<?php echo $product['price_per_size']; ?>">
                <input type="hidden" id="pricePerSquare" value="<?php echo $product['price_per_square']; ?>">
                <input type="hidden" id="minWidth" value="<?php echo $product['size_width_min']; ?>">
                <input type="hidden" id="minDepth" value="<?php echo $product['size_depth_min']; ?>">
                <input type="hidden" id="minHeight" value="<?php echo $product['size_height_min']; ?>">
            </div>
        </div>
    </div>
</section>

<?php if (count($similar_products) > 0): ?>
<section class="similar-section">
    <div class="container">
        <h2>Похожие товары</h2>
        <div class="similar-grid">
            <?php foreach ($similar_products as $similar): ?>
            <a href="product.php?id=<?php echo $similar['id']; ?>" class="similar-card">
                <img src="images/products/<?php echo $similar['image']; ?>" alt="<?php echo htmlspecialchars($similar['name']); ?>" onerror="this.src='images/products/default.jpg'">
                <h3><?php echo htmlspecialchars($similar['name']); ?></h3>
                <div class="similar-price"><?php echo number_format($similar['price'], 0, '', ' '); ?> ₽</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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

    const materialSelect = document.getElementById('materialSelect');
    const colorSelect = document.getElementById('colorSelect');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const sizeWidth = document.getElementById('sizeWidth');
    const sizeDepth = document.getElementById('sizeDepth');
    const sizeHeight = document.getElementById('sizeHeight');
    const basePriceSpan = document.getElementById('basePrice');

    const basePriceValue = parseFloat(document.getElementById('basePriceValue').value);
    const pricePerSize = parseFloat(document.getElementById('pricePerSize').value);
    const pricePerSquare = parseFloat(document.getElementById('pricePerSquare').value);

    let minWidth = sizeWidth ? parseFloat(sizeWidth.min) || 0 : 0;
    let minDepth = sizeDepth ? parseFloat(sizeDepth.min) || 0 : 0;
    let minHeight = sizeHeight ? parseFloat(sizeHeight.min) || 0 : 0;
    
    let baseArea = 0;
    if (minWidth > 0 && minDepth > 0) {
        baseArea = (minWidth * minDepth) / 10000;
    } else if (minWidth > 0 && minHeight > 0) {
        baseArea = (minWidth * minHeight) / 10000;
    } else if (minDepth > 0 && minHeight > 0) {
        baseArea = (minDepth * minHeight) / 10000;
    }
    
    let currentPrice = basePriceValue;
    
    function recalculatePrice() {
        let newPrice = basePriceValue;
        let addition = 0;
        
        let currentWidth = sizeWidth ? parseFloat(sizeWidth.value) || minWidth : 0;
        let currentDepth = sizeDepth ? parseFloat(sizeDepth.value) || minDepth : 0;
        let currentHeight = sizeHeight ? parseFloat(sizeHeight.value) || minHeight : 0;
        
        if (currentWidth < minWidth && minWidth > 0) currentWidth = minWidth;
        if (currentDepth < minDepth && minDepth > 0) currentDepth = minDepth;
        if (currentHeight < minHeight && minHeight > 0) currentHeight = minHeight;
        
        if (pricePerSquare > 0 && minWidth > 0 && minDepth > 0) {
            let currentArea = (currentWidth * currentDepth) / 10000;
            if (currentArea > baseArea) {
                let extraArea = currentArea - baseArea;
                addition = extraArea * pricePerSquare;
            }
        } else if (pricePerSize > 0) {
            let currentSize = 0;
            let minSize = 0;
            if (sizeWidth && sizeWidth.style.display !== 'none') {
                currentSize = currentWidth;
                minSize = minWidth;
            } else if (sizeHeight && sizeHeight.style.display !== 'none') {
                currentSize = currentHeight;
                minSize = minHeight;
            } else if (sizeDepth && sizeDepth.style.display !== 'none') {
                currentSize = currentDepth;
                minSize = minDepth;
            }
            if (currentSize > minSize && minSize > 0) {
                let extraSize = currentSize - minSize;
                addition = extraSize * pricePerSize;
            }
        }
        
        newPrice = basePriceValue + addition;
        currentPrice = Math.round(newPrice);
        basePriceSpan.textContent = currentPrice.toLocaleString('ru-RU') + ' ₽';
        return currentPrice;
    }
    
    function checkOptions() {
        let allSelected = true;
        if (materialSelect && materialSelect.options.length > 0 && !materialSelect.value) {
            allSelected = false;
        }
        if (colorSelect && colorSelect.options.length > 0 && !colorSelect.value) {
            allSelected = false;
        }
        if (allSelected) {
            addToCartBtn.disabled = false;
            addToCartBtn.classList.add('active');
            addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Добавить в корзину';
        } else {
            addToCartBtn.disabled = true;
            addToCartBtn.classList.remove('active');
            addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Выберите материал и цвет';
        }
    }
    
    if (sizeWidth) sizeWidth.addEventListener('input', recalculatePrice);
    if (sizeDepth) sizeDepth.addEventListener('input', recalculatePrice);
    if (sizeHeight) sizeHeight.addEventListener('input', recalculatePrice);
    if (materialSelect) materialSelect.addEventListener('change', checkOptions);
    if (colorSelect) colorSelect.addEventListener('change', checkOptions);
    
    addToCartBtn.addEventListener('click', async function() {
        if (this.disabled) return;
        
        const productId = this.getAttribute('data-id');
        const material = materialSelect ? materialSelect.value : '';
        const color = colorSelect ? colorSelect.value : '';
        const width = sizeWidth ? sizeWidth.value : '';
        const depth = sizeDepth ? sizeDepth.value : '';
        const height = sizeHeight ? sizeHeight.value : '';
        
        try {
            const response = await fetch('add-to-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}&material_id=${material}&color_id=${color}&quantity=1&size_width=${width}&size_depth=${depth}&size_height=${height}&size_price=${currentPrice}`
            });
            const result = await response.json();
            
            if (result.success) {
                const cartCounter = document.querySelector('.cart-count');
                if (cartCounter) cartCounter.textContent = result.cart_count;
                this.innerHTML = '<i class="fas fa-check"></i> Добавлено!';
                this.style.background = '#27ae60';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i> Добавить в корзину';
                    this.style.background = '#8B4513';
                }, 2000);
            } else if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                alert(result.error || 'Ошибка при добавлении');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка');
        }
    });
    
    recalculatePrice();
    checkOptions();
</script>
</body>
</html>