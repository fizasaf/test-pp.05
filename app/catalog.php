<?php
require 'db.php';
session_start();

// Получаем все спектакли из БД
$stmt = $pdo->query("SELECT * FROM performance");
$performances = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог спектаклей - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <a href="index.php">Главная</a>
            <a href="catalog.php">Афиша</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role_id'] == 1): ?>
                    <a href="admin.php">Админ-панель</a>
                <?php else: ?>
                    <a href="cabinet.php">Личный кабинет</a>
                <?php endif; ?>
                <a href="logout.php" style="color: #ff5252;">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h2>Афиша спектаклей</h2>
        <div class="catalog-grid">
            <?php foreach ($performances as $perf): ?>
                <div class="catalog-card">
                    <!-- Плашка с жанром -->
                    <div class="catalog-badge"><?= htmlspecialchars($perf['genre']) ?></div>

                    <h3><?= htmlspecialchars($perf['title']) ?></h3>
                    <p class="catalog-desc"><?= htmlspecialchars($perf['description']) ?></p>

                    <div class="catalog-meta">
                        <span>⏱ <?= $perf['duration_min'] ?> мин.</span>
                        <span>🔞 <?= htmlspecialchars($perf['age_rating']) ?></span>
                    </div>

                    <div class="catalog-footer">
                        <div class="price"><?= number_format($perf['base_price'], 0, '', ' ') ?> ₽</div>
                        <a href="cabinet.php?performance_id=<?= $perf['id'] ?>" class="btn">Купить</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>