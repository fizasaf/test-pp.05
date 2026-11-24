<?php
// app/api_chat.php
require 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role_id'] == 1);

// 1. Отправка сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);

    // ДОБАВЬ ЭТИ ДВЕ СТРОКИ ДЛЯ ОТЛАДКИ
    $target_user_id = $is_admin && isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : $user_id;

    if (!empty($message)) {
        // И ИЗМЕНИ ЭТУ СТРОКУ, ЧТОБЫ ИСПОЛЬЗОВАТЬ target_user_id
        $stmt = $pdo->prepare("INSERT INTO support_messages (user_id, message, is_admin) VALUES (?, ?, ?)");
        $stmt->execute([$target_user_id, $message, $is_admin ? 1 : 0]);

        echo json_encode(['success' => true]);
    }
    exit;
}

// 2. Получение сообщений
$target_user_id = $user_id;
if ($is_admin && isset($_GET['user_id'])) {
    $target_user_id = (int)$_GET['user_id']; // Админ может смотреть переписку конкретного юзера
}

$stmt = $pdo->prepare("SELECT * FROM support_messages WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$target_user_id]);
$messages = $stmt->fetchAll();

echo json_encode(['messages' => $messages]);