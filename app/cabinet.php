<?php
require 'db.php';
require 'helpers.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: login.php');
    exit;
}

// Обработка смены пароля
$pass_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];

    // Получаем текущий пароль из БД
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_hash = $stmt->fetchColumn();

    if (password_verify($old_pass, $current_hash)) {
        if (strlen($new_pass) >= 6) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$new_hash, $_SESSION['user_id']]);
            $pass_msg = '<div class="alert alert-success" role="alert">Пароль успешно изменен!</div>';
        } else {
            $pass_msg = '<div class="alert alert-error" role="alert">Новый пароль должен быть не менее 6 символов.</div>';
        }
    } else {
        $pass_msg = '<div class="alert alert-error" role="alert">Неверный текущий пароль.</div>';
    }
}

$user_id = $_SESSION['user_id'];

// Обработка создания заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $performance_id = $_POST['performance_id'];
    $tickets_count = (int)$_POST['tickets_count'];
    $payment_method = $_POST['payment_method'];

    if ($tickets_count > 0 && $tickets_count < 100) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, performance_id, tickets_count, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $performance_id, $tickets_count, $payment_method]);
    }
}

$performances = $pdo->query("SELECT id, title FROM performance")->fetchAll();

// Получаем историю покупок
$stmt = $pdo->prepare("SELECT o.*, p.title FROM orders o JOIN performance p ON o.performance_id = p.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <a href="index.php">Главная</a>
            <a href="catalog.php">Афиша</a>
            <a href="support.php">Поддержка</a>
            <a href="logout.php" style="color: #ff5252;">Выйти</a>
        </nav>
    </header>
    <div class="container">
        <!-- Профиль -->
       <div class="card profile-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
           <div style="display: flex; align-items: center; gap: 20px;">
               <div class="profile-avatar">👤</div>
               <div>
                   <h2>Здравствуйте, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
                   <p style="color: #777; margin: 0;">Личный кабинет зрителя</p>
               </div>
           </div>
           <!-- Кнопка вызова модального окна -->
           <button class="btn" onclick="document.getElementById('passwordModal').style.display='flex'" style="background: transparent; border: 1px solid #4a90e2; color: #4a90e2;">
               🔑 Сменить пароль
           </button>
       </div>

        <!-- Создание заявки -->
        <div class="card">
            <h2>Создать заявку</h2>
            <form method="POST" class="order-form">
                <input type="hidden" name="create_order" value="1">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Спектакль</label>
                        <select name="performance_id" required>
                            <?php foreach ($performances as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($_GET['performance_id']) && $_GET['performance_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Кол-во билетов</label>
                        <input type="number" name="tickets_count" min="1" max="10" value="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Оплата</label>
                    <select name="payment_method" required>
                        <option value="card">Банковская карта</option>
                        <option value="online">Онлайн-оплата</option>
                    </select>
                </div>
                <button type="submit" class="btn">Отправить заявку</button>
            </form>
        </div>

        <!-- История покупок -->
        <div class="card">
            <h2>История покупок</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Спектакль</th>
                        <th>Дата</th>
                        <th>Кол-во</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="5" style="padding: 20px; text-align: center; color: #777;">У вас пока нет покупок.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $index => $order): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><?= $order['tickets_count'] ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="status-badge" style="background: <?= getStatusColor($order['status']) ?>;">
                                            <?= getStatusName($order['status']) ?>
                                        </span>
                                        <?php if ($order['status'] == 'new'): ?>
                                            <a href="payment.php?order_id=<?= $order['id'] ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Оплатить</a>
                                        <?php endif; ?>
                                    </div>
                                <td>
                                    <div class="status-progress">
                                        <!-- Шаг 1: Новая -->
                                        <div class="step <?= in_array($order['status'], ['new', 'paid']) ? 'active' : '' ?>">
                                            <div class="step-circle"></div>
                                            <span class="step-label">Создана</span>
                                        </div>
                                        <!-- Линия между шагами -->
                                        <div class="step-line <?= $order['status'] == 'paid' ? 'active' : '' ?>"></div>
                                        <!-- Шаг 2: Оплачена -->
                                        <div class="step <?= $order['status'] == 'paid' ? 'active' : '' ?>">
                                            <div class="step-circle"></div>
                                            <span class="step-label">Оплачена</span>
                                        </div>
                                    </div>
                                </td>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Модальное окно смены пароля -->
    <div id="passwordModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <span class="close-btn" onclick="document.getElementById('passwordModal').style.display='none'">&times;</span>
            <h3>Смена пароля</h3>

            <?= $pass_msg ?> <!-- Сюда выводится сообщение об успехе или ошибке -->

            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <button type="submit" class="btn" style="width: 100%;">Сохранить новый пароль</button>
            </form>
        </div>
    </div>
</body>
</html>