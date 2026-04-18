<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/TaskRepository.php';

$repo = new TaskRepository(__DIR__ . '/../storage/tasks.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'todo');
    if ($title !== '' && in_array($status, ['todo', 'doing', 'done'], true)) {
        $repo->create($title, $status);
    }
    header('Location: /');
    exit;
}

$rows = $repo->all();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>CRUD Persistence Layer</title></head>
<body>
<h1>CRUD persistence layer and query safety</h1>
<form method="post">
    <input name="title" placeholder="Task title">
    <select name="status">
        <option value="todo">todo</option>
        <option value="doing">doing</option>
        <option value="done">done</option>
    </select>
    <button type="submit">Create</button>
</form>
<ul>
    <?php foreach ($rows as $row): ?>
        <li>#<?= (int) $row['id'] ?> <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>)</li>
    <?php endforeach; ?>
</ul>
</body>
</html>
