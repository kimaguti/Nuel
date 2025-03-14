<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nuel";  // Your database name is 'nuel'

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get data from the request body
$data = json_decode(file_get_contents("php://input"));

// Sanitize and assign form data
$first_name = $data->first_name ?? '';
$middle_name = $data->middle_name ?? '';  // Optional field
$last_name = $data->last_name ?? '';
$phone_number = $data->phone_number ?? '';
$username = $data->username ?? '';
$password = password_hash($data->password ?? '', PASSWORD_DEFAULT);  // Hash password
$group_name = $data->group_name ?? '';

// Check if required fields are provided
if (empty($first_name) || empty($last_name) || empty($phone_number) || empty($username) || empty($password) || empty($group_name)) {
    echo json_encode(["message" => "All fields are required"]);
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, phone_number, username, password, group_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $phone_number, $username, $password, $group_name);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["message" => "User registered successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $stmt->error]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
