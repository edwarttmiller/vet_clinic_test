<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user_role'] === 'admin') {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Главная – ВетДоктор</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
    <h1>🐾 ВетДоктор</h1>
    <a href="logout.php">Выйти</a>
</div>

<div class="container page-content">
    <p style="font-size:14px; margin-bottom:15px; color:#555;">
        Привет, <strong><?= htmlspecialchars($_SESSION['user_login']) ?></strong>!
    </p>

    <!-- Слайдер -->
    <div class="slider">
        <div class="slider-track" id="sliderTrack">
            <div class="slide">🐱 Кошки</div>
            <div class="slide">🐶 Собаки</div>
            <div class="slide">🐦 Птицы</div>
            <div class="slide">🐹 Грызуны и рептилии</div>
        </div>
        <button class="slider-btn prev" id="prevBtn">&#8249;</button>
        <button class="slider-btn next" id="nextBtn">&#8250;</button>
    </div>

    <div class="card">
        <h3>Добро пожаловать!</h3>
        <p>Запишите вашего питомца на приём к ветеринарному врачу. Работаем каждый день.</p>
    </div>

    <a href="new_appointment.php" class="btn btn-primary" style="display:block; text-decoration:none; text-align:center;">
        + Записаться на приём
    </a>
</div>

<nav class="navbar">
    <a href="index.php" class="active"><span>🏠</span>Главная</a>
    <a href="appointments.php"><span>📋</span>Мои заявки</a>
    <a href="new_appointment.php"><span>➕</span>Запись</a>
</nav>

<script>
const track = document.getElementById('sliderTrack');
let current = 0;
const total = 4;

function goTo(n) {
    current = (n + total) % total;
    track.style.transform = 'translateX(-' + (current * 100) + '%)';
}

document.getElementById('prevBtn').addEventListener('click', () => goTo(current - 1));
document.getElementById('nextBtn').addEventListener('click', () => goTo(current + 1));
setInterval(() => goTo(current + 1), 3000);
</script>
</body>
</html>
