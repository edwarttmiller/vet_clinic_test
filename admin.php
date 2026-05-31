<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$toastMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $allowedStatuses = ['new', 'confirmed', 'accepted', 'done'];
    $newStatus = $_POST['status'];
    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, (int)$_POST['appointment_id']]);
        $toastMsg = 'Статус изменён';
    }
    $query = http_build_query(array_filter([
        'status_filter' => $_POST['status_filter'] ?? '',
        'page'          => $_POST['page'] ?? 1,
        'toast'         => $toastMsg,
    ]));
    header('Location: admin.php?' . $query);
    exit;
}

$toastMsg     = $_GET['toast'] ?? '';
$statusFilter = $_GET['status_filter'] ?? '';
$allowedStatuses = ['new', 'confirmed', 'accepted', 'done'];

$perPage = 5;
$page    = max(1, (int)($_GET['page'] ?? 1));

$where  = '';
$params = [];
if ($statusFilter && in_array($statusFilter, $allowedStatuses)) {
    $where    = 'WHERE a.status = ?';
    $params[] = $statusFilter;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a $where");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$sql  = "SELECT a.*, u.full_name, u.phone FROM appointments a JOIN users u ON u.id = a.user_id $where ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$idx  = 1;
foreach ($params as $val) { $stmt->bindValue($idx++, $val); }
$stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($idx++, $offset,  PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'new'       => 'Новая',
    'confirmed' => 'Подтверждена',
    'accepted'  => 'Принята врачом',
    'done'      => 'Завершена',
];
$paymentLabels = ['cash' => 'Наличными', 'card' => 'Картой на месте'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Панель администратора – ВетДоктор</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="header">
    <h1>⚙️ Администратор</h1>
    <a href="logout.php">Выйти</a>
</div>

<div class="container page-content">
    <h2 class="page-title">Все заявки</h2>

    <form method="get" class="filter-row">
        <select name="status_filter" onchange="this.form.submit()">
            <option value="">Все статусы</option>
            <?php foreach ($statusLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $statusFilter === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (empty($appointments)): ?>
        <div class="alert alert-error">Нет заявок</div>
    <?php endif; ?>

    <?php foreach ($appointments as $a): ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <h3><?= htmlspecialchars($a['animal_type']) ?></h3>
                <span class="badge badge-<?= $a['status'] ?>"><?= $statusLabels[$a['status']] ?></span>
            </div>
            <p>👤 <?= htmlspecialchars($a['full_name']) ?></p>
            <p>📞 <?= htmlspecialchars($a['phone']) ?></p>
            <p>📝 <?= htmlspecialchars($a['complaint']) ?></p>
            <p>📅 <?= date('d.m.Y', strtotime($a['visit_date'])) ?></p>
            <p>💳 <?= $paymentLabels[$a['payment_method']] ?></p>

            <form method="post" style="margin-top:10px;">
                <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="status_filter"  value="<?= htmlspecialchars($statusFilter) ?>">
                <input type="hidden" name="page"           value="<?= $page ?>">
                <select name="status" class="status-select">
                    <?php foreach ($statusLabels as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= $a['status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" style="margin-left:8px; padding:5px 12px; background:#2e7d32; color:#fff; border:none; border-radius:5px; font-size:13px; cursor:pointer;">
                    Сохранить
                </button>
            </form>
        </div>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&status_filter=<?= urlencode($statusFilter) ?>">&#8249;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&status_filter=<?= urlencode($statusFilter) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>&status_filter=<?= urlencode($statusFilter) ?>">&#8250;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div class="toast" id="toast"><?= htmlspecialchars($toastMsg) ?></div>

<script>
<?php if ($toastMsg): ?>
const toast = document.getElementById('toast');
toast.classList.add('show');
setTimeout(() => toast.classList.remove('show'), 3000);
<?php endif; ?>
</script>
</body>
</html>
