<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit;
}

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
}

// Получаем все заявки
$orders = $pdo->query("
    SELECT o.*, p.title, u.full_name, u.phone, u.email 
    FROM orders o 
    JOIN performance p ON o.performance_id = p.id 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP (Админ)</div>
        <nav>
            <a href="index.php">Главная</a> | 
            <span>Здравствуйте, <?= htmlspecialchars($_SESSION['full_name']) ?>!</span>
            <a href="logout.php" style="margin-left: 20px; color: #ff5252;">Выйти</a>
        </nav>
    </header>
    <div class="container">
        <div class="card">
            <h2>Управление заявками</h2>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="padding: 10px;">#</th>
                        <th>Клиент</th>
                        <th>Спектакль</th>
                        <th>Кол-во</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $index => $order): ?>
                        <tr style="border-bottom: 1px solid #2a2f3a;">
                            <td style="padding: 10px;"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($order['full_name']) ?><br><small><?= htmlspecialchars($order['phone']) ?></small></td>
                            <td><?= htmlspecialchars($order['title']) ?></td>
                            <td><?= $order['tickets_count'] ?></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="change_status" value="1">
                                    <select name="status" style="padding: 5px; background: #12151e; color: white; border: 1px solid #333; border-radius: 4px;">
                                        <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>>Новая</option>
                                        <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>Оплачена</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменена</option>
                                    </select>
                                    <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">OK</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Блок поддержки -->
        <div class="card" style="border: 1px solid #4a90e2;">
            <h3>🛠 Поддержка</h3>
            <p>Если у вас возникли технические неполадки, напишите нам:</p>
            <form method="POST">
                <div class="form-group">
                    <textarea rows="3" style="width: 100%; background: #12151e; color: white; border: 1px solid #333; padding: 10px; border-radius: 4px;" placeholder="Опишите проблему..."></textarea>
                </div>
                <button type="button" class="btn" onclick="alert('Сообщение отправлено в техподдержку!');">Отправить в поддержку</button>
            </form>
        </div>
    </div>
</body>
</html>
