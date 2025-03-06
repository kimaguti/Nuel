<?php
session_start();
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
$stmt->bind_param("i", $user_id);  // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}

// Handle member registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_member'])) {
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = htmlspecialchars($_POST['role']);
    $identification_number = htmlspecialchars($_POST['identification_number']);

    // Check if username or phone already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR phone = ? OR identification_number = ?");
    $check_stmt->bind_param("sss", $username, $phone, $identification_number);  // 'sss' for three strings
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username, Phone, or Identification Number already exists.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, second_name, last_name, phone, username, password, role, identification_number) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $phone, $username, $password, $role, $identification_number);
        if ($stmt->execute()) {
            echo "<script>alert('Member registered successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Handle member update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_member'])) {
    $member_id = $_POST['member_id'];
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $username = htmlspecialchars($_POST['username']);
    $role = htmlspecialchars($_POST['role']);
    $identification_number = htmlspecialchars($_POST['identification_number']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Check if username or phone already exists (excluding the current member)
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR phone = ? OR identification_number = ?) AND id != ?");
    $check_stmt->bind_param("sssi", $username, $phone, $identification_number, $member_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username, Phone, or Identification Number already exists.');</script>";
    } else {
        if ($password) {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, second_name = ?, last_name = ?, phone = ?, username = ?, role = ?, identification_number = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $first_name, $middle_name, $last_name, $phone, $username, $role, $identification_number, $password, $member_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, second_name = ?, last_name = ?, phone = ?, username = ?, role = ?, identification_number = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $phone, $username, $role, $identification_number, $member_id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Member updated successfully!'); window.location.href = 'chairperson_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error updating member: " . $conn->error . "');</script>";
        }
    }
}

// Handle member deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id); // 'i' for integer
    if ($delete_stmt->execute()) {
        echo "<script>alert('Member deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting member: " . $conn->error . "');</script>";
    }
}

