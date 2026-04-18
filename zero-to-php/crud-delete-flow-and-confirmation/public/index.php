<?php
declare(strict_types=1);

session_start();
$_SESSION['rows'] ??= [
    ['id' => 1, 'title' => 'Plan sprint'],
    ['id' => 2, 'title' => 'Ship feature'],
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $before = count($_SESSION['rows']);
    $_SESSION['rows'] = array_values(array_filter(
        $_SESSION['rows'],
        static fn(array $row): bool => (int) $row['id'] !== $id
    ));
    $message = count($_SESSION['rows']) < $before ? "Deleted #{$id}." : "Record #{$id} not found.";
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>CRUD Delete Flow</title></head>
<body>
<h1>CRUD delete flow and confirmation</h1>
<?php if ($message !== ''): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<ul>
    <?php foreach ($_SESSION['rows'] as $row): ?>
        <li>
            #<?= (int) $row['id'] ?> <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?');">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button type="submit">Delete</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>
</body>
</html>
