<?php
include __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (!isset($_GET['id'])) { header('Location: index.php'); exit; }

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

header('Location: index.php');
exit;
