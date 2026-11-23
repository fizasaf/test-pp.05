<?php
// app/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <a href="index.php">Главная</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role_id'] == 1): ?>
                    <a href="admin.php">Админ-панель</a>
                <?php else: ?>
                    <a href="cabinet.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['full_name']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="container">
