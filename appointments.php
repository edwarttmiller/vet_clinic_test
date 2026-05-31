<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'], $_POST['appointment_id'])) {
    $apptId     = (int)$_POST['appointment_id'];
    $reviewText = trim($_POST['review_text']);

    $stmt = $pdo->prepare('SELECT id FROM appointments WHERE id = ? AND user_id = ? AND status = "done"');
    $stmt->execute([$apptId, $userId]);
    if ($stmt->fetch() && $reviewText !== '') {
        $stmt2 = $pdo->prepare('SELECT id FROM reviews WHERE appointment_id = ?');
        $stmt2->execute([$apptId]);
        if (!$stmt2->fetch()) {
            $stmt3 = $pdo->prepare('INSERT INTO reviews (appointment_id, user_id, review_text) VALUES (?, ?, ?)');
            $stmt3->execute([$apptId, $userId, $reviewText]);
        }
    }
    header('Location: appointments.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT a.*, r.review_text
    FROM appointments a
    LEFT JOIN reviews r ON r.appointment_id = a.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
');
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'new'       => ['label' => 'Новая',        'class' => 'badge-new'],
    'confirmed' => ['label' => 'Подтверждена', 'class' => 'badge-confirmed'],
    'accepted'  => ['label' => 'Принята врачом','class' => 'badge-accepted'],
    'done'      => ['label' => 'Завершена',    'class' => 'badge-done'],
];
$paymentLabels = ['cash' => 'Наличными', 'card' => 'Картой на месте'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Мои заявки – ВетДоктор</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
    <h1>🐾 ВетДоктор</h1>
    <a href="logout.php">Выйти</a>
</div>

<div class="container page-content">
    <h2 class="page-title">Мои заявки</h2>

    <?php if (empty($appointments)): ?>
        <div class="alert alert-error">У вас пока нет заявок. <a href="new_appointment.php">Записаться</a></div>
    <?php endif; ?>

    <?php foreach ($appointments as $a): ?>
        <?php $st = $statusLabels[$a['status']]; ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3><?= htmlspecialchars($a['animal_type']) ?></h3>
                <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
            </div>
            <p>📝 <?= htmlspecialchars($a['complaint']) ?></p>
            <p>📅 <?= date('d.m.Y', strtotime($a['visit_date'])) ?></p>
            <p>💳 <?= $paymentLabels[$a['payment_method']] ?></p>
            <p style="font-size:11px; color:#aaa;">Создана: <?= date('d.m.Y H:i', strtotime($a['created_at'])) ?></p>

            <?php if ($a['status'] === 'done'): ?>
                <?php if ($a['review_text']): ?>
                    <div style="margin-top:10px; background:#f0f7f4; border-radius:6px; padding:8px;">
                        <p style="font-size:12px; color:#2e7d32; font-weight:bold;">Ваш отзыв:</p>
                        <p style="font-size:13px;"><?= htmlspecialchars($a['review_text']) ?></p>
                    </div>
                <?php else: ?>
                    <div class="review-form">
                        <form method="post">
                            <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                            <textarea name="review_text" placeholder="Оставьте отзыв об услуге..."></textarea>
                            <button type="submit">Отправить отзыв</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<nav class="navbar">
    <a href="index.php"><span>🏠</span>Главная</a>
    <a href="appointments.php" class="active"><span>📋</span>Мои заявки</a>
    <a href="new_appointment.php"><span>➕</span>Запись</a>
</nav>
</body>
</html>
