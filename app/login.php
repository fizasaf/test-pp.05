<?php
require 'db.php';
session_start();

$error = '';
if (isset($_GET['registered'])) {
    $success = 'Регистрация успешна! Войдите в систему.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['full_name'] = $user['full_name'];
        
        if ($user['role_id'] == 1) {
            header('Location: admin.php');
        } else {
            header('Location: cabinet.php');
        }
        exit;
    } else {
        $error = 'Неверный логин или пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav><a href="index.php">Главная</a> | <a href="register.php">Регистрация</a></nav>
    </header>
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <h2>Вход</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>Логин (Email)</label><input type="text" name="login" required></div>
                <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
                <button type="submit" class="btn">Войти</button>
            </form>
            <p style="margin-top: 15px; font-size: 0.9rem;">Нет аккаунта? <a href="register.php" style="color: #4a90e2;">Регистрация</a></p>
        </div>
    </div>
</body>
</html>
