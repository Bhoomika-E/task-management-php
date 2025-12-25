<?php
include __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (!isset($_GET['id'])) { header('Location: index.php'); exit; }

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$conn->begin_transaction();

// mark completed
$stmt = $conn->prepare("UPDATE tasks SET status='completed' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$stmt->close();

// fetch current xp/level
$stmt2 = $conn->prepare("SELECT xp, level FROM users WHERE id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$res = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$newXP = (int)$res['xp'] + 10;
$newLevel = (int)$res['level'];
if ($newXP >= 50) { $newLevel += 1; $newXP = 0; }

$stmt3 = $conn->prepare("UPDATE users SET xp = ?, level = ? WHERE id = ?");
$stmt3->bind_param("iii", $newXP, $newLevel, $user_id);
$stmt3->execute();
$stmt3->close();

$conn->commit();
$conn->close();

if ($newLevel > (int)$res['level']) {
    header('Location: index.php?levelup=1');
    exit;
}
header('Location: index.php');
exit;
