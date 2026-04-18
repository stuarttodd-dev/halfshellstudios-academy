<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/TaskRepository.php';

const ALLOWED_STATUSES = ['todo', 'doing', 'done'];

$repo = new TaskRepository(__DIR__ . '/../storage/tasks.json');
$action = $_GET['action'] ?? 'list';
$errors = [];
$flash = '';

function validateTaskInput(array $input): array
{
    $errors = [];
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? '');
    $dueDate = trim((string) ($input['due_date'] ?? ''));

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    }
    if (!in_array($status, ALLOWED_STATUSES, true)) {
        $errors['status'] = 'Status must be todo, doing, or done.';
    }
    if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $errors['due_date'] = 'Due date must be YYYY-MM-DD.';
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'list';

    if ($action === 'create') {
        $errors = validateTaskInput($_POST);
        if ($errors === []) {
            $repo->create([
                'title' => trim((string) $_POST['title']),
                'status' => (string) $_POST['status'],
                'due_date' => trim((string) ($_POST['due_date'] ?? '')),
            ]);
            header('Location: /?flash=created');
            exit;
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $errors = validateTaskInput($_POST);
        if ($errors === []) {
            $updated = $repo->update($id, [
                'title' => trim((string) $_POST['title']),
                'status' => (string) $_POST['status'],
                'due_date' => trim((string) ($_POST['due_date'] ?? '')),
            ]);
            header('Location: /?flash=' . ($updated ? 'updated' : 'notfound'));
            exit;
        }
        $action = 'edit';
        $_GET['id'] = (string) $id;
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $deleted = $repo->delete($id);
        header('Location: /?flash=' . ($deleted ? 'deleted' : 'notfound'));
        exit;
    }
}

$flashKey = $_GET['flash'] ?? '';
if ($flashKey === 'created') {
    $flash = 'Task created.';
} elseif ($flashKey === 'updated') {
    $flash = 'Task updated.';
} elseif ($flashKey === 'deleted') {
    $flash = 'Task deleted.';
} elseif ($flashKey === 'notfound') {
    $flash = 'Task not found.';
}

function renderForm(string $mode, array $task, array $errors): void
{
    $idInput = $mode === 'edit'
        ? '<input type="hidden" name="id" value="' . (int) $task['id'] . '">'
        : '';
    ?>
    <h2><?= $mode === 'edit' ? 'Edit Task' : 'Create Task' ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="<?= $mode === 'edit' ? 'update' : 'create' ?>">
        <?= $idInput ?>
        <div>
            <label>
                Title:
                <input name="title" value="<?= htmlspecialchars((string) ($task['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <?php if (isset($errors['title'])): ?><span style="color:#a00"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>
        <div>
            <label>
                Status:
                <select name="status">
                    <?php foreach (ALLOWED_STATUSES as $status): ?>
                        <option value="<?= $status ?>" <?= (($task['status'] ?? '') === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (isset($errors['status'])): ?><span style="color:#a00"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>
        <div>
            <label>
                Due date:
                <input name="due_date" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars((string) ($task['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <?php if (isset($errors['due_date'])): ?><span style="color:#a00"><?= htmlspecialchars($errors['due_date'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>
        <button type="submit"><?= $mode === 'edit' ? 'Save changes' : 'Create task' ?></button>
    </form>
    <?php
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CRUD Capstone</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 24px auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        .flash { padding: 10px; border: 1px solid #98cf98; background: #eef8ee; margin-bottom: 12px; }
    </style>
</head>
<body>
<h1>CRUD Capstone</h1>

<?php if ($flash !== ''): ?><div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php
if ($action === 'create') {
    $task = ['title' => (string) ($_POST['title'] ?? ''), 'status' => (string) ($_POST['status'] ?? 'todo'), 'due_date' => (string) ($_POST['due_date'] ?? '')];
    renderForm('create', $task, $errors);
    echo '<p><a href="/">Back to list</a></p>';
} elseif ($action === 'edit') {
    $id = (int) ($_GET['id'] ?? 0);
    $task = $repo->find($id);
    if ($task === null) {
        http_response_code(404);
        echo '<p>Task not found.</p><p><a href="/">Back to list</a></p>';
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $task = array_merge($task, [
                'title' => (string) ($_POST['title'] ?? $task['title']),
                'status' => (string) ($_POST['status'] ?? $task['status']),
                'due_date' => (string) ($_POST['due_date'] ?? $task['due_date']),
            ]);
        }
        renderForm('edit', $task, $errors);
        echo '<p><a href="/">Back to list</a></p>';
    }
} else {
    $tasks = $repo->all();
    echo '<p><a href="/?action=create">Create task</a></p>';
    if ($tasks === []) {
        echo '<p>No tasks yet.</p>';
    } else {
        echo '<table><thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Due date</th><th>Actions</th></tr></thead><tbody>';
        foreach ($tasks as $task) {
            $id = (int) $task['id'];
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . htmlspecialchars((string) $task['title'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string) $task['status'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string) ($task['due_date'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>';
            echo '<a href="/?action=edit&id=' . $id . '">Edit</a> ';
            echo '<form method="post" style="display:inline">';
            echo '<input type="hidden" name="action" value="delete">';
            echo '<input type="hidden" name="id" value="' . $id . '">';
            echo '<button type="submit">Delete</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}
?>
</body>
</html>
