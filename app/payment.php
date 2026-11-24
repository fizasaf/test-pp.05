<?php
require 'db.php';
require 'helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: cabinet.php');
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Получаем информацию о заявке и проверяем, что она принадлежит этому пользователю
$stmt = $pdo->prepare("SELECT o.*, p.title, p.base_price FROM orders o JOIN performance p ON o.performance_id = p.id WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order || $order['status'] !== 'new') {
    header('Location: cabinet.php');
    exit;
}

$total_price = $order['base_price'] * $order['tickets_count'];
$error = '';

// Обработка оплаты
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    $card_number = str_replace(' ', '', $_POST['card_number']);

    // Имитация проверки карты (просто проверяем длину)
    if (strlen($card_number) < 16) {
        $error = 'Неверный номер карты. Введите 16 цифр.';
    } else {
        // Обновляем статус заявки на "paid"
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
        if ($updateStmt->execute([$order_id])) {
            // Перекидываем на страницу успеха
            header('Location: payment_success.php?order_id=' . $order_id);
            exit;
        } else {
            $error = 'Ошибка при обработке платежа.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата билетов - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-card { max-width: 500px; margin: 40px auto; }
        .payment-summary { background: #1e222d; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4a90e2; }
        .card-input-group { display: flex; gap: 10px; margin-bottom: 15px; }
        .card-input-group input { flex: 1; }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav><a href="cabinet.php">Вернуться в кабинет</a></nav>
    </header>
    <div class="container">
        <div class="card payment-card">
            <h2>💳 Оплата билетов</h2>

            <?php if ($error): ?>
                <div class="alert alert-error" role="alert"><?= $error ?></div>
            <?php endif; ?>

            <div class="payment-summary">
                <p><strong>Спектакль:</strong> <?= htmlspecialchars($order['title']) ?></p>
                <p><strong>Количество билетов:</strong> <?= $order['tickets_count'] ?></p>
                <p style="font-size: 1.2rem; color: #27ae60; margin-top: 10px;">
                    <strong>Итого к оплате: <?= number_format($total_price, 0, '', ' ') ?> ₽</strong>
                </p>
            </div>

            <form method="POST">
                <input type="hidden" name="pay" value="1">

                <div class="form-group">
                    <label>Номер карты</label>
                    <input type="text" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>

                <div class="card-input-group">
                    <div class="form-group" style="flex: 1;">
                        <label>Срок действия</label>
                        <input type="text" name="expiry" placeholder="ММ/ГГ" maxlength="5" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>CVC</label>
                        <input type="password" name="cvc" placeholder="***" maxlength="3" required>
                    </div>
                </div>

                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 1.1rem; margin-top: 10px;">Оплатить <?= number_format($total_price, 0, '', ' ') ?> ₽</button>
            </form>
        </div>
    </div>
</body>
</html>