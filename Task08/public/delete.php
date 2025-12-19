<?php
require_once __DIR__ . '/../src/Database.php';
$pdo = Database::getConnection();

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

if ($type === 'student') {
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
    header("Location: index.php");
} elseif ($type === 'exam') {
    $studentId = $_GET['student_id'];
    $pdo->prepare("DELETE FROM exams WHERE id = ?")->execute([$id]);
    header("Location: exam_results.php?student_id=" . $studentId);
}