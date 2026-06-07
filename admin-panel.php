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

$stmt = $pdo->prepare("SELECT role_id FROM usersD WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_role = $stmt->fetchColumn();

if ($user_role != 2) {
    header('Location: profile.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usersD WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Обновление профиля
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
        if ($stmt->execute([$first_name, $last_name, $phone, $email, $_SESSION['user_id']])) {
            $update_success = 'Данные успешно обновлены';
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $stmt = $pdo->prepare("SELECT * FROM usersD WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } else {
            $update_error = 'Ошибка при обновлении данных';
        }
    }
}

// Обработка обновления статуса (через POST из этой же страницы)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $allowed = ['Новый', 'На изготовлении', 'Отправлен', 'Доставлен', 'Отменен'];
    
    if (in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE ordersD SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
    }
    // Перенаправляем обратно, чтобы избежать повторной отправки
    header('Location: admin-panel.php?status_updated=1');
    exit;
}

// Работа с таблицами
$table_message = '';
$table_error = '';
$current_table = isset($_GET['table']) ? $_GET['table'] : 'productsD';
$allowed_tables = ['productsD', 'categoriesD', 'materialsD', 'colorsD', 'usersD'];
if (!in_array($current_table, $allowed_tables)) {
    $current_table = 'productsD';
}

$table_data = [];
$table_columns = [];
$stmt = $pdo->query("SELECT * FROM $current_table");
$table_data = $stmt->fetchAll();
for ($i = 0; $i < $stmt->columnCount(); $i++) {
    $col = $stmt->getColumnMeta($i);
    $table_columns[] = $col['name'];
}

// Добавление записи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $table = $_POST['table_name'];
    $columns = [];
    $values = [];
    $placeholders = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'add_record' && $key != 'table_name') {
            $columns[] = $key;
            $values[] = $value;
            $placeholders[] = '?';
        }
    }
    if (!empty($columns)) {
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($values)) {
            $table_message = 'Запись успешно добавлена';
            $stmt = $pdo->query("SELECT * FROM $current_table");
            $table_data = $stmt->fetchAll();
        } else {
            $table_error = 'Ошибка при добавлении записи';
        }
    }
}

// Редактирование записи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_record'])) {
    $record_id = (int)$_POST['record_id'];
    $table = $_POST['table_name'];
    $updates = [];
    $params = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'edit_record' && $key != 'record_id' && $key != 'table_name') {
            $updates[] = "$key = ?";
            $params[] = $value;
        }
    }
    $params[] = $record_id;
    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $table_message = 'Запись успешно обновлена';
        $stmt = $pdo->query("SELECT * FROM $current_table");
        $table_data = $stmt->fetchAll();
    } else {
        $table_error = 'Ошибка при обновлении записи';
    }
}

// Удаление записи
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM $current_table WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $table_message = 'Запись удалена';
        $stmt = $pdo->query("SELECT * FROM $current_table");
        $table_data = $stmt->fetchAll();
    }
}

// Отчёты
$report_start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$report_end = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT SUM(total) as total_revenue, COUNT(*) as orders_count, AVG(total) as avg_check
    FROM ordersD 
    WHERE DATE(created_at) BETWEEN ? AND ? AND LOWER(status) = 'доставлен'
");
$stmt->execute([$report_start, $report_end]);
$report_data = $stmt->fetch();
$total_revenue = $report_data['total_revenue'] ?? 0;
$orders_count = $report_data['orders_count'] ?? 0;
$avg_check = round($report_data['avg_check'] ?? 0, 2);

$users_count = $pdo->query("SELECT COUNT(*) FROM usersD")->fetchColumn();
$products_count = $pdo->query("SELECT COUNT(*) FROM productsD")->fetchColumn();
$orders_total = $pdo->query("SELECT COUNT(*) FROM ordersD")->fetchColumn();

// Получаем заказы
$all_orders = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM ordersD o
    JOIN usersD u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

foreach ($all_orders as &$order) {
    $items_stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_itemsD oi 
        JOIN productsD p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order['id']]);
    $order['items'] = $items_stmt->fetchAll();
}

