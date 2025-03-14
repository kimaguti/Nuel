<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nuel";  // Your database name is 'nuel'

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"));

$first_name = $data->first_name;
$middle_name = $data->middle_name ?? '';
$last_name = $data->last_name;
$phone_number = $data->phone_number;
$username = $data->username;
$password = password_hash($data->password, PASSWORD_DEFAULT);
$group_name = $data->group_name;

$sql = "INSERT INTO users (first_name, middle_name, last_name, phone_number, username, password, group_name) 
        VALUES ('$first_name', '$middle_name', '$last_name', '$phone_number', '$username', '$password', '$group_name')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "User registered successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $sql . "<br>" . $conn->error]);
}

$conn->close();
?>
