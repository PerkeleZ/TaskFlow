<?php
$filename = 'tasks.json';

if (!file_exists($filename)) {
    file_put_contents($filename, '[]');
}

$tasks = json_decode(file_get_contents($filename), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');

        if ($title !== '') {
            $tasks[] = [
                'title' => $title,
                'done' => false
            ];
        }
    }

    if ($action === 'toggle') {
        $id = (int) $_POST['id'];

        if (isset($tasks[$id])) {
            $tasks[$id]['done'] = !$tasks[$id]['done'];
        }
    }

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        unset($tasks[$id]);
        $tasks = array_values($tasks);
    }

    file_put_contents(
        $filename,
        json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TaskFlow</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 650px;
            margin: 50px auto;
            padding: 20px;
            background: #f1f3f8;
        }

        h1 { color: #304cc7; }

        .task {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 10px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
        }

        .title { flex: 1; }

        .done {
            text-decoration: line-through;
            color: gray;
        }

        button {
            padding: 8px 12px;
            cursor: pointer;
        }

        input[type="text"] {
            padding: 10px;
            width: 65%;
        }
    </style>
</head>
<body>

<h1>TaskFlow</h1>
<p>Простой менеджер задач на PHP</p>

<form method="post">
    <input type="hidden" name="action" value="add">
    <input type="text" name="title"
           placeholder="Введите задачу..." required>
    <button type="submit">Добавить</button>
</form>

<hr>

<?php if (empty($tasks)): ?>
    <p>Пока задач нет. Добавь первую!</p>
<?php endif; ?>

<?php foreach ($tasks as $id => $task): ?>
    <div class="task">
        <span class="title <?= $task['done'] ? 'done' : '' ?>">
            <?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>
        </span>

        <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit">
                <?= $task['done'] ? 'Вернуть' : 'Готово' ?>
            </button>
        </form>

        <form method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit">Удалить</button>
        </form>
    </div>
<?php endforeach; ?>

</body>
</html>