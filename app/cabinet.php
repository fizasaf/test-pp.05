<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Обработка создания новой заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $performance_id = $_POST['performance_id'];
    $tickets_count = (int)$_POST['tickets_count'];
    $payment_method = $_POST['payment_method'];

    if ($tickets_count > 0) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, performance_id, tickets_count, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $performance_id, $tickets_count, $payment_method]);
    }
}

// Получаем список спектаклей для выпадающего списка
$performances = $pdo->query("SELECT id, title FROM performance")->fetchAll();

// Получаем заявки текущего пользователя
$stmt = $pdo->prepare("SELECT o.*, p.title FROM orders o JOIN performance p ON o.performance_id = p.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <a href="index.php">Главная</a>
            <span style="margin: 0 10px; color: #777;">|</span>
            <span>Здравствуйте, <?= htmlspecialchars($_SESSION['full_name']) ?>!</span>
            <a href="logout.php" style="margin-left: 20px; color: #ff5252;">Выйти</a>
        </nav>
    </header>
    <div class="container">
        <div class="card">
            <h2>Создать заявку</h2>
            <form method="POST">
                <input type="hidden" name="create_order" value="1">
                <div class="form-group">
                    <label>Спектакль</label>
                    <select name="performance_id" required>
                        <?php foreach ($performances as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Количество билетов</label>
                    <input type="number" name="tickets_count" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label>Оплата</label>
                    <select name="payment_method" required>
                        <option value="card">Банковская карта</option>
                        <option value="online">Онлайн-оплата</option>
                    </select>
                </div>
                <button type="submit" class="btn">Отправить</button>
            </form>
        </div>

        <div class="card">
            <h2>Мои заявки</h2>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="padding: 10px;">#</th>
                        <th>Спектакль</th>
                        <th>Кол-во</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="5" style="padding: 10px;">У вас пока нет заявок.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $index => $order): ?>
                            <tr style="border-bottom: 1px solid #2a2f3a;">
                                <td style="padding: 10px;"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><?= $order['tickets_count'] ?></td>
                                <td><?= $order['payment_method'] ?></td>
                                <td>
                                    <span style="background: #2a5b8e; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
