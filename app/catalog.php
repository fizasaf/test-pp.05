<?php
require 'db.php';
require 'helpers.php';
session_start();

// --- ФИЛЬТРАЦИЯ ПО ЖАНРУ (ИСПРАВЛЕННЫЙ БЛОК) ---
$genre_filter = $_GET['genre'] ?? 'all';

if ($genre_filter === 'all') {
    $stmt = $pdo->query("SELECT * FROM performance ORDER BY id DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM performance WHERE genre = ? ORDER BY id DESC");
    $stmt->execute([$genre_filter]);
}
$performances = $stmt->fetchAll();

// Получаем список всех уникальных жанров для кнопок
// Используем GROUP BY, чтобы не было дублей (как у тебя на скрине две "Комедии")
$genres = $pdo->query("SELECT genre FROM performance WHERE genre IS NOT NULL AND genre != '' GROUP BY genre")->fetchAll(PDO::FETCH_COLUMN);
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
            <a href="catalog.php" style="color: #4a90e2;">Афиша</a>
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

        <!-- Кнопки фильтрации -->
        <div class="filters" style="margin-bottom: 20px;">
            <a href="catalog.php" class="btn-filter <?= $genre_filter == 'all' ? 'active' : '' ?>">Все</a>
            <?php foreach ($genres as $g): ?>
                <a href="catalog.php?genre=<?= urlencode($g) ?>" class="btn-filter <?= $genre_filter == $g ? 'active' : '' ?>">
                    <?= htmlspecialchars($g) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Сетка спектаклей -->
        <div class="catalog-grid">
            <?php if (empty($performances)): ?>
                <p style="color: #777; grid-column: 1 / -1;">В этой категории пока нет спектаклей.</p>
            <?php else: ?>
                <?php foreach ($performances as $perf): ?>
                    <div class="catalog-card">
                        <div class="catalog-cover" style="background: linear-gradient(135deg, #1e222d, <?= $perf['id'] % 2 == 0 ? '#8e44ad' : '#4a90e2' ?>);">
                            <span>🎭</span>
                        </div>
                        <div class="catalog-body">
                            <div class="catalog-badge"><?= htmlspecialchars($perf['genre']) ?></div>
                            <h3><?= htmlspecialchars($perf['title']) ?></h3>
                            <p class="catalog-desc"><?= htmlspecialchars($perf['description']) ?></p>

                            <div class="catalog-meta">
                                <span>📍 <?= htmlspecialchars($perf['venue'] ?? 'Основная сцена') ?></span>
                                <span>⏱ <?= $perf['duration_min'] ?> мин.</span>
                                <span>👤 <?= htmlspecialchars($perf['age_rating']) ?></span>
                            </div>

                            <div class="catalog-footer">
                                <div class="price"><?= number_format($perf['base_price'], 0, '', ' ') ?> ₽</div>
                                <a href="cabinet.php?performance_id=<?= $perf['id'] ?>" class="btn">Купить</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>