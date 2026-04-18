<?php
declare(strict_types=1);

$records = [
    ['id' => 1, 'title' => 'Plan sprint', 'status' => 'todo'],
    ['id' => 2, 'title' => 'Write docs', 'status' => 'doing'],
];

$action = $_GET['action'] ?? 'list';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>CRUD Routing</title></head>
<body>
<h1>CRUD routing and list screen</h1>
<nav>
    <a href="/?action=list">List</a> |
    <a href="/?action=create">Create form</a> |
    <a href="/?action=edit&id=1">Edit form</a>
</nav>
<?php if ($action === 'list'): ?>
    <ul>
        <?php foreach ($records as $record): ?>
            <li>#<?= (int) $record['id'] ?> <?= htmlspecialchars($record['title'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8') ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($action === 'create'): ?>
    <p>Create form route works.</p>
<?php elseif ($action === 'edit'): ?>
    <p>Edit form route works for id <?= (int) ($_GET['id'] ?? 0) ?>.</p>
<?php else: ?>
    <?php http_response_code(404); ?>
    <p>Not found</p>
<?php endif; ?>
</body>
</html>
