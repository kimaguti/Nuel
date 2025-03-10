<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chairperson') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "rowi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    die("User not found.");
}

// CSRF Token generation (to prevent CSRF attacks)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission for editing member details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_member'])) {
    // Validate CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed!");
    }

    $member_id = $_POST['member_id'];
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $username = htmlspecialchars($_POST['username']);
    $role = htmlspecialchars($_POST['role']);
    $identification_number = htmlspecialchars($_POST['identification_number']);

    // Validate phone format (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo "<script>alert('Please enter a valid phone number (10 digits).');</script>";
    } else {
        // Prepare query to update member information
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, second_name = ?, last_name = ?, phone = ?, username = ?, role = ?, identification_number = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $phone, $username, $role, $identification_number, $member_id);

        if ($stmt->execute()) {
            echo "<script>alert('Member updated successfully!'); window.location.href = 'chairperson_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Fetch member details to populate the form
if (isset($_GET['id'])) {
    $member_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $member = $result->fetch_assoc();
    } else {
        die("Member not found.");
    }
} else {
    die("Member ID not provided.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member Details</title>
    
    <!-- Offline Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Offline Font Awesome CSS -->
    <link rel="stylesheet" href="assets/icons/css/font-awesome.min.css">
    
    <link rel="icon" href="favicon.ico">
    
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
        }

        .navbar {
            margin-bottom: 0;
        }

        .container {
            margin-top: 50px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>
<body>

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container">
        <h2>Edit Member Details</h2>
        <form method="post" action="edit_member.php" onsubmit="return validateForm()">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">

            <div class="form-group">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($member['second_name']); ?>">
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($member['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="member" <?php echo $member['role'] == 'member' ? 'selected' : ''; ?>>Member</option>
                    <option value="secretary" <?php echo $member['role'] == 'secretary' ? 'selected' : ''; ?>>Secretary</option>
                    <option value="treasurer" <?php echo $member['role'] == 'treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="identification_number" class="form-label">Identification Number</label>
                <input type="text" class="form-control" id="identification_number" name="identification_number" value="<?php echo htmlspecialchars($member['identification_number']); ?>" required>
            </div>

            <div class="modal-footer">
                <button type="submit" name="edit_member" class="btn btn-primary">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function validateForm() {
            var phone = document.getElementById('phone').value;
            var phonePattern = /^[0-9]{10}$/;
            if (!phone.match(phonePattern)) {
                alert("Please enter a valid phone number (10 digits).");
                return false;
            }
            return true;
        }
    </script>

    <!-- Offline Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
