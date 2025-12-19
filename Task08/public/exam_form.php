<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getConnection();

$id = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;
$exam = ['subject_id'=>'','mark'=>5,'exam_date'=>date('Y-m-d')];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$studentData = $stmt->fetch();

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$id]);
    $exam = $stmt->fetch();
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subId = $_POST['subject_id'];
    $mark = $_POST['mark'];
    $date = $_POST['exam_date'];

    if ($id) {
        $sql = "UPDATE exams SET subject_id=?, mark=?, exam_date=? WHERE id=?";
        $pdo->prepare($sql)->execute([$subId, $mark, $date, $id]);
    } else {
        $sql = "INSERT INTO exams (student_id, subject_id, mark, exam_date) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$studentId, $subId, $mark, $date]);
    }
    header("Location: exam_results.php?student_id=$studentId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Запись экзамена</title>
    <link rel="stylesheet" href="style.css">
    <script>
        const allSubjects = <?= json_encode($subjects) ?>;

        function filterSubjects() {
            const course = document.getElementById('course_select').value;
            const semester = document.getElementById('semester_select').value;
            const select = document.getElementById('subject_select');
            
            select.innerHTML = '';
            
            const filtered = allSubjects.filter(s => s.course == course && s.semester == semester);
            
            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.text = "Нет предметов для этого семестра";
                select.add(opt);
            }

            filtered.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.text = s.name;
                if (s.id == "<?= $exam['subject_id'] ?>") {
                    opt.selected = true;
                }
                select.add(opt);
            });
        }

        window.onload = filterSubjects;
    </script>
</head>
<body>
<div class="container">
    <h2><?= $id ? 'Редактировать' : 'Добавить' ?> экзамен</h2>
    <p>Студент: <?= htmlspecialchars($studentData['last_name']) ?></p>

    <form method="POST">
        <div style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
            <p style="margin-top:0"><strong>Шаг 1. Выберите период сдачи:</strong></p>
            <div class="form-group">
                <label>Курс:</label>
                <select id="course_select" onchange="filterSubjects()">
                    <option value="1">1 курс</option>
                    <option value="2">2 курс</option>
                    <option value="3">3 курс</option>
                </select>
            </div>
            <div class="form-group">
                <label>Семестр:</label>
                <select id="semester_select" onchange="filterSubjects()">
                    <option value="1">1 семестр</option>
                    <option value="2">2 семестр</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Предмет:</label>
            <select name="subject_id" id="subject_select" required>
            </select>
        </div>

        <div class="form-group">
            <label>Оценка:</label>
            <select name="mark">
                <option value="5" <?= $exam['mark']==5?'selected':'' ?>>5 (Отлично)</option>
                <option value="4" <?= $exam['mark']==4?'selected':'' ?>>4 (Хорошо)</option>
                <option value="3" <?= $exam['mark']==3?'selected':'' ?>>3 (Удовл.)</option>
                <option value="2" <?= $exam['mark']==2?'selected':'' ?>>2 (Неуд.)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Дата сдачи (задним числом можно):</label>
            <input type="date" name="exam_date" value="<?= htmlspecialchars($exam['exam_date']) ?>" required>
        </div>

        <button type="submit" class="btn btn-add">Сохранить</button>
        <a href="exam_results.php?student_id=<?= $studentId ?>" class="btn btn-danger">Отмена</a>
    </form>
</div>
</body>
</html>