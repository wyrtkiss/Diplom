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


$stmt_categories = $pdo->query("SELECT id, name FROM categoriesD ORDER BY name");
$all_categories = $stmt_categories->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $site_name; ?> - Доставка и оплата</title>
    <meta name="description" content="Условия доставки и оплаты мебели ЯрМебель. Бесплатная доставка по Ярославлю. Оплата при получении.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        
        :root {
            --primary: #8B4513;
            --primary-dark: #6B3410;
            --primary-light: #A05A2C;
            --dark: #2C3E50;
            --text: #333;
            --text-light: #666;
            --bg-light: #f5f5f5;
            --bg-white: #fff;
            --border: #e0e0e0;
            --shadow: 0 5px 20px rgba(0,0,0,0.08);
            --shadow-hover: 0 10px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            line-height: 1.5;
            background-color: var(--bg-white);
            overflow-x: hidden;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

     
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .animate-left {
            animation: fadeInLeft 0.6s ease forwards;
        }

        .animate-right {
            animation: fadeInRight 0.6s ease forwards;
        }

        .animate-scale {
            animation: scaleIn 0.5s ease forwards;
        }

      
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
            background-repeat: no-repeat;
            padding: 60px 0;
            text-align: center;
            color: white;
        }

        .page-hero h1 {
            font-size: 42px;
            font-family: 'Playfair Display', serif;
            margin-bottom: 15px;
        }

        .page-hero p {
            font-size: 18px;
            opacity: 0.9;
        }

       
        .delivery-section {
            padding: 60px 0;
            background: var(--bg-white);
        }

        .delivery-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .info-card {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 35px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .info-card h2 {
            font-size: 28px;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-card h2 i {
            color: var(--primary);
            font-size: 32px;
        }

        .info-card h3 {
            font-size: 20px;
            color: var(--primary);
            margin: 20px 0 15px 0;
        }

        .info-card p {
            color: var(--text-light);
            line-height: 1.7;
            margin-bottom: 15px;
        }

        .info-list {
            list-style: none;
            margin-top: 20px;
        }

        .info-list li {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-list li i {
            color: var(--primary);
            margin-top: 3px;
            min-width: 18px;
        }

        .highlight {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 16px;
            margin-top: 25px;
            border-left: 4px solid var(--primary);
        }

        .highlight p {
            margin-bottom: 0;
        }

        .price-free {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        
        .map-zone {
            padding: 0 0 60px 0;
            background: var(--bg-white);
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .map-container iframe {
            width: 100%;
            height: 350px;
            border: none;
        }

 
        .newsletter {
            padding: 60px 0;
            background: linear-gradient(rgba(139, 69, 19, 0.7), rgba(139, 69, 19, 0.7)), url('images/1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
        }

        .newsletter-box {
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }

        .newsletter-box h3 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .newsletter-box p {
            opacity: 0.9;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 40px;
            outline: none;
        }

        .newsletter-form button {
            padding: 14px 30px;
            background: var(--dark);
            border: none;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .newsletter-form button:hover {
            background: #1a252f;
            transform: scale(1.02);
        }

    
        .footer {
            background: linear-gradient(rgba(44, 62, 80, 0.7), rgba(44, 62, 80, 0.7)), url('images/1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #f0f0f0;
            padding: 60px 0 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo span {
            font-size: 28px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            color: white;
        }

        .footer-logo span span {
            color: var(--primary);
        }

        .footer-col p {
            margin-top: 15px;
            line-height: 1.6;
            font-size: 14px;
        }

        .footer-col h4 {
            color: white;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .footer-col ul li {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer-col ul li a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .footer-contacts li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .footer-contacts i {
            width: 20px;
            color: var(--primary);
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 18px;
            color: white;
        }

        .footer-social a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 13px;
        }

        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #25D366;
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(37,211,102,0.4);
            transition: var(--transition);
            z-index: 999;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            background: #20b859;
        }

        .floating-btn i {
            font-size: 28px;
        }

      
        @media (max-width: 1024px) {
            .delivery-grid { grid-template-columns: 1fr; gap: 30px; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .header-top { flex-wrap: nowrap; }
            .header-search { display: none; }
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); z-index: 1000; }
            .nav-main ul { flex-direction: column; gap: 15px; align-items: center; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-social { justify-content: center; }
            .footer-contacts li { justify-content: center; }
            .page-hero h1 { font-size: 32px; }
            .newsletter-form { flex-direction: column; }
            .info-card { padding: 20px; }
            .info-card h2 { font-size: 24px; }
        }

        @media (max-width: 480px) {
            .floating-btn { width: 45px; height: 45px; bottom: 20px; right: 20px; }
            .floating-btn i { font-size: 22px; }
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
                <a href="profile.php" class="action-icon">
                    <i class="far fa-user"></i>
                </a>
                <a href="cart.php" class="action-icon cart-link">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count">0</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="action-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <nav class="nav-main" id="mainNav">
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="about.php">О компании</a></li>
                <li><a href="delivery.php" class="active">Доставка и оплата</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
    </div>
</header>


<section class="page-hero">
    <div class="container">
        <h1 class="animate-up">Доставка и оплата</h1>
        <p class="animate-up" style="animation-delay: 0.1s;">Удобные и прозрачные условия для наших клиентов</p>
    </div>
</section>


<section class="delivery-section">
    <div class="container">
        <div class="delivery-grid">
            
            
            <div class="info-card animate-left">
                <h2><i class="fas fa-truck"></i> Доставка</h2>
                
                
                <h3>📍 Зоны доставки</h3>
                <ul class="info-list">
                    <li><i class="fas fa-check-circle"></i> <strong>Бесплатно:</strong> в пределах города Ярославля</li>
                    <li><i class="fas fa-check-circle"></i> <strong>По области и в другие регионы:</strong> отправка транспортными компаниями</li>
                </ul>
                
                <h3>⏱ Сроки доставки</h3>
                <ul class="info-list">
                    <li><i class="fas fa-clock"></i> По Ярославлю — 1-3 дня</li>
                    <li><i class="fas fa-clock"></i> По области — 2-5 дней</li>
                    <li><i class="fas fa-clock"></i> В другие города — от 5 до 14 дней</li>
                </ul>
                
                <h3>📦 Что нужно знать</h3>
                <ul class="info-list">
                    <li><i class="fas fa-info-circle"></i> Доставка осуществляется с 10:00 до 20:00</li>
                    <li><i class="fas fa-info-circle"></i> За день до доставки курьер свяжется с вами</li>
                    <li><i class="fas fa-info-circle"></i> Сборка мебели уже входит в стоимость</li>
                </ul>
            </div>
            
      
            <div class="info-card animate-right">
                <h2><i class="fas fa-credit-card"></i> Оплата</h2>
                
                <div class="highlight">
                    <p>💵 <strong>Оплата при получении</strong> — самый удобный и безопасный способ!</p>
                    <p style="margin-top: 10px;">Выплачиваете заказ только после того, как осмотрите его и убедитесь в качестве.</p>
                </div>
                
                <h3>💳 Способы оплаты</h3>
                <ul class="info-list">
                    <li><i class="fas fa-money-bill-wave"></i> <strong>Наличными курьеру</strong> — при получении заказа</li>
                    <li><i class="fas fa-credit-card"></i> <strong>Банковской картой</strong> — через терминал курьера</li>
                    <li><i class="fas fa-building-columns"></i> <strong>Безналичный расчет</strong> — для юридических лиц по счету</li>
                </ul>
                
                <h3>🔒 Почему оплата при получении выгодна?</h3>
                <ul class="info-list">
                    <li><i class="fas fa-eye"></i> Вы видите товар перед оплатой</li>
                    <li><i class="fas fa-check"></i> Проверяете комплектацию и качество</li>
                    <li><i class="fas fa-shield-alt"></i> Никаких рисков потерять деньги</li>
                    <li><i class="fas fa-smile"></i> Полная гарантия возврата при несоответствии</li>
                </ul>
                
                <div class="highlight" style="margin-top: 25px;">
                    <p><strong>ℹ️ Важно:</strong> При заказе мы предлагаем <strong>бесплатную доставку</strong> по Ярославлю. Предоплата не требуется!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-zone">
    <div class="container">
        <div class="map-container animate-scale">
            <iframe 
                src="https://yandex.ru/map-widget/v1/?ll=39.893514,57.626105&z=12&pt=39.893514,57.626105&mode=search&sll=39.893514,57.626105&text=Ярославль"
                allowfullscreen="true">
            </iframe>
        </div>
        <p style="text-align: center; margin-top: 15px; font-size: 13px; color: var(--text-light);">
            <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i> Основная зона бесплатной доставки — в пределах города Ярославля
        </p>
    </div>
</section>


<section class="newsletter">
    <div class="container">
        <div class="newsletter-box animate-scale">
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
                <div class="footer-logo">
                    <span>Яр<span>Мебель</span></span>
                </div>
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
            if (mainNav.style.display === 'block') {
                mainNav.style.display = 'none';
            } else {
                mainNav.style.display = 'block';
            }
        });
    }
</script>

</body>
</html>