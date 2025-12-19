<?php
// data/init_db.php
require_once __DIR__ . '/../src/Database.php';

$pdo = Database::getConnection();

echo "<h3>Начинаем инициализацию и генерацию оценок...</h3>";

// 1. СОЗДАНИЕ ТАБЛИЦ
$commands = [
    "CREATE TABLE IF NOT EXISTS groups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        start_year INTEGER NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        group_id INTEGER NOT NULL,
        last_name TEXT NOT NULL,
        first_name TEXT NOT NULL,
        middle_name TEXT,
        gender TEXT CHECK(gender IN ('M', 'F')) NOT NULL,
        FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS subjects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        course INTEGER NOT NULL,
        semester INTEGER NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS exams (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        subject_id INTEGER NOT NULL,
        mark INTEGER CHECK(mark >= 2 AND mark <= 5),
        exam_date DATE NOT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    )"
];

foreach ($commands as $cmd) {
    $pdo->exec($cmd);
}

// 2. ОЧИСТКА СТАРЫХ ДАННЫХ
$pdo->exec("DELETE FROM exams; DELETE FROM students; DELETE FROM groups; DELETE FROM subjects;");
$pdo->exec("DELETE FROM sqlite_sequence"); 

// 3. ДОБАВЛЕНИЕ ГРУПП
$pdo->exec("INSERT INTO groups (name, start_year) VALUES ('305(1)', 2023)");
$pdo->exec("INSERT INTO groups (name, start_year) VALUES ('305(2)', 2023)");

// 4. ДОБАВЛЕНИЕ СТУДЕНТОВ
$students = [
    // --- Группа 305(1) (ID: 1) ---
    [1, 'Пузаков', 'Дмитрий', 'M'],
    [1, 'Тульсков', 'Илья', 'M'],
    [1, 'Пшеницына', 'Полина', 'F'],
    [1, 'Снегирев', 'Данил', 'M'],
    [1, 'Четайкин', 'Владислав', 'M'],
    [1, 'Маклаков', 'Сергей', 'M'],
    [1, 'Шарунов', 'Максим', 'M'],
    [1, 'Рыжкин', 'Владислав', 'M'],
    [1, 'Паркаев', 'Василий', 'M'],
    [1, 'Пяткин', 'Игорь', 'M'],
    [1, 'Полковников', 'Дмитрий', 'M'],
    [1, 'Казейкин', 'Иван', 'M'],
    [1, 'Фирстов', 'Артем', 'M'],

    // --- Группа 305(2) (ID: 2) ---
    [2, 'Шушев', 'Денис', 'M'],
    [2, 'Логунов', 'Илья', 'M'],
    [2, 'Иванов', 'Максим', 'M'],
    [2, 'Маскинскова', 'Наталья', 'F'],
    [2, 'Макарова', 'Юлия', 'F'],
    [2, 'Зубков', 'Рома', 'M'],
    [2, 'Рябчинко', 'Александра', 'F'],
    [2, 'Ивенин', 'Артем', 'M'],
    [2, 'Кочнев', 'Артем', 'M'],
    [2, 'Наумкин', 'Влад', 'M'],
    [2, 'Мукасеев', 'Дмитрий', 'M'],
    [2, 'Рыбаков', 'Евгений', 'M'],
    [2, 'Томилин', 'Илья', 'M']
];

$stmt = $pdo->prepare("INSERT INTO students (group_id, last_name, first_name, gender) VALUES (?, ?, ?, ?)");
foreach ($students as $s) {
    $stmt->execute($s);
}
echo "Добавлено студентов: " . count($students) . "<br>";

