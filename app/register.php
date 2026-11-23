<?php
require 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($login) || empty($full_name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким логином уже существует.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_id = 2; // Пользователь

            $sql = "INSERT INTO users (login, password, full_name, phone, email, role_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$login, $hashed_password, $full_name, $phone, $email, $role_id])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Ошибка при регистрации.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav><a href="index.php">Главная</a> | <a href="login.php">Вход</a></nav>
    </header>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <h2>Регистрация</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>Логин</label><input type="text" name="login" required></div>
                <div class="form-group"><label>ФИО</label><input type="text" name="full_name" required></div>
                <div class="form-group"><label>Телефон</label><input type="text" name="phone" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
                <button type="submit" class="btn">Создать аккаунт</button>
            </form>
            <p style="margin-top: 15px; font-size: 0.9rem;">Уже есть аккаунт? <a href="login.php" style="color: #4a90e2;">Войти</a></p>
        </div>
    </div>
</body>
</html>
