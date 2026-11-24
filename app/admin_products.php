<?php
require 'db.php';
require 'helpers.php';
session_start();

// Проверка на админа
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: login.php');
    exit;
}

$message = '';

// 1. Обработка добавления товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $duration = (int)$_POST['duration_min'];
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $age_rating = trim($_POST['age_rating']);
    $price = (float)$_POST['base_price'];

    if (!empty($title) && $duration > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO performance (title, duration_min, description, genre, age_rating, base_price) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $duration, $description, $genre, $age_rating, $price])) {
            $message = '<div class="alert alert-success" role="alert">Спектакль успешно добавлен!</div>';
        } else {
            $message = '<div class="alert alert-error" role="alert">Ошибка при добавлении.</div>';
        }
    } else {
        $message = '<div class="alert alert-error" role="alert">Заполните обязательные поля корректно.</div>';
    }
}

// 2. Обработка удаления товара
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    // Проверяем, есть ли заявки на этот спектакль, чтобы не сломать БД
    $check = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE performance_id = ?");
    $check->execute([$delete_id]);
    if ($check->fetchColumn() > 0) {
        $message = '<div class="alert alert-error" role="alert">Нельзя удалить: на этот спектакль уже есть заявки.</div>';
    } else {
        $stmt = $pdo->prepare("DELETE FROM performance WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: admin_products.php");
        exit;
    }
}

// Получаем все спектакли
$performances = $pdo->query("SELECT * FROM performance ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление афишей - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP (Админ)</div>
        <nav>
            <a href="admin.php">Заявки</a>
            <a href="admin_products.php" style="color: #4a90e2;">Афиша</a>
            <a href="index.php">На сайт</a>
            <a href="logout.php" style="color: #ff5252;">Выйти</a>
        </nav>
    </header>
    <div class="container">

        <?= $message ?>

        <!-- Форма добавления -->
        <div class="card">
            <h2>Добавить спектакль</h2>
            <form method="POST" class="form-row" style="flex-wrap: wrap; align-items: flex-start;">
                <input type="hidden" name="add_product" value="1">

                <div class="form-group" style="flex: 1 1 300px;">
                    <label>Название *</label>
                    <input type="text" name="title" required placeholder="Например: Лебединое озеро">
                </div>
                <div class="form-group" style="flex: 0 1 150px;">
                    <label>Длительность (мин) *</label>
                    <input type="number" name="duration_min" required min="1" value="120">
                </div>
                <div class="form-group" style="flex: 0 1 150px;">
                    <label>Цена (руб) *</label>
                    <input type="number" name="base_price" required min="0" step="0.01" value="1500">
                </div>
                <div class="form-group" style="flex: 1 1 200px;">
                    <label>Жанр</label>
                    <input type="text" name="genre" placeholder="Балет, Комедия...">
                </div>
                <div class="form-group" style="flex: 0 1 100px;">
                    <label>Возраст</label>
                    <input type="text" name="age_rating" placeholder="6+">
                </div>
                <div class="form-group" style="flex: 1 1 100%;">
                    <label>Описание</label>
                    <textarea name="description" rows="2" style="width: 100%; background: var(--bg-color); color: white; border: 1px solid #333; padding: 10px; border-radius: 4px;"></textarea>
                </div>
                <div class="form-group" style="flex: 1 1 100%;">
                    <button type="submit" class="btn">Добавить в афишу</button>
                </div>
            </form>
        </div>

        <!-- Список товаров -->
        <div class="card">
            <h2>Текущая афиша</h2>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th>ID</th>
                        <th>Название</th>
                        <th>Жанр</th>
                        <th>Цена</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($performances as $perf): ?>
                        <tr style="border-bottom: 1px solid #2a2f3a;">
                            <td><?= $perf['id'] ?></td>
                            <td><?= htmlspecialchars($perf['title']) ?></td>
                            <td><?= htmlspecialchars($perf['genre']) ?></td>
                            <td><?= number_format($perf['base_price'], 0, '', ' ') ?> ₽</td>
                            <td>
                                <a href="?delete_id=<?= $perf['id'] ?>" class="btn" style="background: #d32f2f; padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Удалить спектакль?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>