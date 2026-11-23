<?php
require 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($login) || empty($full_name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения.';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким логином уже существует.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_id = 2;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <!-- role="alert" для скринридеров -->
                <div class="alert alert-error" role="alert"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="login">Логин</label>
                    <!-- aria-describedby связывает поле с текстом ошибки для незрячих -->
                    <input type="text" id="login" name="login" required aria-describedby="login-error">
                    <div id="login-error" class="field-error" role="alert"></div>
                </div>

                <div class="form-group">
                    <label for="full_name">ФИО</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="text" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #777; display: block; margin-top: 5px;">Минимум 6 символов</small>
                </div>

                <button type="submit" class="btn">Создать аккаунт</button>
            </form>
            <p style="margin-top: 15px; font-size: 0.9rem;">Уже есть аккаунт? <a href="login.php" style="color: #4a90e2;">Войти</a></p>
        </div>
    </div>

    <script>
        // Мгновенная проверка логина при потере фокуса
        document.getElementById('login').addEventListener('blur', function() {
            const loginInput = this;
            const errorDiv = document.getElementById('login-error');
            const loginValue = loginInput.value.trim();

            if (loginValue === '') {
                errorDiv.textContent = 'Логин не может быть пустым';
                loginInput.setAttribute('aria-invalid', 'true');
                return;
            }

            // AJAX запрос
            fetch('check_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'login=' + encodeURIComponent(loginValue)
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    errorDiv.textContent = 'Этот логин уже занят';
                    loginInput.setAttribute('aria-invalid', 'true');
                    loginInput.style.borderColor = '#d32f2f';
                } else {
                    errorDiv.textContent = '';
                    loginInput.setAttribute('aria-invalid', 'false');
                    loginInput.style.borderColor = '';
                }
            })
            .catch(err => console.error('Ошибка проверки:', err));
        });
    </script>
</body>
</html>