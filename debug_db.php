<?php
// debug_db.php - Показать все данные из всех таблиц БД

$host = 'localhost';
$dbname = 'diplom';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Получаем список всех таблиц
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Диагностика БД - ЯрМебель</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .table-container { background: white; margin-bottom: 30px; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .table-title { background: #8B4513; color: white; padding: 12px 20px; font-size: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        tr:hover { background: #f9f9f9; }
        .no-data { padding: 20px; text-align: center; color: #999; }
        pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; max-width: 300px; }
    </style>
</head>
<body>
    <h1>📊 Структура и данные базы данных 'diplom'</h1>
";

foreach ($tables as $table) {
    // Получаем структуру таблицы
    $columns = $pdo->query("DESCRIBE `$table`")->fetchAll();
    
    // Получаем все данные из таблицы
    $data = $pdo->query("SELECT * FROM `$table`")->fetchAll();
    
    echo "<div class='table-container'>";
    echo "<div class='table-title'>📁 Таблица: $table (" . count($data) . " записей)</div>";
    
    if (count($data) > 0) {
        echo "<table>";
        echo "<thead><tr>";
        foreach ($columns as $col) {
            echo "<th>" . htmlspecialchars($col['Field']) . "</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($columns as $col) {
                $value = $row[$col['Field']] ?? '';
                if (is_null($value) || $value === '') {
                    echo "<td style='color:#999;'>—</td>";
                } elseif (strlen($value) > 100) {
                    echo "<td><pre>" . htmlspecialchars(substr($value, 0, 200)) . (strlen($value) > 200 ? '…' : '') . "</pre></td>";
                } else {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='no-data'>📭 Таблица пуста</div>";
    }
    echo "</div>";
}

echo "
    <hr>
    <h2>🔧 Таблицы в базе данных:</h2>
    <ul>";
foreach ($tables as $table) {
    echo "<li><strong>$table</strong></li>";
}
echo "</ul>
</body>
</html>";
?>