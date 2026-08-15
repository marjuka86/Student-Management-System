<?php
session_start();

$host = 'localhost';
$db   = 'ewu_sms';
$user = 'root';
$pass = '';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $student_id = $_GET['id'];
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $stmt = $pdo->prepare("DELETE FROM STUDENTS WHERE Student_ID = ?");
        $stmt->execute([$student_id]);
    } catch (\PDOException $e) {
        // Handle error silently
    }
}

header("Location: students.php");
exit;
?>