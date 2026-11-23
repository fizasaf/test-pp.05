<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role_id'] == 1): ?>
                    <a href="admin.php">Админ-панель</a>
                <?php else: ?>
                    <a href="cabinet.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход</a> | <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container" style="text-align: center; margin-top: 50px;">
        <h1>Добро пожаловать в театр!</h1>
        <p>Здесь вы можете забронировать билеты на лучшие спектакли.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn" style="display: inline-block; margin-top: 20px; text-decoration: none;">Зарегистрироваться</a>
        <?php endif; ?>
    </div>
</body>
</html>
