<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chairperson') {
    die("Unauthorized access.");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "rowi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$member_id = $_GET['id'] ?? null;
if (!$member_id) {
    die("Member ID not provided.");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) {
    die("Member not found.");
}

header('Content-Type: application/json');
echo json_encode($member);
?>