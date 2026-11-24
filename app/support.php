<?php
require 'db.php';
require 'helpers.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поддержка - Theatre MVP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .chat-box { height: 300px; overflow-y: auto; background: #12151e; border: 1px solid #333; border-radius: 8px; padding: 15px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px; }
        .msg { max-width: 80%; padding: 10px 15px; border-radius: 15px; font-size: 0.9rem; }
        .msg-user { background: #2a5b8e; align-self: flex-end; border-bottom-right-radius: 2px; }
        .msg-admin { background: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
        .msg-time { font-size: 0.7rem; color: #aaa; margin-top: 5px; display: block; text-align: right; }
        .chat-input-area { display: flex; gap: 10px; }
    </style>
</head>
<body>
    <header>
        <div class="logo">🎭 Theatre MVP</div>
        <nav>
            <a href="index.php">Главная</a>
            <a href="catalog.php">Афиша</a>
            <a href="cabinet.php">Личный кабинет</a>
        </nav>
    </header>
    <div class="container">
        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <h2>🛠 Чат с поддержкой</h2>
            <p style="color: #777; margin-bottom: 15px;">Задайте ваш вопрос, и администратор ответит вам в ближайшее время.</p>

            <div class="chat-box" id="chat-box">
                <!-- Сообщения будут загружаться сюда -->
                <p style="text-align: center; color: #555;">Загрузка сообщений...</p>
            </div>

            <form id="chat-form" class="chat-input-area">
                <input type="text" id="message-input" placeholder="Напишите сообщение..." required style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #333; background: #12151e; color: white;">
                <button type="submit" class="btn">Отправить</button>
            </form>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');

        // Функция загрузки сообщений
        function loadMessages() {
            fetch('api_chat.php')
                .then(res => res.json())
                .then(data => {
                    if (data.messages) {
                        chatBox.innerHTML = '';
                        data.messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.className = `msg ${msg.is_admin == 1 ? 'msg-admin' : 'msg-user'}`;
                            const date = new Date(msg.created_at);
                            const time = date.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                            div.innerHTML = `${msg.message} <span class="msg-time">${time}</span>`;
                            chatBox.appendChild(div);
                        });
                        chatBox.scrollTop = chatBox.scrollHeight; // Автопрокрутка вниз
                    }
                });
        }

        // Отправка сообщения
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text) return;

            const formData = new FormData();
            formData.append('message', text);

            fetch('api_chat.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                messageInput.value = '';
                loadMessages();
            });
        });

        // Запускаем загрузку каждые 2 секунды
        loadMessages();
        setInterval(loadMessages, 2000);
    </script>
</body>
</html>