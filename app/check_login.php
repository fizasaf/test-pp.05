<?php
// app/check_login.php
require 'db.php';

if (isset($_POST['login'])) {
    $login = trim($_POST['login']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $stmt->execute([$login]);
    if ($stmt->fetch()) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>