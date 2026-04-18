<?php
declare(strict_types=1);

$records = [
    ['id' => 1, 'title' => 'Plan sprint', 'status' => 'todo'],
    ['id' => 2, 'title' => 'Ship feature', 'status' => 'doing'],
];

$id = (int) ($_GET['id'] ?? 1);
$record = null;
foreach ($records as $row) {
    if ((int) $row['id'] === $id) {
        $record = $row;
        break;
    }
}

if ($record === null) {
    http_response_code(404);
    echo 'Record not found.';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $status = (string) ($_POST['status'] ?? '');
    $record['title'] = $title === '' ? $record['title'] : $title;
    $record['status'] = in_array($status, ['todo', 'doing', 'done'], true) ? $status : $record['status'];
    $message = 'Record updated in memory.';
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>CRUD Edit Flow</title></head>
<body>
<h1>CRUD edit flow and prefilled form</h1>
<?php if ($message !== ''): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post">
    <label>Title <input name="title" value="<?= htmlspecialchars($record['title'], ENT_QUOTES, 'UTF-8') ?>"></label>
    <label>Status
        <select name="status">
            <?php foreach (['todo', 'doing', 'done'] as $status): ?>
                <option value="<?= $status ?>" <?= ($record['status'] === $status) ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Save</button>
</form>
</body>
</html>
