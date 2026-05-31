<?php
session_start();
require_once 'config.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors['login'] = 'Логин: латиница и цифры, минимум 6 символов';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Пароль: минимум 8 символов';
    }
    if (!preg_match('/^[а-яёА-ЯЁ\s]+$/u', $fullName)) {
        $errors['full_name'] = 'ФИО: только кириллица и пробелы';
    }
    if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors['phone'] = 'Телефон: формат 8(ХХХ)ХХХ-ХХ-ХХ';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Такой логин уже занят';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (login, password, full_name, phone, email) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$login, $hash, $fullName, $phone, $email]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Регистрация – ВетДоктор</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
    <h1>🐾 ВетДоктор</h1>
</div>

<div class="container">
    <h2 class="page-title">Регистрация</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Пользователь создан! <a href="login.php">Войдите</a></div>
    <?php endif; ?>

    <form method="post" id="regForm" novalidate>

        <div class="form-group">
            <label>Логин *</label>
            <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            <div class="error-msg <?= isset($errors['login']) ? 'show' : '' ?>">
                <?= $errors['login'] ?? 'Логин: латиница и цифры, минимум 6 символов' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Пароль *</label>
            <input type="password" name="password">
            <div class="error-msg <?= isset($errors['password']) ? 'show' : '' ?>">
                <?= $errors['password'] ?? 'Минимум 8 символов' ?>
            </div>
        </div>

        <div class="form-group">
            <label>ФИО *</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            <div class="error-msg <?= isset($errors['full_name']) ? 'show' : '' ?>">
                <?= $errors['full_name'] ?? 'Только кириллица и пробелы' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Телефон *</label>
            <input type="text" name="phone" placeholder="8(ХХХ)ХХХ-ХХ-ХХ" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            <div class="error-msg <?= isset($errors['phone']) ? 'show' : '' ?>">
                <?= $errors['phone'] ?? 'Формат: 8(ХХХ)ХХХ-ХХ-ХХ' ?>
            </div>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <div class="error-msg <?= isset($errors['email']) ? 'show' : '' ?>">
                <?= $errors['email'] ?? 'Введите корректный email' ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Создать пользователя</button>
    </form>

    <div class="link-row">
        Уже зарегистрированы? <a href="login.php">Войти</a>
    </div>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', function(e) {
    let ok = true;
    const fields = [
        { name: 'login',     pattern: /^[a-zA-Z0-9]{6,}$/,            msg: 'Логин: латиница и цифры, минимум 6 символов' },
        { name: 'password',  pattern: /^.{8,}$/,                       msg: 'Минимум 8 символов' },
        { name: 'full_name', pattern: /^[а-яёА-ЯЁ\s]+$/u,             msg: 'Только кириллица и пробелы' },
        { name: 'phone',     pattern: /^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/, msg: 'Формат: 8(ХХХ)ХХХ-ХХ-ХХ' },
        { name: 'email',     pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,    msg: 'Некорректный email' },
    ];
    fields.forEach(f => {
        const input = document.querySelector('[name="' + f.name + '"]');
        const err   = input.nextElementSibling;
        if (!f.pattern.test(input.value)) {
            err.textContent = f.msg;
            err.classList.add('show');
            ok = false;
        } else {
            err.classList.remove('show');
        }
    });
    if (!ok) e.preventDefault();
});
</script>
</body>
</html>
