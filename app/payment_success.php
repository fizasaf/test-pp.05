<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: cabinet.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата успешна - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-box { text-align: center; padding: 50px 20px; }
        .success-icon { font-size: 5rem; color: #27ae60; margin-bottom: 20px; animation: pop 0.5s ease-out; }
        @keyframes pop { 0% { transform: scale(0); } 80% { transform: scale(1.2); } 100% { transform: scale(1); } }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
    </header>
    <div class="container">
        <div class="card success-box">
            <div class="success-icon">✅</div>
            <h1>Оплата прошла успешно!</h1>
            <p style="color: #aaa; margin: 15px 0 30px;">Ваши билеты забронированы. Электронные билеты отправлены на вашу почту.</p>
            <a href="cabinet.php" class="btn" style="padding: 12px 30px; font-size: 1.1rem;">Вернуться в личный кабинет</a>
        </div>
    </div>
</body>
</html>