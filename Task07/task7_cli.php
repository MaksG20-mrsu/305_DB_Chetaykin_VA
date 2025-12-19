<?php
$currentYear = (int)date('Y');

try {
    $pdo = new PDO('sqlite:university.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT group_number FROM groups WHERE graduation_year >= $currentYear");
    $groups = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($groups)) {
        exit("Нет действующих групп.\n");
    }

    echo "Доступные группы: " . implode(', ', $groups) . "\n";
    echo "Введите номер группы для фильтрации (или нажмите Enter для всех): ";
    $input = trim(fgets(STDIN));

    if ($input !== '' && !in_array($input, $groups)) {
        exit("Ошибка: Группа '$input' не найдена в списке действующих.\n");
    }

    $sql = "SELECT g.group_number, g.major, s.full_name, s.gender, s.birth_date, s.student_card 
            FROM students s 
            JOIN groups g ON s.group_id = g.id 
            WHERE g.graduation_year >= :currYear";
    
    if ($input !== '') {
        $sql .= " AND g.group_number = :groupNum";
    }
    
    $sql .= " ORDER BY g.group_number, s.full_name";

    $stmt = $pdo->prepare($sql);
    $params = [':currYear' => $currentYear];
    if ($input !== '') $params[':groupNum'] = $input;
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "Студентов не найдено.\n";
    } else {
        $mask = "| %-10s | %-25s | %-25s | %-5s | %-12s | %-10s |\n";
        $line = "+" . str_repeat("-", 12) . "+" . str_repeat("-", 27) . "+" . str_repeat("-", 27) . "+" . str_repeat("-", 7) . "+" . str_repeat("-", 14) . "+" . str_repeat("-", 12) . "+\n";

        echo $line;
        printf($mask, 'Группа', 'Направление', 'ФИО', 'Пол', 'Дата рожд.', 'Билет');
        echo $line;

        foreach ($rows as $row) {
            printf($mask, $row['group_number'], $row['major'], $row['full_name'], $row['gender'], $row['birth_date'], $row['student_card']);
        }
        echo $line;
    }

} catch (PDOException $e) {
    echo "Ошибка БД: " . $e->getMessage();
}