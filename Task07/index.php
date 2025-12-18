<?php
$currentYear = (int)date('Y');
$pdo = new PDO('sqlite:university.db');

$groupStmt = $pdo->query("SELECT group_number FROM groups WHERE graduation_year >= $currentYear");
$availableGroups = $groupStmt->fetchAll(PDO::FETCH_COLUMN);

$selectedGroup = $_GET['group_filter'] ?? '';

$sql = "SELECT g.group_number, g.major, s.full_name, s.gender, s.birth_date, s.student_card 
        FROM students s 
        JOIN groups g ON s.group_id = g.id 
        WHERE g.graduation_year >= :currYear";

if ($selectedGroup !== '' && in_array($selectedGroup, $availableGroups)) {
    $sql .= " AND g.group_number = :groupNum";
}

$sql .= " ORDER BY g.group_number, s.full_name";

$stmt = $pdo->prepare($sql);
$params = [':currYear' => $currentYear];
if ($selectedGroup !== '' && in_array($selectedGroup, $availableGroups)) {
    $params[':groupNum'] = $selectedGroup;
}
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Список студентов действующих групп</h1>

    <form method="GET">
        <label for="group_filter">Фильтр по группе:</label>
        <select name="group_filter" id="group_filter" onchange="this.form.submit()">
            <option value="">-- Все группы --</option>
            <?php foreach ($availableGroups as $group): ?>
                <option value="<?= htmlspecialchars($group) ?>" <?= $selectedGroup === $group ? 'selected' : '' ?>>
                    <?= htmlspecialchars($group) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit">Применить</button></noscript>
    </form>

    <?php if (empty($students)): ?>
        <p>Студенты не найдены.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>№ Группы</th>
                    <th>Направление</th>
                    <th>ФИО</th>
                    <th>Пол</th>
                    <th>Дата рождения</th>
                    <th>№ Билета</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['group_number']) ?></td>
                        <td><?= htmlspecialchars($student['major']) ?></td>
                        <td><?= htmlspecialchars($student['full_name']) ?></td>
                        <td><?= htmlspecialchars($student['gender']) ?></td>
                        <td><?= htmlspecialchars($student['birth_date']) ?></td>
                        <td><?= htmlspecialchars($student['student_card']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>