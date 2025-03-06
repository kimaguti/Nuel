<?php

// Database connection
$conn = new mysqli("localhost", "root", "", "rowi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, second_name, last_name, role FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $full_name = $user['first_name'] . ' ' . ($user['second_name'] ? $user['second_name'] . ' ' : '') . $user['last_name'];
    $role = $user['role'];
} else {
    $full_name = "User";
    $role = "Unknown";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chairperson Dashboard </title>
    <!-- Local Font Awesome CSS -->
    <link rel="stylesheet" href="assets/icons/css/font-awesome.min.css">
    <style>
        /* Reset default margin and padding for all elements */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Ensure the body and html take up the full height and width */
        html, body {
            height: 100%;
            width: 100%;
        }

        /* Navbar styling */
        .navbar {
            background-color:rgb(105, 170, 240);
            padding: 5px 10px; /* Reduced padding to reduce height */
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            height: 30px; /* Set a fixed height for the navbar */
            width: 100%; /* Ensure the navbar spans the full width */
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
        }

        .navbar a:hover {
            opacity: 0.8; /* Add hover effect */
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 5px; /* Space between icon and text */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div>Welcome, 
            <a href="edit_profile.php" style="color: #fff; text-decoration: none;">
                <?php echo htmlspecialchars($full_name); ?> (<?php echo htmlspecialchars(ucfirst($role)); ?>)
            </a>
        </div>
        <div>
            <a href="logout.php" class="logout-link">
                <i class="fas fa-power-off"></i> <!-- Font Awesome shutdown icon -->
                Logout
            </a>
        </div>
    </div>
</body>
</html>