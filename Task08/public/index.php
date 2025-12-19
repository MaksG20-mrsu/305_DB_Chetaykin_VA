<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getConnection();

// Фильтрация
$groupId = isset($_GET['group_id']) && $_GET['group_id'] !== '' ? (int)$_GET['group_id'] : null;

$groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll();

$sql = "SELECT s.*, g.name AS group_name 
        FROM students s 
        JOIN groups g ON s.group_id = g.id";
if ($groupId) {
    $sql .= " WHERE s.group_id = :gid";
}
$sql .= " ORDER BY g.name ASC, s.last_name ASC";

$stmt = $pdo->prepare($sql);
if ($groupId) $stmt->bindValue(':gid', $groupId);
$stmt->execute();
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Студенты факультета</h1>
    
    <!-- Фильтр -->
    <form method="GET" class="filter-form">
        <label>Фильтр по группе:</label>
        <select name="group_id" onchange="this.form.submit()">
            <option value="">Все группы</option>
            <?php foreach($groups as $g): ?>
                <option value="<?= $g['id'] ?>" <?= $groupId == $g['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Группа</th>
                <th>Фамилия И.О.</th>
                <th>Пол</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['group_name']) ?></td>
                    <td><?= htmlspecialchars($s['last_name'] . ' ' . mb_substr($s['first_name'], 0, 1) . '.' . ($s['middle_name'] ? mb_substr($s['middle_name'], 0, 1).'.' : '')) ?></td>
                    <td><?= $s['gender'] == 'M' ? 'Муж' : 'Жен' ?></td>
                    <td class="actions">
                        <a href="exam_results.php?student_id=<?= $s['id'] ?>" class="btn btn-info">Результаты экзаменов</a>
                        <a href="student_form.php?id=<?= $s['id'] ?>" class="btn btn-edit">Редактировать</a>
                        <a href="delete.php?type=student&id=<?= $s['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить студента?');">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="student_form.php" class="btn btn-add">Добавить студента</a>
</div>
</body>
</html>