$status_styles = [
    'Новый' => ['class' => 'status-new', 'icon' => 'fa-clock'],
    'На изготовлении' => ['class' => 'status-production', 'icon' => 'fa-gear'],
    'Отправлен' => ['class' => 'status-shipped', 'icon' => 'fa-truck'],
    'Доставлен' => ['class' => 'status-delivered', 'icon' => 'fa-check-circle'],
    'Отменен' => ['class' => 'status-cancelled', 'icon' => 'fa-ban']
];

$users_list = $pdo->query("SELECT id, first_name, last_name, email FROM usersD")->fetchAll();
$products_list = $pdo->query("SELECT id, name, price FROM productsD")->fetchAll();
$materials_list = $pdo->query("SELECT id, name FROM materialsD")->fetchAll();
$colors_list = $pdo->query("SELECT id, name FROM colorsD")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - ЯрМебель</title>
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
        .nav-main ul { display: flex; gap: 30px; }
        .nav-main a { font-weight: 500; transition: var(--transition); }
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
        input, select, textarea { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 14px; transition: var(--transition); }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }
        .btn-save { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 30px; cursor: pointer; font-weight: 600; transition: var(--transition); }
        .btn-save:hover { background: var(--primary-dark); transform: scale(1.02); }
        .btn-danger { background: #e74c3c; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; font-size: 14px; }
        .btn-danger:hover { background: #c0392b; }
        .btn-secondary { background: #6c757d; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; font-size: 14px; }
        .btn-secondary:hover { background: #5a6268; }
        .success-msg { background: #d4edda; color: #155724; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 12px; margin-bottom: 20px; }
        
        .orders-title { font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: space-between; }
        .btn-create-order { background: #27ae60; color: white; border: none; padding: 10px 25px; border-radius: 30px; cursor: pointer; font-weight: 500; }
        .btn-create-order:hover { background: #219a52; }
        .order-card {
            background: var(--bg-white);
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
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
        .order-products-summary li { margin-bottom: 8px; }
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
        .status-select {
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid var(--border);
            font-family: inherit;
            cursor: pointer;
        }
        .btn-update-status { background: var(--primary); color: white; border: none; padding: 6px 15px; border-radius: 20px; cursor: pointer; margin-left: 10px; }
        
        .create-order-form {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            display: none;
        }
        .create-order-form h3 { margin-bottom: 20px; }
        .order-items-list { margin: 15px 0; }
        .order-item-row {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 15px;
            border: 1px solid var(--border);
        }
        .order-item-row select, .order-item-row input {
            margin-bottom: 10px;
        }
        .item-options-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .size-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .btn-remove-item {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }
        .btn-add-item { background: #3498db; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; margin-top: 10px; }
        
        .table-container {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }
        .table-container h2 { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; justify-content: space-between; flex-wrap: wrap; }
        .table-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .table-tab {
            padding: 8px 20px;
            background: var(--bg-light);
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
        }
        .table-tab.active { background: var(--primary); color: white; }
        .table-tab:hover { background: var(--primary-dark); color: white; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--bg-light); font-weight: 600; }
        
        .actions-cell {
            white-space: nowrap;
            width: 70px;
            text-align: center;
        }
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin: 0 3px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-edit-icon {
            background: #3498db;
            color: white;
        }
        .btn-edit-icon:hover {
            background: #2980b9;
        }
        .btn-delete-icon {
            background: #e74c3c;
            color: white;
            text-decoration: none;
        }
        .btn-delete-icon:hover {
            background: #c0392b;
        }
        
        .edit-form, .add-form {
            margin-top: 20px;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 16px;
            display: none;
        }
        .edit-form.active, .add-form.active { display: block; }
        
        .reports-section {
            background: var(--bg-white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
        }
        .reports-section h2 { margin-bottom: 20px; }
        .report-filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 25px;
        }
        .report-filters .form-group { margin-bottom: 0; }
        .report-filters input { padding: 10px 15px; border: 1px solid var(--border); border-radius: 12px; }
        .btn-filter { background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 30px; cursor: pointer; }
        .report-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        .report-card {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
        }
        .report-card h3 { font-size: 14px; color: var(--text-light); margin-bottom: 10px; text-transform: uppercase; }
        .report-card .value { font-size: 36px; font-weight: 700; color: var(--primary); }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        .stat-card { background: var(--bg-light); padding: 20px; text-align: center; border-radius: 16px; }
        
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
            .stats-grid, .report-cards { grid-template-columns: 1fr; }
            .item-options-row, .size-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-main { display: none; position: absolute; top: 70px; left: 0; right: 0; background: white; padding: 20px; box-shadow: var(--shadow); }
            th, td { padding: 8px; font-size: 12px; }
            .order-header { flex-direction: column; align-items: flex-start; }
            .report-filters { flex-direction: column; align-items: stretch; }
            .actions-cell { width: 50px; }
            .btn-icon { width: 28px; height: 28px; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo"><a href="index.php">Яр<span>Мебель</span></a></div>
            <div class="header-search"></div>
            <div class="header-actions"></div>
            <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="nav-main" id="mainNav">
            <ul></ul>
        </nav>
    </div>
</header>

<section class="profile-section">
    <div class="container">
        <?php if (isset($_GET['status_updated'])): ?>
            <div class="success-msg">Статус заказа успешно обновлён!</div>
        <?php endif; ?>
        <?php if ($update_success): ?>
            <div class="success-msg"><?php echo $update_success; ?></div>
        <?php endif; ?>
        <?php if ($update_error): ?>
            <div class="error-msg"><?php echo $update_error; ?></div>
        <?php endif; ?>
        <?php if ($table_message): ?>
            <div class="success-msg"><?php echo $table_message; ?></div>
        <?php endif; ?>
        <?php if ($table_error): ?>
            <div class="error-msg"><?php echo $table_error; ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <div class="sidebar-card">
                <div class="avatar"><i class="fas fa-user-shield"></i></div>
                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="sidebar-menu">
                    <div class="sidebar-menu-item" data-tab="edit"><i class="fas fa-user-edit"></i> <span>Редактировать профиль</span></div>
                    <div class="sidebar-menu-item active" data-tab="orders"><i class="fas fa-shopping-cart"></i> <span>Заказы</span></div>
                    <div class="sidebar-menu-item" data-tab="tables"><i class="fas fa-database"></i> <span>Таблицы</span></div>
                    <div class="sidebar-menu-item" data-tab="reports"><i class="fas fa-chart-line"></i> <span>Отчеты</span></div>
                </div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Выйти</span></a>
            </div>
            
            <div class="right-content">
                <div id="tab-edit" class="tab-content" style="display: none;">
                    <div class="edit-form-card">
                        <h2><i class="fas fa-user-edit"></i> Редактирование профиля</h2>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Фамилия</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="update_profile" class="btn-save">Сохранить изменения</button>
                        </form>
                    </div>
                </div>
                <div id="tab-orders" class="tab-content">
                    <div class="orders-title">
                        <span><i class="fas fa-shopping-cart"></i> Все заказы</span>
                        <button class="btn-create-order" onclick="toggleCreateOrderForm()"><i class="fas fa-plus"></i> Создать заказ</button>
                    </div>
                    
                    <div id="createOrderForm" class="create-order-form">
                        <h3><i class="fas fa-plus-circle"></i> Создание нового заказа</h3>
                        <form method="POST" id="createOrderFormElement">
                            <div class="form-group">
                                <label>Выберите пользователя</label>
                                <select name="user_id" required>
                                    <option value="">-- Выберите пользователя --</option>
                                    <?php foreach ($users_list as $u): ?>
                                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Адрес доставки</label>
                                <input type="text" name="address" required placeholder="г. Ярославль, ул. ...">
                            </div>
                            <div class="form-group">
                                <label>Комментарий</label>
                                <textarea name="comment" rows="2" placeholder="Дополнительная информация"></textarea>
                            </div>
                            
                            <div class="order-items-list">
                                <label>Товары в заказе</label>
                                <div id="orderItemsContainer">
                                    <div class="order-item-row" data-index="0">
                                        <div class="item-options-row">
                                            <select name="items[0][product_id]" class="product-select" required>
                                                <option value="">-- Выберите товар --</option>
                                                <?php foreach ($products_list as $p): ?>
                                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name'] . ' - ' . number_format($p['price'], 0, '', ' ') . ' ₽'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="items[0][material_id]" class="material-select">
                                                <option value="">-- Материал --</option>
                                                <?php foreach ($materials_list as $m): ?>
                                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="items[0][color_id]" class="color-select">
                                                <option value="">-- Цвет --</option>
                                                <?php foreach ($colors_list as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="size-row">
                                            <input type="number" name="items[0][size_width]" placeholder="Ширина (см)" step="1">
                                            <input type="number" name="items[0][size_depth]" placeholder="Глубина (см)" step="1">
                                            <input type="number" name="items[0][size_height]" placeholder="Высота (см)" step="1">
                                        </div>
                                        <div class="item-options-row">
                                            <input type="number" name="items[0][quantity]" placeholder="Кол-во" min="1" value="1" required>
                                        </div>
                                        <button type="button" class="btn-remove-item" onclick="removeOrderItem(this)" style="display: none;">✖ Удалить товар</button>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-item" onclick="addOrderItem()"><i class="fas fa-plus"></i> Добавить товар</button>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="create_order" class="btn-save">Создать заказ</button>
                                <button type="button" class="btn-secondary" onclick="toggleCreateOrderForm()">Отмена</button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (count($all_orders) > 0): ?>
                        <?php foreach ($all_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header" onclick="toggleOrderBody(this)">
                                <div class="order-number"><i class="fas fa-receipt"></i> Заказ № <?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div class="order-date"><i class="far fa-calendar-alt"></i> <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></div>
                                <div><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong> | <?php echo number_format($order['total'], 0, '', ' '); ?> ₽</div>
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
                                    <div>
                                        <form method="POST" style="display: inline-flex; gap: 10px; align-items: center;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="status-select">
                                                <option value="Новый" <?php echo $order['status'] == 'Новый' ? 'selected' : ''; ?>>Новый</option>
                                                <option value="На изготовлении" <?php echo $order['status'] == 'На изготовлении' ? 'selected' : ''; ?>>На изготовлении</option>
                                                <option value="Отправлен" <?php echo $order['status'] == 'Отправлен' ? 'selected' : ''; ?>>Отправлен</option>
                                                <option value="Доставлен" <?php echo $order['status'] == 'Доставлен' ? 'selected' : ''; ?>>Доставлен</option>
                                                <option value="Отменен" <?php echo $order['status'] == 'Отменен' ? 'selected' : ''; ?>>Отменен</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update-status">Обновить</button>
                                        </form>
                                    </div>
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
                        <p>Нет заказов</p>
                    <?php endif; ?>
                </div>
                
                <!-- Вкладка Таблицы -->
                <div id="tab-tables" class="tab-content" style="display: none;">
                    <div class="table-container">
                        <div class="table-tabs">
                            <?php foreach ($allowed_tables as $tbl): ?>
                                <a href="?table=<?php echo $tbl; ?>" class="table-tab <?php echo $current_table == $tbl ? 'active' : ''; ?>">
                                    <?php echo $tbl; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <h2>
                            <span><i class="fas fa-table"></i> Таблица: <?php echo $current_table; ?></span>
                            <?php if ($current_table != 'usersD'): ?>
                                <button class="btn-save" onclick="toggleAddForm()" style="padding: 8px 20px;"><i class="fas fa-plus"></i> Добавить запись</button>
                            <?php endif; ?>
                        </h2>
                        
                        <?php if ($current_table != 'usersD'): ?>
                        <div id="addForm" class="add-form">
                            <h3>Добавление новой записи</h3>
                            <form method="POST">
                                <input type="hidden" name="add_record" value="1">
                                <input type="hidden" name="table_name" value="<?php echo $current_table; ?>">
                                <div id="add_fields">
                                    <?php foreach ($table_columns as $col): ?>
                                        <?php if ($col != 'id' && $col != 'created_at' && $col != 'updated_at'): ?>
                                            <div class="form-group">
                                                <label><?php echo $col; ?></label>
                                                <input type="text" name="<?php echo $col; ?>" class="form-control">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn-save">Добавить</button>
                                <button type="button" class="btn-secondary" onclick="toggleAddForm()">Отмена</button>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (count($table_data) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <?php foreach ($table_columns as $col): ?>
                                            <th style="padding: 10px 8px; text-align: left; border-bottom: 1px solid #ddd;"><?php echo $col; ?></th>
                                        <?php endforeach; ?>
                                        <th style="width: 70px; text-align: center;">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table_data as $row): ?>
                                    <tr>
                                        <?php foreach ($table_columns as $col): ?>
                                            <td style="padding: 10px 8px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars(substr($row[$col] ?? '', 0, 50)); ?><?php echo strlen($row[$col] ?? '') > 50 ? '...' : ''; ?></td>
                                        <?php endforeach; ?>
                                        <td style="white-space: nowrap; width: 70px; text-align: center;">
                                            <?php if ($current_table != 'usersD'): ?>
                                                <button class="btn-icon btn-edit-icon" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($row)); ?>, '<?php echo $current_table; ?>')" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?table=<?php echo $current_table; ?>&delete_id=<?php echo $row['id']; ?>" class="btn-icon btn-delete-icon" onclick="return confirm('Удалить запись?')" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #999; font-size:12px;">просмотр</span>
                                            <?php endif; ?>
                                         </div>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p>Нет данных в таблице</p>
                        <?php endif; ?>
                        
                        <div id="editForm" class="edit-form">
                            <h3>Редактирование записи</h3>
                            <form method="POST" id="editRecordForm">
                                <input type="hidden" name="edit_record" value="1">
                                <input type="hidden" name="record_id" id="edit_record_id">
                                <input type="hidden" name="table_name" id="edit_table_name" value="<?php echo $current_table; ?>">
                                <div id="edit_fields"></div>
                                <button type="submit" class="btn-save">Сохранить изменения</button>
                                <button type="button" class="btn-secondary" onclick="hideEditForm()">Отмена</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка Отчеты -->
                <div id="tab-reports" class="tab-content" style="display: none;">
                    <div class="reports-section">
                        <h2><i class="fas fa-chart-line"></i> Статистические отчеты</h2>
                        <form method="GET" class="report-filters" id="reportForm">
                            <div class="form-group">
                                <label>Период с</label>
                                <input type="date" name="start_date" value="<?php echo $report_start; ?>">
                            </div>
                            <div class="form-group">
                                <label>по</label>
                                <input type="date" name="end_date" value="<?php echo $report_end; ?>">
                            </div>
                            <button type="submit" class="btn-filter">Применить</button>
                        </form>
                        <div class="report-cards">
                            <div class="report-card"><h3><i class="fas fa-ruble-sign"></i> Общая выручка</h3><div class="value"><?php echo number_format($total_revenue, 0, '', ' '); ?> ₽</div><div class="period">за выбранный период</div></div>
                            <div class="report-card"><h3><i class="fas fa-shopping-cart"></i> Количество заказов</h3><div class="value"><?php echo $orders_count; ?></div><div class="period">за выбранный период</div></div>
                            <div class="report-card"><h3><i class="fas fa-receipt"></i> Средний чек</h3><div class="value"><?php echo number_format($avg_check, 0, '', ' '); ?> ₽</div><div class="period">за выбранный период</div></div>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-card"><i class="fas fa-users"></i><h3><?php echo $users_count; ?></h3><p>Всего пользователей</p></div>
                            <div class="stat-card"><i class="fas fa-box"></i><h3><?php echo $products_count; ?></h3><p>Всего товаров</p></div>
                            <div class="stat-card"><i class="fas fa-shopping-cart"></i><h3><?php echo $orders_total; ?></h3><p>Всего заказов</p></div>
                        </div>
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
    let itemCounter = 1;
    
    function toggleOrderBody(header) {
        header.nextElementSibling.classList.toggle('active');
    }
    
    function toggleCreateOrderForm() {
        const form = document.getElementById('createOrderForm');
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
    
    function addOrderItem() {
        const container = document.getElementById('orderItemsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'order-item-row';
        newRow.setAttribute('data-index', itemCounter);
        newRow.innerHTML = `
            <div class="item-options-row">
                <select name="items[${itemCounter}][product_id]" class="product-select" required>
                    <option value="">-- Выберите товар --</option>
                    <?php foreach ($products_list as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name'] . ' - ' . number_format($p['price'], 0, '', ' ') . ' ₽'); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="items[${itemCounter}][material_id]" class="material-select">
                    <option value="">-- Материал --</option>
                    <?php foreach ($materials_list as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="items[${itemCounter}][color_id]" class="color-select">
                    <option value="">-- Цвет --</option>
                    <?php foreach ($colors_list as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="size-row">
                <input type="number" name="items[${itemCounter}][size_width]" placeholder="Ширина (см)" step="1">
                <input type="number" name="items[${itemCounter}][size_depth]" placeholder="Глубина (см)" step="1">
                <input type="number" name="items[${itemCounter}][size_height]" placeholder="Высота (см)" step="1">
            </div>
            <div class="item-options-row">
                <input type="number" name="items[${itemCounter}][quantity]" placeholder="Кол-во" min="1" value="1" required>
            </div>
            <button type="button" class="btn-remove-item" onclick="removeOrderItem(this)">✖ Удалить товар</button>
        `;
        container.appendChild(newRow);
        itemCounter++;
    }
    
    function removeOrderItem(btn) {
        btn.parentElement.remove();
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach((row, idx) => {
            const removeBtn = row.querySelector('.btn-remove-item');
            if (removeBtn) {
                removeBtn.style.display = rows.length === 1 ? 'none' : 'inline-block';
            }
        });
    }
    
    function showEditForm(row, tableName) {
        document.getElementById('edit_record_id').value = row.id;
        document.getElementById('edit_table_name').value = tableName;
        let fields = '';
        for (let [key, value] of Object.entries(row)) {
            if (key !== 'id') {
                fields += `<div class="form-group"><label>${key}</label><input type="text" name="${key}" value="${escapeHtml(String(value))}" class="form-control"></div>`;
            }
        }
        document.getElementById('edit_fields').innerHTML = fields;
        document.getElementById('editForm').classList.add('active');
        document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideEditForm() {
        document.getElementById('editForm').classList.remove('active');
    }
    
    function toggleAddForm() {
        document.getElementById('addForm').classList.toggle('active');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.querySelectorAll('.sidebar-menu-item').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.sidebar-menu-item').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            const tabName = tab.dataset.tab;
            const activeContent = document.getElementById(`tab-${tabName}`);
            if (activeContent) {
                activeContent.style.display = 'block';
            }
            localStorage.setItem('admin_active_tab', tabName);
        });
    });

    const savedTab = localStorage.getItem('admin_active_tab');
    if (savedTab && document.getElementById(`tab-${savedTab}`)) {
        const savedTabElement = document.querySelector(`.sidebar-menu-item[data-tab="${savedTab}"]`);
        if (savedTabElement) {
            savedTabElement.click();
        } else {
            const ordersTab = document.querySelector('.sidebar-menu-item[data-tab="orders"]');
            if (ordersTab) ordersTab.click();
        }
    } else {
        const ordersTab = document.querySelector('.sidebar-menu-item[data-tab="orders"]');
        if (ordersTab) ordersTab.click();
    }

    const mobileBtn = document.getElementById('mobileMenuBtn');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            const nav = document.getElementById('mainNav');
            nav.style.display = nav.style.display === 'block' ? 'none' : 'block';
        });
    }

    const createOrderForm = document.getElementById('createOrderFormElement');
    if (createOrderForm) {
        let isSubmitting = false;
        createOrderForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
            setTimeout(() => {
                isSubmitting = false;
            }, 5000);
        });
    }
</script>
</body>
</html>