<?php
session_start();
if (!isset($_SESSION['user_id']) {
    die("Unauthorized access.");
}

$conn = new mysqli("localhost", "root", "", "rowi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch loans data
$loans_taken = $conn->query("SELECT COUNT(*) as total FROM loans")->fetch_assoc()['total'];
$loans_paid = $conn->query("SELECT COUNT(*) as total FROM loans WHERE status = 'paid'")->fetch_assoc()['total'];
$loans_unpaid = $conn->query("SELECT COUNT(*) as total FROM loans WHERE status = 'unpaid'")->fetch_assoc()['total'];

// Fetch monthly contributions data
$contributions = [];
for ($i = 1; $i <= 12; $i++) {
    $sql = "SELECT SUM(amount) as total FROM contributions WHERE MONTH(date) = $i AND YEAR(date) = YEAR(CURDATE())";
    $result = $conn->query($sql);
    $contributions[] = $result->fetch_assoc()['total'] ?? 0;
}

echo json_encode([
    'loans' => [
        'taken' => $loans_taken,
        'paid' => $loans_paid,
        'unpaid' => $loans_unpaid
    ],
    'contributions' => $contributions
]);
?>