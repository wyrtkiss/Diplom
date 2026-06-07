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

$search_results = [];
$search_query = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            p.id, 
            p.name, 
            p.description, 
            p.price, 
            p.image
        FROM productsD p
        WHERE p.name LIKE :search OR p.description LIKE :search
        LIMIT 12
    ");
    $stmt->execute(['search' => "%$search_query%"]);
    $search_results = $stmt->fetchAll();
}

$stmt_categories = $pdo->query("SELECT id, name FROM categoriesD ORDER BY id LIMIT 6");
$categories = $stmt_categories->fetchAll();

$stmt_products = $pdo->query("
    SELECT DISTINCT 
        p.id, 
        p.name, 
        p.description, 
        p.price, 
        p.image
    FROM productsD p
    ORDER BY p.id
    LIMIT 8
");
$popular_products = $stmt_products->fetchAll();

$all_products = [];
$stmt_all = $pdo->query("SELECT id, name, description, price, image, category_id FROM productsD");
$all_products = $stmt_all->fetchAll();

$new_products = [];
$stmt_new = $pdo->query("SELECT id, name, description, price, image FROM productsD ORDER BY id DESC LIMIT 6");
$new_products = $stmt_new->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo $site_name; ?> - лучшее решение для вашей квартиры</title>
    <meta name="description" content="Интернет-магазин мебели ЯрМебель. Умный подбор мебели по размерам комнаты и стилю.">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: var(--text); background-color: var(--bg-white); overflow-x: hidden; }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; height: auto; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes blink { 0%, 100% { opacity: 0.3; } 50% { opacity: 1; } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        .animate-up { animation: fadeInUp 0.6s ease forwards; }
        .animate-left { animation: fadeInLeft 0.6s ease forwards; }
        .animate-right { animation: fadeInRight 0.6s ease forwards; }
        .animate-scale { animation: scaleIn 0.5s ease forwards; }

        .header { background: var(--bg-white); box-shadow: var(--shadow); position: sticky; top: 0; z-index: 1000; }
        .header-top { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; gap: 20px; flex-wrap: wrap; }
        .logo-text { font-size: 28px; font-weight: 700; font-family: 'Playfair Display', serif; color: var(--dark); }
        .logo-accent { color: var(--primary); }
        .header-search { flex: 1; max-width: 400px; }
        .search-form { display: flex; border: 1px solid var(--border); border-radius: 30px; overflow: hidden; transition: var(--transition); }
        .search-form:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }
        .search-input { flex: 1; padding: 12px 20px; border: none; outline: none; }
        .search-btn { background: var(--primary); border: none; padding: 0 20px; color: white; cursor: pointer; transition: var(--transition); }
        .search-btn:hover { background: var(--primary-dark); }
        .header-actions { display: flex; align-items: center; gap: 25px; }
        .action-icon { position: relative; font-size: 22px; color: var(--dark); transition: var(--transition); }
        .action-icon:hover { color: var(--primary); transform: translateY(-2px); }
        .cart-count { position: absolute; top: -8px; right: -12px; background: var(--primary); color: white; font-size: 11px; font-weight: 600; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .nav-main { border-top: 1px solid var(--border); padding: 12px 0; }
        .nav-main ul { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; }
        .nav-main a { font-size: 15px; font-weight: 500; transition: var(--transition); }
        .nav-main a:hover, .nav-main a.active { color: var(--primary); }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--dark); }

        .hero { background: linear-gradient(rgba(44,62,80,0.6), rgba(44,62,80,0.6)), url('images/1.jpg'); background-size: cover; background-position: center; min-height: 550px; display: flex; align-items: center; text-align: center; position: relative; }
        .hero-content { color: white; max-width: 800px; margin: 0 auto; text-align: center; position: relative; z-index: 2; animation: fadeInUp 0.8s ease; }
        .hero-content h1 { font-size: 48px; font-family: 'Playfair Display', serif; margin-bottom: 20px; line-height: 1.2; }
        .hero-content p { font-size: 18px; margin-bottom: 30px; opacity: 0.9; }
        .hero-buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }

        .btn { display: inline-flex; align-items: center; gap: 10px; padding: 14px 30px; border-radius: 40px; font-weight: 600; transition: var(--transition); cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139,69,19,0.3); }
        .btn-outline { background: transparent; border: 2px solid white; color: white; }
        .btn-outline:hover { background: white; color: var(--primary); transform: translateY(-2px); }
        .btn-secondary { background: transparent; border: 2px solid var(--primary); color: var(--primary); }
        .btn-secondary:hover { background: var(--primary); color: white; transform: translateY(-2px); }

        .advantages { padding: 60px 0; background: var(--bg-white); }
        .advantages-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; text-align: center; }
        .advantage-item { padding: 30px 20px; background: var(--bg-light); border-radius: 16px; transition: var(--transition); }
        .advantage-item:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .advantage-item i { font-size: 45px; color: var(--primary); margin-bottom: 15px; }
        .advantage-item h3 { font-size: 18px; margin-bottom: 8px; color: var(--dark); }
        .advantage-item p { font-size: 14px; color: var(--text-light); }

        .categories { padding: 80px 0; background: var(--bg-light); }
        .section-header { text-align: center; margin-bottom: 50px; }
        .section-header h2 { font-size: 36px; font-family: 'Playfair Display', serif; color: var(--dark); margin-bottom: 10px; }
        .section-header p { color: var(--text-light); }
        .categories-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px; }
        .category-card { text-align: center; transition: var(--transition); background: var(--bg-white); padding: 20px 15px; border-radius: 16px; box-shadow: var(--shadow); }
        .category-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .category-image { border-radius: 16px; overflow: hidden; margin-bottom: 15px; aspect-ratio: 1 / 1; background: var(--bg-light); }
        .category-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .category-card:hover .category-image img { transform: scale(1.05); }
        .category-card h3 { font-size: 16px; color: var(--dark); font-weight: 600; }

        .popular-products { padding: 80px 0; background: var(--bg-white); }
        .products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; margin-bottom: 40px; }
        .product-card { background: var(--bg-white); border-radius: 16px; overflow: hidden; transition: var(--transition); box-shadow: var(--shadow); position: relative; border: 1px solid var(--border); }
        .product-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .product-badge { position: absolute; top: 15px; left: 15px; background: var(--primary); color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 10; }
        .product-image { display: block; aspect-ratio: 1 / 1; overflow: hidden; background: var(--bg-light); }
        .product-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .product-card:hover .product-image img { transform: scale(1.05); }
        .product-info { padding: 20px; }
        .product-info h3 { font-size: 16px; margin-bottom: 10px; }
        .product-info h3 a { color: var(--dark); }
        .product-info h3 a:hover { color: var(--primary); }
        .product-price { margin-bottom: 15px; }
        .current-price { font-size: 22px; font-weight: 700; color: var(--primary); }
        .btn-details { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 30px; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .btn-details:hover { background: var(--primary-dark); transform: scale(1.02); }
        .text-center { text-align: center; }

        .search-results { padding: 60px 0; background: var(--bg-light); }
        .product-description { font-size: 13px; color: var(--text-light); margin-bottom: 10px; line-height: 1.4; }
        .search-empty { text-align: center; padding: 60px 20px; background: white; border-radius: 20px; }

        .about-company { padding: 80px 0; background: linear-gradient(135deg, var(--bg-light) 0%, #fff 100%); }
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }
        .about-content h2 { font-size: 36px; font-family: 'Playfair Display', serif; margin-bottom: 20px; color: var(--dark); }
        .accent { color: var(--primary); }
        .about-text { color: var(--text-light); line-height: 1.8; margin-bottom: 30px; }
        .about-list { margin: 25px 0; }
        .about-list li { margin-bottom: 15px; display: flex; align-items: center; gap: 12px; font-size: 16px; }
        .about-list i { width: 30px; height: 30px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .about-image { border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-hover); }
        .about-image img { width: 100%; height: auto; transition: var(--transition); }

        .newsletter { padding: 60px 0; background: linear-gradient(rgba(139,69,19,0.7), rgba(139,69,19,0.7)), url('images/1.jpg'); background-size: cover; background-position: center; background-attachment: fixed; color: white; }
        .newsletter-box { text-align: center; max-width: 500px; margin: 0 auto; }
        .newsletter-box h3 { font-size: 28px; margin-bottom: 10px; }
        .newsletter-form { display: flex; gap: 10px; margin-top: 25px; }
        .newsletter-form input { flex: 1; padding: 14px 20px; border: none; border-radius: 40px; outline: none; }
        .newsletter-form button { padding: 14px 30px; background: var(--dark); border: none; border-radius: 40px; color: white; font-weight: 600; cursor: pointer; transition: var(--transition); }

        .footer { background: linear-gradient(rgba(44,62,80,0.7), rgba(44,62,80,0.7)), url('images/1.jpg'); background-size: cover; background-position: center; background-attachment: fixed; color: #f0f0f0; padding: 60px 0 20px; }
        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; margin-bottom: 40px; }
        .footer-logo span { font-size: 28px; font-weight: 700; font-family: 'Playfair Display', serif; color: white; }
        .footer-logo span span { color: var(--primary); }
        .footer-col p { margin-top: 15px; line-height: 1.6; }
        .footer-col h4 { color: white; margin-bottom: 20px; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a:hover { color: var(--primary); padding-left: 5px; }
        .footer-contacts li { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .footer-contacts i { width: 20px; color: var(--primary); }
        .footer-social { display: flex; gap: 15px; margin-top: 20px; }
        .footer-social a { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition); font-size: 18px; color: white; }
        .footer-social a:hover { background: var(--primary); color: white; transform: translateY(-3px); }
        .footer-bottom { text-align: center; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 14px; }

        .chat-bot-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: linear-gradient(135deg, #FFB347 0%, #FF8C42 100%);
            color: white; width: 65px; height: 65px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 1001; border: none; font-size: 36px;
            transition: all 0.3s ease; box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            animation: bounce 2s infinite;
        }
        .chat-bot-btn:hover { transform: scale(1.1); background: #FF8C42; }

        .chat-modal {
            display: none; position: fixed; bottom: 110px; right: 30px;
            z-index: 1002; width: 450px; height: 650px;
            background: white; border-radius: 25px; box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            flex-direction: column; overflow: hidden; animation: slideUp 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .chat-header {
            background: linear-gradient(135deg, #FFB347 0%, #FF8C42 100%);
            color: white; padding: 18px 20px; display: flex;
            justify-content: space-between; align-items: center; flex-shrink: 0;
        }
        .chat-header-info { display: flex; align-items: center; gap: 12px; }
        .chat-avatar { font-size: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
        .chat-header-info h3 { font-size: 16px; margin: 0; font-weight: 600; }
        .chat-header-info p { font-size: 11px; opacity: 0.85; margin: 3px 0 0; }
        .chat-close-btn { font-size: 28px; cursor: pointer; transition: 0.3s; line-height: 1; }
        .chat-close-btn:hover { opacity: 0.7; }

        .chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 120px 18px 18px 18px; 
    background: #f8f6f4;
    display: flex;
    flex-direction: column;
    gap: 12px;
}   .chat-body::-webkit-scrollbar { width: 5px; }
        .chat-body::-webkit-scrollbar-track { background: #e8e0d8; border-radius: 10px; }
        .chat-body::-webkit-scrollbar-thumb { background: #FFB347; border-radius: 10px; }

        .bot-message, .user-message { display: flex; gap: 10px; animation: fadeIn 0.3s ease; }
        .user-message { justify-content: flex-end; }
        .message-avatar { width: 32px; height: 32px; background: #e8e0d8; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .message-avatar i { color: #FF8C42; font-size: 18px; }
        .user-message .message-avatar { background: #FFB347; }
        .user-message .message-avatar i { color: white; }
        .message-bubble { max-width: 80%; padding: 10px 14px; border-radius: 18px; font-size: 13px; line-height: 1.45; }
        .bot-message .message-bubble { background: white; color: #333; border-top-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
        .user-message .message-bubble { background: #FFB347; color: white; border-top-right-radius: 4px; }

        .suggestions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .suggestion-btn { background: #f0e8e0; border: none; padding: 6px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; transition: 0.2s; color: #8B4513; }
        .suggestion-btn:hover { background: #FFB347; color: white; }

        .product-list { margin-top: 10px; display: flex; flex-direction: column; gap: 10px; }
        .chat-product-card { display: flex; align-items: center; gap: 12px; padding: 10px; background: #fff8f0; border-radius: 12px; text-decoration: none; transition: 0.2s; border: 1px solid #ffe0c0; }
        .chat-product-card:hover { transform: translateX(5px); border-color: #FFB347; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .chat-product-card img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .chat-product-info { flex: 1; }
        .chat-product-name { font-size: 13px; font-weight: 600; color: #333; }
        .chat-product-price { font-size: 12px; color: #FF8C42; font-weight: 500; }
        .chat-product-size { font-size: 10px; color: #888; margin-top: 2px; }

        .delivery-info {
            background: #fff8f0; border-radius: 12px; padding: 12px; margin-top: 8px;
            border-left: 3px solid #FFB347;
        }
        .delivery-info h4 { color: #8B4513; margin-bottom: 8px; font-size: 14px; }
        .delivery-info p { font-size: 12px; margin: 5px 0; }

        .chat-footer { padding: 15px; background: white; border-top: 1px solid #eee; display: flex; gap: 10px; flex-shrink: 0; }
        .chat-footer input { flex: 1; padding: 12px 16px; border: 1px solid #e0d8d0; border-radius: 30px; outline: none; font-size: 14px; font-family: 'Inter', sans-serif; }
        .chat-footer input:focus { border-color: #FFB347; box-shadow: 0 0 0 2px rgba(255,179,71,0.2); }
        .chat-footer button { width: 44px; height: 44px; background: #FFB347; color: white; border: none; border-radius: 50%; cursor: pointer; transition: 0.3s; font-size: 18px; }
        .chat-footer button:hover { background: #FF8C42; transform: scale(1.05); }

        .typing-indicator { display: flex; gap: 5px; padding: 8px 12px; }
        .typing-indicator span { width: 8px; height: 8px; background: #FFB347; border-radius: 50%; animation: blink 1.4s infinite; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

        @media (max-width: 768px) {
            .header-top { flex-wrap: nowrap; }
            .header-search { display: none; }
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); z-index: 1000; }
            .nav-main ul { flex-direction: column; gap: 15px; align-items: center; }
            .advantages-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .categories-grid { grid-template-columns: repeat(2, 1fr); }
            .products-grid { grid-template-columns: repeat(2, 1fr); }
            .about-grid { grid-template-columns: 1fr; text-align: center; }
            .about-list li { justify-content: center; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .footer-social { justify-content: center; }
            .hero-content h1 { font-size: 32px; }
            .newsletter-form { flex-direction: column; }
            .chat-modal { width: 340px; right: 15px; bottom: 90px; height: 550px; }
            .chat-bot-btn { width: 55px; height: 55px; bottom: 20px; right: 20px; font-size: 30px; }
        }
        @media (max-width: 480px) {
            .products-grid { grid-template-columns: 1fr; }
            .hero-buttons { flex-direction: column; align-items: stretch; }
            .chat-modal { width: calc(100% - 30px); right: 15px; left: 15px; bottom: 90px; height: 550px; }
        }
    </style>
</head>
<body>

<button class="chat-bot-btn" id="openChatBtn">🧸</button>

<div id="chatModal" class="chat-modal">
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-avatar">🪑</div>
            <div>
                <h3>Ярик — консультант</h3>
                <p>✨ помогу подобрать мебель по параметрам</p>
            </div>
        </div>
        <span class="chat-close-btn" id="closeChatBtn">&times;</span>
    </div>
    <div class="chat-body" id="chatBody"></div>
    <div class="chat-footer">
        <input type="text" id="userInput" placeholder="Напишите сообщение..." autocomplete="off">
        <button id="sendBtn"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<!-- ШАПКА -->
<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo"><a href="index.php"><span class="logo-text">Яр<span class="logo-accent">Мебель</span></span></a></div>
            <div class="header-search">
                <form action="index.php" method="get" class="search-form">
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
                <li><a href="index.php" class="active">Главная</a></li>
                <li><a href="catalog.php">Каталог</a></li>
                <li><a href="about.php">О компании</a></li>
                <li><a href="delivery.php">Доставка и оплата</a></li>
                <li><a href="contacts.php">Контакты</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="animate-up">Мебель от производителя в Ярославле</h1>
                <p class="animate-up" style="animation-delay: 0.1s;">Качество, проверенное временем. Современный дизайн. Доступные цены.</p>
                <div class="hero-buttons animate-up" style="animation-delay: 0.2s;">
                    <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
                    <a href="about.php" class="btn btn-outline">О компании</a>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($search_query)): ?>
    <section class="search-results">
        <div class="container">
            <div class="section-header"><h2>Результаты поиска: «<?php echo htmlspecialchars($search_query); ?>»</h2><p>Найдено: <?php echo count($search_results); ?></p></div>
            <?php if (count($search_results) > 0): ?>
            <div class="products-grid">
                <?php foreach($search_results as $product): ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="product-image"><img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/products/default.jpg'"></a>
                    <div class="product-info">
                        <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        <p class="product-description"><?php echo mb_substr(htmlspecialchars($product['description'] ?? ''), 0, 100); ?>...</p>
                        <div class="product-price"><span class="current-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span></div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-details"><i class="fas fa-info-circle"></i> Подробнее</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="search-empty"><i class="fas fa-search"></i><h3>Ничего не найдено</h3><a href="index.php" class="btn btn-primary">Вернуться на главную</a></div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (empty($search_query)): ?>
    <section class="advantages">
        <div class="container">
            <div class="advantages-grid">
                <div class="advantage-item animate-up"><i class="fas fa-truck"></i><h3>Бесплатная доставка</h3><p>По Ярославлю</p></div>
                <div class="advantage-item animate-up" style="animation-delay:0.1s"><i class="fas fa-calendar-alt"></i><h3>Собственное производство</h3><p>Контроль качества</p></div>
                <div class="advantage-item animate-up" style="animation-delay:0.2s"><i class="fas fa-shield-alt"></i><h3>Гарантия 5 лет</h3><p>На всю корпусную мебель</p></div>
                <div class="advantage-item animate-up" style="animation-delay:0.3s"><i class="fas fa-ruble-sign"></i><h3>Цены от производителя</h3><p>Без наценок</p></div>
            </div>
        </div>
    </section>

    <section class="categories">
        <div class="container">
            <div class="section-header"><h2>Категории товаров</h2><p>Выберите категорию</p></div>
            <div class="categories-grid">
                <?php $category_images = [1 => 'sofa.jpg', 2 => 'chair.jpg', 3 => 'wardrobe.jpg', 4 => 'bed.jpeg', 5 => 'table.jpg', 6 => 'stool.jpg']; $i = 0; foreach($categories as $cat): ?>
                <a href="catalog.php?category=<?php echo $cat['id']; ?>" class="category-card animate-scale" style="animation-delay: <?php echo $i * 0.05; ?>s">
                    <div class="category-image"><img src="images/categories/<?php echo $category_images[$cat['id']] ?? 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" onerror="this.src='images/categories/default.jpg'"></div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                </a>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </section>

    <section class="popular-products">
        <div class="container">
            <div class="section-header"><h2>Популярные товары</h2><p>Лучшие продажи</p></div>
            <div class="products-grid">
                <?php $i = 0; foreach($popular_products as $product): ?>
                <div class="product-card animate-scale" style="animation-delay: <?php echo $i * 0.05; ?>s">
                    <div class="product-badge">⭐ Хит продаж</div>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="product-image"><img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/products/default.jpg'"></a>
                    <div class="product-info">
                        <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        <div class="product-price"><span class="current-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span></div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-details"><i class="fas fa-info-circle"></i> Подробнее</a>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
            <div class="text-center"><a href="catalog.php" class="btn btn-secondary">Смотреть весь каталог →</a></div>
        </div>
    </section>

    <section class="about-company">
        <div class="container">
            <div class="about-grid">
                <div class="about-content animate-left">
                    <h2>ЯрМебель — <span class="accent">мебель для вашего уюта</span></h2>
                    <p class="about-text">Производим мебель с 2018 года в Ярославле. Каждое изделие проходит строгий контроль качества.</p>
                    <ul class="about-list"><li><i class="fas fa-check"></i> Экологичные материалы</li><li><i class="fas fa-check"></i> Современное оборудование</li><li><i class="fas fa-check"></i> Индивидуальный подход</li><li><i class="fas fa-check"></i> Собственная служба доставки</li></ul>
                    <a href="about.php" class="btn btn-primary">Подробнее о компании →</a>
                </div>
                <div class="about-image animate-right"><img src="images/2.jpg" alt="Наше производство" onerror="this.src='images/about-default.jpg'"></div>
            </div>
        </div>
    </section>

    <section class="newsletter">
        <div class="container">
            <div class="newsletter-box animate-scale">
                <h3>Будьте в курсе акций</h3>
                <p>Подпишитесь на рассылку</p>
                <form class="newsletter-form" method="post"><input type="email" name="email" placeholder="Ваш e-mail" required><button type="submit">Подписаться <i class="fas fa-paper-plane"></i></button></form>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><div class="footer-logo"><span>Яр<span>Мебель</span></span></div><p>Качественная мебель от производителя</p><div class="footer-social"><a href="#"><i class="fab fa-vk"></i></a><a href="#"><i class="fab fa-whatsapp"></i></a></div></div>
            <div class="footer-col"><h4>Каталог</h4><ul><?php $stmt = $pdo->query("SELECT id, name FROM categoriesD ORDER BY name LIMIT 6"); while($cat = $stmt->fetch()): ?><li><a href="catalog.php?category=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li><?php endwhile; ?></ul></div>
            <div class="footer-col"><h4>Покупателям</h4><ul><li><a href="delivery.php">Доставка и оплата</a></li><li><a href="delivery.php">Возврат и обмен</a></li><li><a href="delivery.php">Гарантия 5 лет</a></li><li><a href="delivery.php">Сборка мебели</a></li></ul></div>
            <div class="footer-col"><h4>Контакты</h4><ul class="footer-contacts"><li><i class="fas fa-phone"></i> <a href="tel:+74852212345">+7 (4852) 12-34-56</a></li><li><i class="fas fa-envelope"></i> <a href="mailto:info@yarmeble.ru">info@yarmeble.ru</a></li><li><i class="fas fa-map-marker-alt"></i> г. Ярославль, ул. Промышленная, 12</li><li><i class="fas fa-clock"></i> Пн-Вс: 9:00 - 20:00</li></ul></div>
        </div>
        <div class="footer-bottom"><p>&copy; 2026 ЯрМебель. Все права защищены.</p></div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('chatModal');
        const openBtn = document.getElementById('openChatBtn');
        const closeBtn = document.getElementById('closeChatBtn');
        const sendBtn = document.getElementById('sendBtn');
        const userInput = document.getElementById('userInput');
        const chatBody = document.getElementById('chatBody');

        let isOpen = false;
        let conversationState = 'initial';
        let userParams = { room: null, size: null, style: null, budget: null };

        function scrollToBottom() { if (chatBody) chatBody.scrollTop = chatBody.scrollHeight; }

        openBtn.onclick = () => {
            if (isOpen) { modal.style.display = 'none'; isOpen = false; }
            else { modal.style.display = 'flex'; isOpen = true; setTimeout(() => { userInput.focus(); scrollToBottom(); }, 100); }
        };
        closeBtn.onclick = () => { modal.style.display = 'none'; isOpen = false; };
        window.onclick = (e) => { if (e.target == modal) { modal.style.display = 'none'; isOpen = false; } };

        function addMessage(text, isUser = false, htmlExtra = '') {
            const msgDiv = document.createElement('div');
            msgDiv.className = isUser ? 'user-message' : 'bot-message';
            const avatarIcon = isUser ? 'fa-user' : 'fa-robot';
            msgDiv.innerHTML = `<div class="message-avatar"><i class="fas ${avatarIcon}"></i></div><div class="message-bubble">${text.replace(/\n/g, '<br>')}${htmlExtra}</div>`;
            chatBody.appendChild(msgDiv);
            scrollToBottom();
            return msgDiv;
        }

        function addSuggestions(suggestions) {
            const suggDiv = document.createElement('div');
            suggDiv.className = 'bot-message';
            suggDiv.innerHTML = `<div class="message-avatar"><i class="fas fa-robot"></i></div><div class="message-bubble"><div class="suggestions">${suggestions.map(s => `<button class="suggestion-btn" data-msg="${s}">${s}</button>`).join('')}</div></div>`;
            chatBody.appendChild(suggDiv);
            suggDiv.querySelectorAll('.suggestion-btn').forEach(btn => {
                btn.addEventListener('click', () => { sendMessage(btn.dataset.msg); });
            });
            scrollToBottom();
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'bot-message';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `<div class="message-avatar"><i class="fas fa-robot"></i></div><div class="message-bubble typing-indicator"><span></span><span></span><span></span></div>`;
            chatBody.appendChild(typingDiv);
            scrollToBottom();
        }
        function hideTyping() { const t = document.getElementById('typingIndicator'); if (t) t.remove(); }

        const allProducts = <?php echo json_encode($all_products); ?>;
        const newProducts = <?php echo json_encode($new_products); ?>;

        function searchProductsByCategory(category) {
            const catLower = category.toLowerCase();
            return allProducts.filter(p => 
                p.name.toLowerCase().includes(catLower) || 
                (p.description && p.description.toLowerCase().includes(catLower))
            ).slice(0, 5);
        }

        function buildProductsHtml(products) {
            if (!products.length) return '';
            let html = '<div class="product-list">';
            products.forEach(p => {
                let sizeHtml = '';
                if (p.description) {
                    const sizeMatch = p.description.match(/\d{2,3}[xх×]\d{2,3}/i);
                    if (sizeMatch) sizeHtml = `<div class="chat-product-size">📐 ${sizeMatch[0]} см</div>`;
                }
                html += `<a href="product.php?id=${p.id}" class="chat-product-card" target="_blank">
                    <img src="images/products/${p.image || 'default.jpg'}" onerror="this.src='images/products/default.jpg'" alt="${escapeHtml(p.name)}">
                    <div class="chat-product-info">
                        <div class="chat-product-name">${escapeHtml(p.name)}</div>
                        <div class="chat-product-price">${Number(p.price).toLocaleString()} ₽</div>
                        ${sizeHtml}
                    </div>
                </a>`;
            });
            html += '</div>';
            return html;
        }

        function getDeliveryInfoHtml() {
            return `<div class="delivery-info">
                <h4>🚚 Доставка</h4>
                <p><strong>📍 Зоны доставки:</strong> Бесплатно в пределах города Ярославля. По области и в другие регионы — отправка транспортными компаниями.</p>
                <p><strong>⏱ Сроки доставки:</strong> По Ярославлю — 1-3 дня, по области — 2-5 дней, в другие города — от 5 до 14 дней.</p>
                <p><strong>📦 Что нужно знать:</strong> Доставка осуществляется с 10:00 до 20:00. За день до доставки курьер свяжется с вами. Сборка мебели уже входит в стоимость.</p>
                <h4>💳 Оплата</h4>
                <p><strong>Оплата при получении</strong> — самый удобный и безопасный способ! Выплачиваете заказ только после того, как осмотрите его.</p>
                <p><strong>Способы оплаты:</strong> наличными курьеру, банковской картой через терминал курьера, безналичный расчет для юридических лиц.</p>
                <p><strong>Важно:</strong> При заказе мы предлагаем <strong>бесплатную доставку</strong> по Ярославлю. Предоплата не требуется!</p>
            </div>`;
        }

        function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[m])); }

        async function sendMessage(forcedMsg = null) {
            const message = (forcedMsg !== null) ? forcedMsg : userInput.value.trim();
            if (!message) return;

            if (forcedMsg === null) {
                addMessage(message, true);
                userInput.value = '';
            }
            showTyping();
            await new Promise(r => setTimeout(r, 600));

            const lowerMsg = message.toLowerCase();
            let reply = '';
            let productsHtml = '';
            let suggestions = [];

            if (lowerMsg.match(/новинк|новые|поступление/i)) {
                reply = "🔥 Вот наши новинки — последние поступления:";
                productsHtml = buildProductsHtml(newProducts);
                suggestions = ["Помоги подобрать", "Условия доставки"];
            }

            else if (lowerMsg.match(/доставк|оплат|как доставить|стоимость доставк/i)) {
                hideTyping();
                addMessage("📦 Подробные условия доставки и оплаты:", false);
                addMessage(getDeliveryInfoHtml(), false);
                suggestions = ["Помоги подобрать", "Новинки"];
                return;
            }
            else if (conversationState === 'initial') {
                if (lowerMsg.match(/привет|здравствуй|помоги|подбери/i)) {
                    reply = "🐻 Здравствуйте! Я помогу подобрать идеальную мебель.\n\n📌 Для начала скажите:\n• Для какой комнаты ищете мебель? (гостиная, спальня, детская, кухня)\n• Какие размеры комнаты?\n• Какой бюджет?";
                    suggestions = ["Гостиная", "Спальня", "Детская", "Кухня"];
                    conversationState = 'choosing_room';
                } else {
                    reply = "🐻 Привет! Я мебельный консультант. Напишите «помоги подобрать», и я задам несколько вопросов, чтобы найти идеальный вариант!\n\nИли спросите про:\n• Новинки\n• Условия доставки";
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                }
            } 
            else if (conversationState === 'choosing_room') {
                const rooms = ['гостиная', 'спальня', 'детская', 'кухня', 'кабинет', 'прихожая'];
                const foundRoom = rooms.find(r => lowerMsg.includes(r));
                if (foundRoom) {
                    userParams.room = foundRoom;
                    reply = `Отлично! ${foundRoom.charAt(0).toUpperCase() + foundRoom.slice(1)} — хороший выбор.\n\n📏 Какая площадь или размеры комнаты? (например: "18 кв.м" или "ширина 3 метра")`;
                    conversationState = 'choosing_size';
                } else {
                    reply = "Подскажите, для какой комнаты нужна мебель? Варианты: гостиная, спальня, детская, кухня, кабинет, прихожая.";
                    suggestions = ["Гостиная", "Спальня", "Детская", "Кухня"];
                }
            }
            else if (conversationState === 'choosing_size') {
                const sizeMatch = lowerMsg.match(/\d{2,3}/);
                if (sizeMatch) {
                    userParams.size = sizeMatch[0] + ' кв.м';
                    reply = `Понял, площадь около ${userParams.size}. Какой бюджет примерно рассматриваете? (например: "до 50 000" или "50-100 тысяч")`;
                    conversationState = 'choosing_budget';
                } else {
                    reply = "Подскажите хотя бы примерную площадь комнаты в квадратных метрах. Это поможет подобрать мебель правильного размера.";
                }
            }
            else if (conversationState === 'choosing_budget') {
                const budgetMatch = lowerMsg.match(/\d{4,6}/);
                if (budgetMatch) {
                    userParams.budget = parseInt(budgetMatch[0]);
                    reply = `Спасибо! Ищу варианты для ${userParams.room} с бюджетом до ${userParams.budget.toLocaleString()} ₽...`;
                    hideTyping();
                    
                    let products = searchProductsByCategory(userParams.room);
                    if (userParams.budget) {
                        products = products.filter(p => p.price <= userParams.budget);
                    }
                    products = products.slice(0, 5);
                    
                    if (products.length > 0) {
                        productsHtml = buildProductsHtml(products);
                        reply += `\n\n🎯 Вот что подходит под ваши параметры:`;
                    } else {
                        reply += `\n\n😔 По вашим параметрам ничего не найдено. Попробуйте расширить бюджет или посмотрите эти варианты:`;
                        productsHtml = buildProductsHtml(allProducts.slice(0, 4));
                    }
                    conversationState = 'initial';
                    suggestions = ["Подобрать другую комнату", "Показать новинки", "Условия доставки"];
                } else {
                    reply = "Подскажите примерный бюджет в рублях (например: 50 000 или 100 000)";
                }
            }
            else {

                if (lowerMsg.match(/диван|софа/)) {
                    const products = searchProductsByCategory('диван');
                    reply = "🛋️ Вот диваны, которые могут вам подойти:";
                    productsHtml = buildProductsHtml(products);
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else if (lowerMsg.match(/кровать|спальня/)) {
                    const products = searchProductsByCategory('кровать');
                    reply = "🛏️ Кровати в наличии:";
                    productsHtml = buildProductsHtml(products);
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else if (lowerMsg.match(/шкаф|купе/)) {
                    const products = searchProductsByCategory('шкаф');
                    reply = "🚪 Шкафы для вашего интерьера:";
                    productsHtml = buildProductsHtml(products);
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else if (lowerMsg.match(/стол|обеденный/)) {
                    const products = searchProductsByCategory('стол');
                    reply = "🍽️ Столы от производителя:";
                    productsHtml = buildProductsHtml(products);
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else if (lowerMsg.match(/скидка|акция/)) {
                    reply = "🎉 Сейчас акция: -10% на спальные гарнитуры, -7% на все диваны до конца месяца!";
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else if (lowerMsg.match(/показать все|каталог/)) {
                    reply = "📖 Весь наш каталог мебели:";
                    productsHtml = buildProductsHtml(allProducts.slice(0, 6));
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                } else {
                    reply = "🧸 Я могу помочь подобрать мебель!\n\nНапишите:\n• «Помоги подобрать» — я задам вопросы\n• «Новинки» — свежие поступления\n• «Условия доставки» — всё о доставке и оплате";
                    suggestions = ["Помоги подобрать", "Новинки", "Условия доставки"];
                }
            }

            hideTyping();
            if (reply) addMessage(reply, false, productsHtml);
            if (suggestions.length) addSuggestions(suggestions);
        }

        sendBtn.addEventListener('click', () => sendMessage());
        userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
        

        setTimeout(() => {
            addMessage("🐻 Привет! Я Ярик — консультант.\n\nНапишите «помоги подобрать», и я подберу мебель по вашим параметрам.", false);
            addSuggestions(["Помоги подобрать", "Новинки", "Условия доставки"]);
        }, 500);
    });
</script>
</body>
</html>