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
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password_input)) {
        $error = 'Пожалуйста, заполните все обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email адрес';
    } elseif (strlen($password_input) < 4) {
        $error = 'Пароль должен содержать не менее 4 символов';
    } elseif ($password_input !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usersD WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже зарегистрирован';
        } else {
           
            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO usersD (role_id, first_name, last_name, email, password, phone) 
                VALUES (1, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone])) {
                $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
            } else {
                $error = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ЯрМебель</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(rgba(44,62,80,0.8), rgba(44,62,80,0.8)), url('images/1.jpg'); background-size: cover; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .register-container { background: white; border-radius: 24px; padding: 40px; max-width: 500px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo a { font-size: 32px; font-weight: 700; font-family: 'Playfair Display', serif; color: #2C3E50; text-decoration: none; }
        .logo span { color: #8B4513; }
        h2 { text-align: center; margin-bottom: 10px; color: #2C3E50; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input { width: 100%; padding: 14px 16px; border: 1px solid #ddd; border-radius: 12px; font-family: inherit; font-size: 15px; transition: 0.3s; }
        input:focus { outline: none; border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .btn { width: 100%; padding: 14px; background: #8B4513; color: white; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #6B3410; transform: scale(1.02); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .login-link { text-align: center; margin-top: 20px; color: #666; }
        .login-link a { color: #8B4513; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo"><a href="index.php">Яр<span>Мебель</span></a></div>
        <h2>Регистрация</h2>
        <p class="subtitle">Создайте аккаунт для оформления заказов</p>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="row">
                <div class="form-group"><label>Имя *</label><input type="text" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"></div>
                <div class="form-group"><label>Фамилия *</label><input type="text" name="last_name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"></div>
            </div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div>
            <div class="form-group"><label>Телефон</label><input type="tel" name="phone" placeholder="+7 (XXX) XXX-XX-XX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"></div>
            <div class="row">
                <div class="form-group"><label>Пароль *</label><input type="password" name="password" required></div>
                <div class="form-group"><label>Подтверждение *</label><input type="password" name="password_confirm" required></div>
            </div>
            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>
        <div class="login-link">Уже есть аккаунт? <a href="login.php">Войти</a></div>
    </div>
</body>
</html>