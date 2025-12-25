<?php
include __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];

$title = trim($_POST['title'] ?? '');
$desc = trim($_POST['description'] ?? '');
$date = $_POST['due_date'] ?? '';
$time = $_POST['due_time'] ?? '';

if (!$title || !$date || !$time) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date, due_time) VALUES (?, ?, ?, ?, ?)"); 
$stmt->bind_param("issss", $user_id, $title, $desc, $date, $time);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php');
exit;
