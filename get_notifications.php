<?php
include __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['pending'=>0]); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM tasks WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
$pending = isset($res['pending']) ? (int)$res['pending'] : 0;
echo json_encode(['pending' => $pending]);