// 5. ДОБАВЛЕНИЕ ПРЕДМЕТОВ
$subjects = [
    // 1 курс, 1 семестр
    ['Введение в направление', 1, 1],
    ['Иностранный язык (Немецкий)', 1, 1],
    ['Иностранный язык (Английский)', 1, 1],
    ['История России', 1, 1],
    ['Линейная алгебра и аналитическая геометрия', 1, 1],
    ['Математический анализ', 1, 1],
    ['Машинная арифметика и цифровая логика', 1, 1],
    ['Основы программирования', 1, 1],
    ['Физическая культура и спорт', 1, 1],
    
    // 1 курс, 2 семестр
    ['Алгоритмы и структуры данных', 1, 2],
    ['Безопасность жизнедеятельности', 1, 2],
    ['Введение в искусственный интеллект', 1, 2],
    ['Дискретная математика', 1, 2],
    ['Ознакомительная практика', 1, 2],
    
    // 2 курс, 1 семестр
    ['Дополнительные главы матанализа', 2, 1],
    ['Математическая логика и теория алгоритмов', 2, 1],
    ['Научно-исследовательская работа', 2, 1],
    ['Объектно-ориентированное программирование', 2, 1],
    ['Основы Российской государственности', 2, 1],
    ['Теория вероятностей и мат. статистика', 2, 1],
    ['Физика', 2, 1],
    
    // 2 курс, 2 семестр
    ['Вычислительная математика', 2, 2],
    ['Компьютерный статистический анализ данных', 2, 2],
    ['Культура делового общения', 2, 2],
    ['Математические основы ИИ', 2, 2],
    ['Основы высокопроизводительных вычислений', 2, 2],
    ['Основы нечеткой алгебры', 2, 2],
    ['Теория автоматов и формальных языков', 2, 2],
    ['Теория информации', 2, 2],
    ['Технологическая практика', 2, 2],
    ['Экономика', 2, 2],
    
    // 3 курс, 1 семестр 
    ['Базы данных', 3, 1],
    ['Машинное обучение', 3, 1],
    ['Методы визуализации данных', 3, 1],
    ['Методы оптимизации', 3, 1],
    ['Основы проектной деятельности', 3, 1],
    ['Основы электротехники и электроники', 3, 1],
    ['Правоведение', 3, 1],
    ['Практикум по проектированию систем ИИ', 3, 1],
    ['Проектирование и архитектура ПО', 3, 1],
    ['Разработка многопоточных приложений', 3, 1],
    ['Экономика программной инженерии', 3, 1]
];

$stmt = $pdo->prepare("INSERT INTO subjects (name, course, semester) VALUES (?, ?, ?)");
foreach ($subjects as $s) {
    $stmt->execute($s);
}
echo "Добавлено предметов: " . count($subjects) . "<br>";


// 6. АВТОМАТИЧЕСКАЯ ГЕНЕРАЦИЯ ОЦЕНОК
echo "Генерация оценок за 1 и 2 курс...<br>";

$allStudents = $pdo->query("SELECT id FROM students")->fetchAll(PDO::FETCH_COLUMN);

$allSubjects = $pdo->query("SELECT id, course, semester FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

$examStmt = $pdo->prepare("INSERT INTO exams (student_id, subject_id, mark, exam_date) VALUES (?, ?, ?, ?)");

$countExams = 0;

foreach ($allStudents as $studentId) {
    foreach ($allSubjects as $sub) {
        if ($sub['course'] >= 3) {
            continue;
        }

        $year = 2023 + $sub['course'] - 1; 
        
        if ($sub['semester'] == 1) {
            $examYear = $year + 1;
            $month = '01';
            $day = rand(10, 25);
        } else {
            $examYear = $year + 1;
            $month = '06';
            $day = rand(5, 25);
        }
        
        $date = "$examYear-$month-$day";

        // Генерация оценки (3, 4 или 5)
        $mark = rand(3, 5);

        // Запись в БД
        $examStmt->execute([$studentId, $sub['id'], $mark, $date]);
        $countExams++;
    }
}

echo "Сгенерировано результатов экзаменов: $countExams<br>";

echo "<hr><strong style='color:green'>База данных успешно обновлена! Студенты имеют оценки за 1 и 2 курс.</strong><br>";
echo "Перейди на <a href='../public/index.php'>Главную страницу</a>";
?>