<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getConnection();

$studentId = $_GET['student_id'] ?? null;
if (!$studentId) die("Студент не выбран");

$stmt = $pdo->prepare("SELECT s.*, g.name as group_name FROM students s JOIN groups g ON s.group_id = g.id WHERE s.id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT e.*, sub.name as subject_name, sub.course, sub.semester 
    FROM exams e 
    JOIN subjects sub ON e.subject_id = sub.id 
    WHERE e.student_id = ? 
    ORDER BY e.exam_date ASC
");
$stmt->execute([$studentId]);
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экзамены: <?= htmlspecialchars($student['last_name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Результаты экзаменов</h2>
    <p>Студент: <strong><?= htmlspecialchars($student['last_name'] . ' ' . $student['first_name']) ?></strong> (Группа: <?= $student['group_name'] ?>)</p>

    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Предмет</th>
                <th>Курс/Сем</th>
                <th>Оценка</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($exams as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['exam_date']) ?></td>
                    <td><?= htmlspecialchars($e['subject_name']) ?></td>
                    <td><?= $e['course'] ?> курс, <?= $e['semester'] ?> сем.</td>
                    <td><?= $e['mark'] ?></td>
                    <td class="actions">
                        <a href="exam_form.php?id=<?= $e['id'] ?>&student_id=<?= $studentId ?>" class="btn btn-edit">Ред.</a>
                        <a href="delete.php?type=exam&id=<?= $e['id'] ?>&student_id=<?= $studentId ?>" class="btn btn-danger" onclick="return confirm('Удалить?');">Уд.</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="exam_form.php?student_id=<?= $studentId ?>" class="btn btn-add">Добавить экзамен</a>
    <a href="index.php" class="btn btn-back">Назад к списку студентов</a>
</div>
</body>
</html>