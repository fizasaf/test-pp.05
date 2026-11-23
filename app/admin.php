<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit;
}

// --- ОБРАБОТКА СМЕНЫ СТАТУСА (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        // Если запрос AJAX (из JavaScript), возвращаем JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Статус успешно обновлен!']);
            exit;
        }
        // Если обычный POST, просто перезагружаем
        header('Location: admin.php');
        exit;
    }
}

// --- ФИЛЬТРАЦИЯ И ПАГИНАЦИЯ ---
$status_filter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 5; // Количество заявок на страницу
$offset = ($page - 1) * $limit;

// Базовый запрос
$sql = "SELECT o.*, p.title, u.full_name, u.phone, u.email
        FROM orders o
        JOIN performance p ON o.performance_id = p.id
        JOIN users u ON o.user_id = u.id";

// Добавляем фильтр
$params = [];
if ($status_filter !== 'all') {
    $sql .= " WHERE o.status = ?";
    $params[] = $status_filter;
}

// Считаем общее количество для пагинации
$countSql = str_replace("SELECT o.*, p.title, u.full_name, u.phone, u.email", "SELECT COUNT(*)", $sql);
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$total_orders = $stmtCount->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Добавляем сортировку и лимиты
$sql .= " ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- Контейнер для всплывающих уведомлений -->
    <div id="toast-container" class="toast-container"></div>

    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
                <h2>Управление заявками</h2>

                <!-- Фильтры -->
                <div class="filters">
                    <a href="?status=all" class="btn-filter <?= $status_filter == 'all' ? 'active' : '' ?>">Все</a>
                    <a href="?status=new" class="btn-filter <?= $status_filter == 'new' ? 'active' : '' ?>">Новые</a>
                    <a href="?status=paid" class="btn-filter <?= $status_filter == 'paid' ? 'active' : '' ?>">Оплачены</a>
                    <a href="?status=cancelled" class="btn-filter <?= $status_filter == 'cancelled' ? 'active' : '' ?>">Отменены</a>
                </div>
            </div>

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
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="6" style="padding: 20px; text-align: center;">Заявок не найдено</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $index => $order): ?>
                            <tr style="border-bottom: 1px solid #2a2f3a;">
                                <td style="padding: 10px;"><?= $offset + $index + 1 ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?><br><small><?= htmlspecialchars($order['phone']) ?></small></td>
                                <td><?= htmlspecialchars($order['title']) ?></td>
                                <td><?= $order['tickets_count'] ?></td>
                                <td>
                                    <!-- Форма без перезагрузки страницы -->
                                    <form onsubmit="updateStatus(event, <?= $order['id'] ?>, this.status.value)" style="display: flex; gap: 5px;">
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
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?status=<?= $status_filter ?>&page=<?= $i ?>" class="page-link <?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Блок поддержки -->
        <div class="card" style="border: 1px solid #4a90e2;">
            <h3>🛠 Поддержка</h3>
            <p>Если у вас возникли технические неполадки, напишите нам:</p>
            <form method="POST">
                <div class="form-group">
                    <textarea rows="3" style="width: 100%; background: #12151e; color: white; border: 1px solid #333; padding: 10px; border-radius: 4px;" placeholder="Опишите проблему..."></textarea>
                </div>
                <button type="button" class="btn" onclick="showToast('Сообщение отправлено в техподдержку!', 'success')">Отправить в поддержку</button>
            </form>
        </div>
    </div>

    <script>
        // Функция AJAX-запроса для смены статуса
        function updateStatus(event, orderId, newStatus) {
            event.preventDefault(); // Отменяем стандартную отправку формы

            const formData = new FormData();
            formData.append('change_status', '1');
            formData.append('order_id', orderId);
            formData.append('status', newStatus);

            fetch('admin.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast('Ошибка при обновлении', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showToast('Ошибка сети', 'error');
            });
        }

        // Функция показа всплывающего уведомления (Toast)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;

            container.appendChild(toast);

            // Удаляем через 3 секунды
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>