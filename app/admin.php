<?php
require 'db.php';
require 'helpers.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit;
}

// --- ОБРАБОТКА ОТВЕТА ИЗ ЧАТА (ПРОСТОЙ POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_message'])) {
    $target_user_id = (int)$_POST['target_user_id'];
    $message = trim($_POST['admin_message']);

    if (!empty($message) && $target_user_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO support_messages (user_id, message, is_admin) VALUES (?, ?, 1)");
        $stmt->execute([$target_user_id, $message]);

        // Перезагружаем страницу, чтобы сообщение появилось
        header("Location: admin.php?chat_user=" . $target_user_id);
        exit;
    }
}

// --- ОБРАБОТКА СМЕНЫ СТАТУСА (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Статус успешно обновлен!']);
            exit;
        }
        header('Location: admin.php');
        exit;
    }
}

// --- ФИЛЬТРАЦИЯ И ПАГИНАЦИЯ ---
$status_filter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT o.*, p.title, u.full_name, u.phone, u.email
        FROM orders o
        JOIN performance p ON o.performance_id = p.id
        JOIN users u ON o.user_id = u.id";

$params = [];
if ($status_filter !== 'all') {
    $sql .= " WHERE o.status = ?";
    $params[] = $status_filter;
}

$countSql = str_replace("SELECT o.*, p.title, u.full_name, u.phone, u.email", "SELECT COUNT(*)", $sql);
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$total_orders = $stmtCount->fetchColumn();
$total_pages = ceil($total_orders / $limit);

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
            <a href="admin.php" style="color: #4a90e2;">Заявки</a>
            <a href="admin_products.php">Афиша</a>
            <a href="index.php">На сайт</a>
            <span style="margin: 0 15px;">Здравствуйте, <?= htmlspecialchars($_SESSION['full_name']) ?>!</span>
            <a href="logout.php" style="color: #ff5252;">Выйти</a>
        </nav>
    </header>

    <div id="toast-container" class="toast-container"></div>

    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
                <h2>Управление заявками</h2>

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
                                    <form onsubmit="updateStatus(event, <?= $order['id'] ?>, this.status.value)" style="display: flex; gap: 5px;">
                                        <select name="status" style="padding: 5px; background: #12151e; color: white; border: 1px solid #333; border-radius: 4px;">
                                            <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>><?= getStatusName('new') ?></option>
                                            <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>><?= getStatusName('paid') ?></option>
                                            <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>><?= getStatusName('cancelled') ?></option>
                                        </select>
                                        <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">OK</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?status=<?= $status_filter ?>&page=<?= $i ?>" class="page-link <?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ЧАТ ПОДДЕРЖКИ -->
        <div class="card chat-container-card">
            <h3>💬 Чат поддержки</h3>

            <?php
            $chatUsers = $pdo->query("SELECT DISTINCT u.id, u.full_name FROM users u JOIN support_messages sm ON u.id = sm.user_id WHERE sm.is_admin = 0")->fetchAll();
            ?>

            <div class="chat-layout">
                <div class="chat-sidebar">
                    <div class="chat-sidebar-header">Пользователи</div>
                    <?php foreach ($chatUsers as $cu): ?>
                        <a href="admin.php?chat_user=<?= $cu['id'] ?>" class="chat-user-item" style="text-decoration: none; color: white;">
                            <div class="chat-avatar">👤</div>
                            <div class="chat-user-name"><?= htmlspecialchars($cu['full_name']) ?></div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($chatUsers)): ?>
                        <p style="padding: 15px; color: #777; font-size: 0.9rem; text-align: center;">Нет сообщений</p>
                    <?php endif; ?>
                </div>

                <div class="chat-main">
                    <div class="chat-main-header" id="admin-chat-title">
                        <?php
                        if (isset($_GET['chat_user'])) {
                            $userStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                            $userStmt->execute([(int)$_GET['chat_user']]);
                            $chatUserName = $userStmt->fetchColumn();
                            echo "Чат с " . htmlspecialchars($chatUserName);
                        } else {
                            echo "Выберите пользователя";
                        }
                        ?>
                    </div>

                    <div id="admin-chat-box" class="chat-messages">
                        <?php
                        if (isset($_GET['chat_user'])) {
                            $chat_user_id = (int)$_GET['chat_user'];
                            $msgStmt = $pdo->prepare("SELECT * FROM support_messages WHERE user_id = ? ORDER BY created_at ASC");
                            $msgStmt->execute([$chat_user_id]);
                            $messages = $msgStmt->fetchAll();

                            foreach ($messages as $msg) {
                                $class = $msg['is_admin'] ? 'msg-admin' : 'msg-user';
                                $time = date('H:i', strtotime($msg['created_at']));
                                echo "<div class='msg {$class}'>" . htmlspecialchars($msg['message']) . "<span class='msg-time'>{$time}</span></div>";
                            }
                        } else {
                            echo "<p style='text-align: center; color: #777;'>Выберите пользователя слева</p>";
                        }
                        ?>
                    </div>

                    <form method="POST" action="admin.php" class="chat-input-area" style="display: <?= isset($_GET['chat_user']) ? 'flex' : 'none'; ?>;">
                        <input type="hidden" name="target_user_id" value="<?= $_GET['chat_user'] ?? '' ?>">
                        <input type="text" name="admin_message" placeholder="Напишите ответ..." required style="flex: 1; padding: 12px 15px; border-radius: 20px; border: 1px solid #333; background: var(--bg-color); color: white;">
                        <button type="submit" class="btn">Ответить</button>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- Конец container -->

    <script>
        function updateStatus(event, orderId, newStatus) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('change_status', '1');
            formData.append('order_id', orderId);
            formData.append('status', newStatus);

            fetch('admin.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Автопрокрутка чата вниз при загрузке
        const chatBox = document.getElementById('admin-chat-box');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</body>
</html>