// Fetch member details for editing
$edit_member = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_member = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chairperson Dashboard - Manage Members</title>
    
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

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            position: fixed;
            top: 30px;
            bottom: 0;
            height: 100%;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 20px;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* Form input layout adjustments */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-group label {
            width: 150px;
            margin-right: 10px;
            text-align: right;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 0;
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

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Welcome, <?php echo htmlspecialchars($user['first_name']); ?> (Chairperson)</h3>
            <a href="#" onclick="showSection('dashboard')">Dashboard</a>
            <a href="#" onclick="showSection('members')">Manage Members</a>
            <a href="#" onclick="showSection('loans')">Approve Loans</a>
            <a href="#" onclick="showSection('reports')">Reports</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
<div id="dashboard" class="section">
    <h3>Dashboard</h3>
    <div class="row">
        <!-- Registered Members Card -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Registered Members</div>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php
                        $sql = "SELECT COUNT(*) as total_members FROM users WHERE role != 'chairperson'";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        echo $row['total_members'];
                        ?>
                    </h5>
                    <p class="card-text">Total members registered in the system.</p>
                </div>
            </div>
        </div>

        <!-- Loans Overview Card -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Loans Overview</div>
                <div class="card-body">
                    <canvas id="loansChart" width="200" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Contributions Card -->
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Monthly Contributions</div>
                <div class="card-body">
                    <canvas id="contributionsChart" width="200" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4>Quick Links</h4>
            <a href="#" onclick="showSection('members')" class="btn btn-secondary">Manage Members</a>
            <a href="#" onclick="showSection('loans')" class="btn btn-secondary">Approve Loans</a>
            <a href="#" onclick="showSection('reports')" class="btn btn-secondary">View Reports</a>
        </div>
    </div>
</div>
            <!-- Manage Members Section -->
            <div id="members" class="section active">
                <h3>Manage Members</h3>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#memberModal" onclick="resetForm()">Register New Member</button>

                <!-- Members Table -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Identification Number</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch members from the database
                        $sql = "SELECT * FROM users WHERE role != 'chairperson'"; // Exclude chairperson
                        $result = $conn->query($sql);

                        // Counter for numbering
                        $counter = 1;

                        while ($row = $result->fetch_assoc()) {
                            $full_name = $row['first_name'] . ' ' . ($row['second_name'] ? $row['second_name'] . ' ' : '') . $row['last_name'];
                            
                            echo "<tr>
                                    <td>{$counter}</td>
                                    <td>{$row['identification_number']}</td>
                                    <td>{$full_name}</td>
                                    <td>{$row['phone']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['role']}</td>
                                    <td>
                                        <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#memberModal' onclick='editMember({$row['id']})'>Edit</button>
                                        <a href='?delete_id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this member?\");'>Delete</a>
                                    </td>
                                </tr>";
                            $counter++; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Registering/Editing Member -->
    <div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel">Register/Edit Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" onsubmit="return validateForm()">
                        <input type="hidden" id="member_id" name="member_id">
                        <div class="mb-3 form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3 form-group">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="member">Member</option>
                                <option value="secretary">Secretary</option>
                                <option value="treasurer">Treasurer</option>
                            </select>
                        </div>
                        <div class="mb-3 form-group">
                            <label for="identification_number" class="form-label">Identification Number</label>
                            <input type="text" class="form-control" id="identification_number" name="identification_number" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="register_member" class="btn btn-primary" id="submitButton">Register Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function (section) {
                section.classList.remove('active');
            });

            var selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }
        }

        window.onload = function() {
            showSection('dashboard');
        };

        function validateForm() {
            var phone = document.getElementById('phone').value;
            var username = document.getElementById('username').value;
            var identification_number = document.getElementById('identification_number').value;

            var phonePattern = /^[0-9]{10}$/;
            if (!phone.match(phonePattern)) {
                alert("Please enter a valid phone number (10 digits).");
                return false;
            }

            if (username.includes(' ')) {
                alert("Username cannot contain spaces.");
                return false;
            }

            return true;
        }

        function resetForm() {
            document.getElementById('member_id').value = '';
            document.getElementById('first_name').value = '';
            document.getElementById('middle_name').value = '';
            document.getElementById('last_name').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = 'member';
            document.getElementById('identification_number').value = '';
            document.getElementById('submitButton').name = 'register_member';
            document.getElementById('submitButton').innerText = 'Register Member';
            document.getElementById('memberModalLabel').innerText = 'Register New Member';
        }

        function editMember(memberId) {
            fetch(`get_member.php?id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('member_id').value = data.id;
                    document.getElementById('first_name').value = data.first_name;
                    document.getElementById('middle_name').value = data.second_name;
                    document.getElementById('last_name').value = data.last_name;
                    document.getElementById('phone').value = data.phone;
                    document.getElementById('username').value = data.username;
                    document.getElementById('role').value = data.role;
                    document.getElementById('identification_number').value = data.identification_number;
                    document.getElementById('submitButton').name = 'update_member';
                    document.getElementById('submitButton').innerText = 'Update Member';
                    document.getElementById('memberModalLabel').innerText = 'Edit Member';
                });
        }
    

    // Loans Chart
    const loansCtx = document.getElementById('loansChart').getContext('2d');
    const loansChart = new Chart(loansCtx, {
        type: 'bar',
        data: {
            labels: ['Loans Taken', 'Loans Paid', 'Loans Unpaid'],
            datasets: [{
                label: 'Loans Overview',
                data: [12, 8, 4], // Replace with dynamic data from the database
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Contributions Chart
    const contributionsCtx = document.getElementById('contributionsChart').getContext('2d');
    const contributionsChart = new Chart(contributionsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Contributions',
                data: [500, 600, 700, 800, 900, 1000, 1100, 1200, 1300, 1400, 1500, 1600], // Replace with dynamic data
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    </script>

    <!-- Offline Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>