<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal  = trim($_POST['animal_type'] ?? '');
    $complaint = trim($_POST['complaint'] ?? '');
    $date    = trim($_POST['visit_date'] ?? '');
    $payment = trim($_POST['payment_method'] ?? '');

    $allowedAnimals = ['Кошка', 'Собака', 'Птица', 'Грызун', 'Рептилия'];

    if (!in_array($animal, $allowedAnimals)) {
        $errors['animal_type'] = 'Выберите вид животного';
    }
    if ($complaint === '') {
        $errors['complaint'] = 'Опишите жалобу';
    }
    if (!$date) {
        $errors['visit_date'] = 'Укажите дату';
    } elseif ($date < date('Y-m-d')) {
        $errors['visit_date'] = 'Дата не может быть в прошлом';
    }
    if (!in_array($payment, ['cash', 'card'])) {
        $errors['payment_method'] = 'Выберите способ оплаты';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO appointments (user_id, animal_type, complaint, visit_date, payment_method) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $animal, $complaint, $date, $payment]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Новая заявка – ВетДоктор</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
    <h1>🐾 ВетДоктор</h1>
    <a href="logout.php">Выйти</a>
</div>

<div class="container page-content">
    <h2 class="page-title">Новая заявка</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Заявка отправлена администратору!</div>
    <?php endif; ?>

    <form method="post" id="apptForm" novalidate>

        <div class="form-group">
            <label>Вид животного *</label>
            <select name="animal_type">
                <option value="">-- Выберите --</option>
                <?php foreach (['Кошка','Собака','Птица','Грызун','Рептилия'] as $a): ?>
                    <option value="<?= $a ?>" <?= (($_POST['animal_type'] ?? '') === $a) ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
            <div class="error-msg <?= isset($errors['animal_type']) ? 'show' : '' ?>" id="err-animal">
                <?= $errors['animal_type'] ?? 'Выберите вид животного' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Жалоба / симптомы *</label>
            <textarea name="complaint"><?= htmlspecialchars($_POST['complaint'] ?? '') ?></textarea>
            <div class="error-msg <?= isset($errors['complaint']) ? 'show' : '' ?>" id="err-complaint">
                <?= $errors['complaint'] ?? 'Опишите жалобу' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Дата приёма *</label>
            <input type="date" name="visit_date" value="<?= htmlspecialchars($_POST['visit_date'] ?? '') ?>">
            <div class="error-msg <?= isset($errors['visit_date']) ? 'show' : '' ?>" id="err-date">
                <?= $errors['visit_date'] ?? 'Укажите дату' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Способ оплаты *</label>
            <select name="payment_method">
                <option value="">-- Выберите --</option>
                <option value="cash" <?= (($_POST['payment_method'] ?? '') === 'cash') ? 'selected' : '' ?>>Наличными</option>
                <option value="card" <?= (($_POST['payment_method'] ?? '') === 'card') ? 'selected' : '' ?>>Картой на месте</option>
            </select>
            <div class="error-msg <?= isset($errors['payment_method']) ? 'show' : '' ?>" id="err-payment">
                <?= $errors['payment_method'] ?? 'Выберите способ оплаты' ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Отправить</button>
    </form>
</div>

<nav class="navbar">
    <a href="index.php"><span>🏠</span>Главная</a>
    <a href="appointments.php"><span>📋</span>Мои заявки</a>
    <a href="new_appointment.php" class="active"><span>➕</span>Запись</a>
</nav>

<script>
document.getElementById('apptForm').addEventListener('submit', function(e) {
    let ok = true;

    const animal = document.querySelector('[name="animal_type"]');
    const errAnimal = document.getElementById('err-animal');
    if (!animal.value) {
        errAnimal.classList.add('show'); ok = false;
    } else { errAnimal.classList.remove('show'); }

    const complaint = document.querySelector('[name="complaint"]');
    const errComplaint = document.getElementById('err-complaint');
    if (!complaint.value.trim()) {
        errComplaint.classList.add('show'); ok = false;
    } else { errComplaint.classList.remove('show'); }

    const date = document.querySelector('[name="visit_date"]');
    const errDate = document.getElementById('err-date');
    if (!date.value) {
        errDate.textContent = 'Укажите дату';
        errDate.classList.add('show'); ok = false;
    } else {
        const today = new Date(); today.setHours(0,0,0,0);
        if (new Date(date.value) < today) {
            errDate.textContent = 'Дата не может быть в прошлом';
            errDate.classList.add('show'); ok = false;
        } else { errDate.classList.remove('show'); }
    }

    const payment = document.querySelector('[name="payment_method"]');
    const errPayment = document.getElementById('err-payment');
    if (!payment.value) {
        errPayment.classList.add('show'); ok = false;
    } else { errPayment.classList.remove('show'); }

    if (!ok) e.preventDefault();
});
</script>
</body>
</html>
