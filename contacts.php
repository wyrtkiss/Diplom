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
$stmt_reviews = $pdo->query("
    SELECT 
        r.id, 
        r.rating, 
        r.comment, 
        DATE_FORMAT(r.created_at, '%d.%m.%Y') as created_date,
        u.first_name, 
        u.last_name
    FROM reviewsD r
    JOIN usersD u ON r.user_id = u.id
    WHERE r.is_moderated = 1
    ORDER BY r.created_at DESC
");
$reviews = $stmt_reviews->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $site_name; ?> - Контакты и отзывы</title>
    <meta name="description" content="Контакты магазина мебели ЯрМебель. Адрес, телефон, email, схема проезда. Отзывы наших клиентов.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>

        :root {
            --primary: #8B4513;
            --primary-dark: #6B3410;
            --primary-light: #A05A2C;
            --accent: #F0E68C;
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
                transform: scale(0.9);
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
    position: relative;
}

        .page-hero h1 {
            font-size: 38px;
            font-family: 'Playfair Display', serif;
            margin-bottom: 10px;
        }

        .page-hero p {
            font-size: 16px;
            opacity: 0.9;
        }

        
        .contacts-grid {
            padding: 60px 0 40px;
            background: var(--bg-white);
        }

        .contacts-grid .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .contact-card {
            background: var(--bg-white);
            border-radius: 16px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .contact-icon i {
            font-size: 26px;
            color: white;
        }

        .contact-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .contact-card p {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.5;
        }

        .contact-card a {
            color: var(--primary);
        }

        .contact-card a:hover {
            text-decoration: underline;
        }

        .work-hours-block {
            margin-top: 30px;
            text-align: center;
        }

        .work-hours-inner {
            display: inline-block;
            background: var(--bg-light);
            padding: 15px 30px;
            border-radius: 50px;
        }

        .work-hours-inner p {
            display: inline-block;
            margin: 0 15px;
            font-size: 14px;
            color: var(--text-light);
        }

        .work-hours-inner i {
            color: var(--primary);
            margin-right: 5px;
        }

       
        .reviews-section {
            padding: 0 0 80px 0;
            background: var(--bg-light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 32px;
            font-family: 'Playfair Display', serif;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .section-header p {
            color: var(--text-light);
        }

       
        .reviews-carousel {
            position: relative;
            padding: 0 40px;
        }

        .reviews-container {
            overflow: hidden;
        }

        .reviews-wrapper {
            display: flex;
            transition: transform 0.5s ease;
            gap: 25px;
        }

        .review-card {
            flex: 0 0 calc(33.333% - 17px);
            background: var(--bg-white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .review-rating {
            margin-bottom: 12px;
        }

        .review-rating i {
            color: #f5a623;
            font-size: 14px;
            margin-right: 2px;
        }

        .review-rating i.far {
            color: #ddd;
        }

        .review-text {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text);
            margin-bottom: 15px;
            min-height: 80px;
        }

        .review-author {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            border-top: 1px solid var(--border);
            padding-top: 12px;
        }

        .review-author-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        .review-author-name i {
            color: var(--primary);
            margin-right: 5px;
        }

        .review-date {
            font-size: 11px;
            color: var(--text-light);
        }

        
        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .carousel-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        .carousel-btn:disabled {
            background: var(--border);
            cursor: not-allowed;
            transform: none;
        }

        .no-reviews {
            text-align: center;
            padding: 50px;
            background: var(--bg-white);
            border-radius: 16px;
        }

        .no-reviews i {
            font-size: 48px;
            color: var(--primary);
            opacity: 0.4;
            margin-bottom: 15px;
        }

   
        .map-section {
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
            font-size: 26px;
            margin-bottom: 8px;
        }

        .newsletter-box p {
            opacity: 0.9;
            font-size: 14px;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 40px;
            outline: none;
        }

        .newsletter-form button {
            padding: 12px 25px;
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
            margin-bottom: 30px;
        }

        .footer-logo span {
            font-size: 26px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
            color: white;
        }

        .footer-logo span span {
            color: var(--primary);
        }

        .footer-col p {
            margin-top: 12px;
            line-height: 1.6;
            font-size: 13px;
        }

        .footer-col h4 {
            color: white;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer-col ul li {
            margin-bottom: 8px;
            font-size: 13px;
        }

        .footer-col ul li a:hover {
            color: var(--primary);
        }

        .footer-contacts li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .footer-contacts i {
            width: 18px;
            color: var(--primary);
        }

        .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .footer-social a {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 16px;
            color: white;
        }

        .footer-social a:hover {
            background: var(--primary);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 12px;
        }

      
        .floating-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #25D366;
            color: white;
            width: 50px;
            height: 50px;
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
        }

      
        @media (max-width: 1024px) {
            .contacts-grid .grid { grid-template-columns: repeat(2, 1fr); }
            .review-card { flex: 0 0 calc(50% - 12.5px); }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .header-top { flex-wrap: nowrap; }
            .header-search { display: none; }
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); z-index: 1000; }
            .nav-main ul { flex-direction: column; gap: 15px; align-items: center; }
            .contacts-grid .grid { grid-template-columns: 1fr; }
            .review-card { flex: 0 0 100%; }
            .reviews-carousel { padding: 0 20px; }
            .work-hours-inner p { display: block; margin: 5px 0; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-social { justify-content: center; }
            .footer-contacts li { justify-content: center; }
            .page-hero h1 { font-size: 28px; }
            .newsletter-form { flex-direction: column; }
        }

        @media (max-width: 480px) {
            .floating-btn { width: 42px; height: 42px; bottom: 15px; right: 15px; }
        }
    </style>
</head>
<body>
</a>

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
                <li><a href="delivery.php">Доставка и оплата</a></li>
                <li><a href="contacts.php" class="active">Контакты</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="page-hero">
    <div class="container">
        <h1 class="animate-up">Контакты</h1>
        <p class="animate-up" style="animation-delay: 0.1s;">Свяжитесь с нами удобным для вас способом</p>
    </div>
</section>

<section class="contacts-grid">
    <div class="container">
        <div class="grid">
            <div class="contact-card animate-up">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Адрес</h3>
                <p>г. Ярославль, ул. Промышленная, д. 12</p>
            </div>
            <div class="contact-card animate-up" style="animation-delay: 0.1s;">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3>Телефоны</h3>
                <p><a href="tel:+74852212345">+7 (4852) 12-34-56</a> — продажи</p>
                <p><a href="tel:+74852212347">+7 (4852) 12-34-57</a> — производство</p>
            </div>
            <div class="contact-card animate-up" style="animation-delay: 0.2s;">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p><a href="mailto:info@yarmeble.ru">info@yarmeble.ru</a></p>
            </div>
            <div class="contact-card animate-up" style="animation-delay: 0.3s;">
                <div class="contact-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3>Мессенджеры</h3>
                <p><a href="https://wa.me/74852212345">WhatsApp: +7 (4852) 12-34-56</a></p>
                <p>ВКонтакте: @yarmeble</p>
            </div>
        </div>
        <div class="work-hours-block animate-up" style="animation-delay: 0.4s;">
            <div class="work-hours-inner">
                <p><i class="fas fa-clock"></i> Производство: Пн-Пт 9:00-18:00</p>
                <p><i class="fas fa-truck"></i> Доставка: Пн-Сб 10:00-20:00</p>
                <p><i class="fas fa-headset"></i> Отдел продаж: Пн-Вс 9:00-21:00</p>
            </div>
        </div>
    </div>
</section>

<section class="reviews-section">
    <div class="container">
        <div class="section-header animate-up">
            <h2><i class="fas fa-star" style="color: var(--primary);"></i> Отзывы наших клиентов</h2>
            <p>Что говорят о нас покупатели</p>
        </div>

        <?php if (count($reviews) > 0): ?>
        <div class="reviews-carousel">
            <div class="reviews-container">
                <div class="reviews-wrapper" id="reviewsWrapper">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $review['rating']): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="review-text">
                            <?php echo htmlspecialchars(mb_substr($review['comment'], 0, 150)) . (mb_strlen($review['comment']) > 150 ? '...' : ''); ?>
                        </div>
                        <div class="review-author">
                            <span class="review-author-name">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($review['first_name']); ?>
                            </span>
                            <span class="review-date">
                                <i class="far fa-calendar-alt"></i> <?php echo $review['created_date']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (count($reviews) > 3): ?>
            <div class="carousel-nav">
                <button class="carousel-btn" id="prevReviewBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn" id="nextReviewBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="no-reviews">
            <i class="fas fa-comment-dots"></i>
            <p>Пока нет отзывов</p>
            <p style="font-size: 13px; margin-top: 8px;">Оставьте отзыв в личном кабинете после покупки</p>
        </div>
        <?php endif; ?>
    </div>
</section>


<section class="map-section">
    <div class="container">
        <div class="map-container animate-scale">
            <iframe 
                src="https://yandex.ru/map-widget/v1/?ll=39.893514,57.626105&z=16&pt=39.893514,57.626105&mode=search&sll=39.893514,57.626105&text=Ярославль%20Промышленная%20улица%2012"
                allowfullscreen="true">
            </iframe>
        </div>
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

    const wrapper = document.getElementById('reviewsWrapper');
    const prevBtn = document.getElementById('prevReviewBtn');
    const nextBtn = document.getElementById('nextReviewBtn');
    
    if (wrapper && prevBtn && nextBtn) {
        const cards = wrapper.querySelectorAll('.review-card');
        const totalCards = cards.length;
        const visibleCards = 3;
        let currentIndex = 0;
        const maxIndex = Math.ceil(totalCards / visibleCards) - 1;
        
        function updateCarousel() {
            const offset = currentIndex * (100 / visibleCards);
            wrapper.style.transform = `translateX(-${offset}%)`;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === maxIndex;
        }
        
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateCarousel();
            }
        });
        
        updateCarousel();
    }
</script>

</body>
</html>