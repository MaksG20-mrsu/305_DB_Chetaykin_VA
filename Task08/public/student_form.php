<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getConnection();

$id = $_GET['id'] ?? null;
$student = ['last_name'=>'','first_name'=>'','middle_name'=>'','group_id'=>'','gender'=>'M'];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ln = $_POST['last_name'];
    $fn = $_POST['first_name'];
    $mn = $_POST['middle_name'];
    $gid = $_POST['group_id'];
    $gen = $_POST['gender'];

    if ($id) {
        $sql = "UPDATE students SET last_name=?, first_name=?, middle_name=?, group_id=?, gender=? WHERE id=?";
        $pdo->prepare($sql)->execute([$ln, $fn, $mn, $gid, $gen, $id]);
    } else {
        $sql = "INSERT INTO students (last_name, first_name, middle_name, group_id, gender) VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$ln, $fn, $mn, $gid, $gen]);
    }
    header("Location: index.php");
    exit;
}

$groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактирование' : 'Добавление' ?> студента</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2><?= $id ? 'Редактировать' : 'Добавить' ?> студента</h2>
    <form method="POST">
        <div class="form-group">
            <label>Фамилия:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Имя:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Отчество:</label>
            <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>">
        </div>
        <div class="form-group">
            <label>Группа:</label>
            <select name="group_id" required>
                <?php foreach($groups as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $student['group_id'] == $g['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Пол:</label>
            <label><input type="radio" name="gender" value="M" <?= $student['gender'] == 'M' ? 'checked' : '' ?>> Муж</label>
            <label><input type="radio" name="gender" value="F" <?= $student['gender'] == 'F' ? 'checked' : '' ?>> Жен</label>
        </div>
        <button type="submit" class="btn btn-add">Сохранить</button>
        <a href="index.php" class="btn btn-danger">Отмена</a>
    </form>
</div>
</body>
</html>