<?php
$groups = [
    ['number' => '305(1)', 'major' => 'Программная инженерия', 'end_year' => 2026],
    ['number' => '305(2)', 'major' => 'Программная инженерия', 'end_year' => 2026]
];

$students_data = [
    '305(1)' => ['Дмитрий Пузаков', 'Илья Тульсков', 'Полина Пшеницына', 'Данил Снегирев', 'Владислав Четайкин', 'Сергей Маклаков', 'Максим Шарунов', 'Владислав Рыжкин', 'Василий Паркаев', 'Игорь Пяткин', 'Дмитрий Полковников', 'Иван Казейкин', 'Артем Фирстов'],
    '305(2)' => ['Денис Шушев', 'Илья Логунов', 'Максим Иванов', 'Наталья Маскинскова', 'Юлия Макарова', 'Рома Зубков', 'Александра Рябчинко', 'Артем Ивенин', 'Артем Кочнев', 'Влад Наумкин', 'Дмитрий Мукасеев', 'Евгений Рыбаков', 'Илья Томилин']
];

try {
    $pdo = new PDO('sqlite:university.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("DROP TABLE IF EXISTS students");
    $pdo->exec("DROP TABLE IF EXISTS groups");

    $pdo->exec("CREATE TABLE groups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        group_number TEXT NOT NULL,
        major TEXT NOT NULL,
        graduation_year INTEGER NOT NULL
    )");

    $pdo->exec("CREATE TABLE students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        group_id INTEGER,
        full_name TEXT NOT NULL,
        gender TEXT,
        birth_date DATE,
        student_card TEXT,
        FOREIGN KEY (group_id) REFERENCES groups(id)
    )");

    $stmtGroup = $pdo->prepare("INSERT INTO groups (group_number, major, graduation_year) VALUES (?, ?, ?)");
    foreach ($groups as $g) {
        $stmtGroup->execute([$g['number'], $g['major'], $g['end_year']]);
    }

    $stmtStudent = $pdo->prepare("INSERT INTO students (group_id, full_name, gender, birth_date, student_card) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($students_data as $groupNum => $names) {
        $res = $pdo->query("SELECT id FROM groups WHERE group_number = '$groupNum'")->fetch();
        $groupId = $res['id'];

        foreach ($names as $name) {
            $gender = (preg_match('/(а|я)$/u', explode(' ', $name)[1] ?? '')) ? 'Жен' : 'Муж';
            $birth = "2004-" . rand(1, 12) . "-" . rand(1, 28);
            $card = "ST-" . rand(10000, 99999);
            $stmtStudent->execute([$groupId, $name, $gender, $birth, $card]);
        }
    }

    echo "База данных успешно создана и заполнена!\n";

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}