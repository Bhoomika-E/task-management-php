<?php
include __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, title, due_date, due_time FROM tasks WHERE user_id = ? AND status = 'pending' ORDER BY due_date ASC, due_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
$stmt->close();
$conn->close();
echo json_encode($data);
