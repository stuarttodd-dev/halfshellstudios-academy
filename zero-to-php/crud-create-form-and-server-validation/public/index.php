<?php
declare(strict_types=1);

const ALLOWED_STATUSES = ['todo', 'doing', 'done'];

function validateCreate(array $input): array
{
    $errors = [];
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? '');
    if ($title === '') {
        $errors['title'] = 'Title is required.';
    }
    if (!in_array($status, ALLOWED_STATUSES, true)) {
        $errors['status'] = 'Status is invalid.';
    }
    return $errors;
}

$errors = [];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateCreate($_POST);
    if ($errors === []) {
        $message = 'Record would be created.';
    }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>CRUD Create Validation</title></head>
<body>
<h1>CRUD create form and server validation</h1>
<?php if ($message !== ''): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post">
    <label>Title <input name="title" value="<?= htmlspecialchars((string) ($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
    <?php if (isset($errors['title'])): ?><p><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <label>Status
        <select name="status">
            <?php foreach (ALLOWED_STATUSES as $status): ?>
                <option value="<?= $status ?>" <?= (($_POST['status'] ?? 'todo') === $status) ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if (isset($errors['status'])): ?><p><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <button type="submit">Create</button>
</form>
</body>
</html>
