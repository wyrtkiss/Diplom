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

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin-panel.php');
    } else {
        header('Location: profile.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password_input)) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.email, u.password, u.first_name, u.last_name, u.role_id, r.name as role_name 
            FROM usersD u 
            JOIN rolesD r ON u.role_id = r.id 
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $password_valid = false;
            if (password_verify($password_input, $user['password'])) {
                $password_valid = true;
            } elseif ($password_input === $user['password']) {
                $password_valid = true;
            }
            
            if ($password_valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['role_id'] = $user['role_id'];
                
                if (isset($_SESSION['temp_cart_item'])) {
                    if (!isset($_SESSION['cart'])) {
                        $_SESSION['cart'] = [];
                    }
                    
                    $temp_item = $_SESSION['temp_cart_item'];
                    $found = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['product_id'] == $temp_item['product_id'] && 
                            $item['material_id'] == $temp_item['material_id'] && 
                            $item['color_id'] == $temp_item['color_id']) {
                            $item['quantity'] += $temp_item['quantity'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $_SESSION['cart'][] = $temp_item;
                    }
                    
                    unset($_SESSION['temp_cart_item']);
                    header('Location: cart.php');
                    exit;
                }
                
                if ($user['role_name'] === 'admin' || $user['role_id'] == 2) {
                    header('Location: admin-panel.php'); 
                } else {
                    header('Location: profile.php');      
                }
                exit;
            } else {
                $error = 'Неверный email или пароль';
            }
        } else {
            $error = 'Неверный email или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - ЯрМебель</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(rgba(44,62,80,0.8), rgba(44,62,80,0.8)), url('images/1.jpg'); background-size: cover; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; position: relative; }
        
        /* Кнопка "На главную" */
        .home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 10px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            z-index: 100;
        }
        .home-btn:hover {
            background: rgba(255,255,255,0.35);
            transform: translateY(-2px);
        }
        .home-btn i {
            font-size: 14px;
        }
        
        .login-container { background: white; border-radius: 24px; padding: 40px; max-width: 450px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative; z-index: 1; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo a { font-size: 32px; font-weight: 700; font-family: 'Playfair Display', serif; color: #2C3E50; text-decoration: none; }
        .logo span { color: #8B4513; }
        h2 { text-align: center; margin-bottom: 10px; color: #2C3E50; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input { width: 100%; padding: 14px 16px; border: 1px solid #ddd; border-radius: 12px; font-family: inherit; font-size: 15px; transition: 0.3s; }
        input:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }
        .btn { width: 100%; padding: 14px; background: #8B4513; color: white; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #6B3410; transform: scale(1.02); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .register-link { text-align: center; margin-top: 20px; color: #666; }
        .register-link a { color: #8B4513; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
        
        @media (max-width: 480px) {
            .home-btn { top: 10px; left: 10px; padding: 6px 14px; font-size: 12px; }
            .login-container { padding: 25px; }
        }
    </style>
</head>
<body>
    <!-- Кнопка "На главную" -->
    <a href="index.php" class="home-btn">
        <i class="fas fa-home"></i> На главную
    </a>
    
    <div class="login-container">
        <div class="logo"><a href="index.php">Яр<span>Мебель</span></a></div>
        <h2>Вход в аккаунт</h2>
        <p class="subtitle">Введите свои данные для входа</p>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
        <div class="register-link">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></div>
    </div>
</body>
</html>