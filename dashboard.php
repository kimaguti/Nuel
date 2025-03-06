<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chairperson') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "chama_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_chama'])) {
        $chama_name = $_POST['chama_name'];
        $contribution_amount = $_POST['contribution_amount'];
        $sql = "INSERT INTO chama (name, contribution_amount) VALUES ('$chama_name', '$contribution_amount')";
        if ($conn->query($sql) === TRUE) {
            echo "Chama created successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    if (isset($_POST['register_member'])) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];
        $sql = "INSERT INTO users (name, phone, role) VALUES ('$name', '$phone', '$role')";
        if ($conn->query($sql) === TRUE) {
            echo "Member registered successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    if (isset($_POST['approve_loan'])) {
        $loan_id = $_POST['loan_id'];
        $sql = "UPDATE loans SET status='approved' WHERE id='$loan_id'";
        if ($conn->query($sql) === TRUE) {
            echo "Loan approved successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chairperson Dashboard - Chama App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Chama App</h3>
            <a href="#">Dashboard</a>
            <a href="#create-chama">Create Chama</a>
            <a href="#register-member">Register Member</a>
            <a href="#approve-loans">Approve Loans</a>
            <a href="#reports">Reports</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Welcome, Chairperson!</h2>

            <!-- Create Chama -->
            <div id="create-chama">
                <h3>Create Chama</h3>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="chama_name" class="form-label">Chama Name</label>
                        <input type="text" class="form-control" id="chama_name" name="chama_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contribution_amount" class="form-label">Monthly Contribution Amount</label>
                        <input type="number" class="form-control" id="contribution_amount" name="contribution_amount" required>
                    </div>
                    <button type="submit" name="create_chama" class="btn btn-primary">Create Chama</button>
                </form>
            </div>

            <!-- Register Member -->
            <div id="register-member">
                <h3>Register Member</h3>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role">
                            <option value="member">Member</option>
                            <option value="treasurer">Treasurer</option>
                            <option value="secretary">Secretary</option>
                        </select>
                    </div>
                    <button type="submit" name="register_member" class="btn btn-primary">Register Member</button>
                </form>
            </div>

            <!-- Approve Loans -->
            <div id="approve-loans">
                <h3>Approve Loans</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Purpose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM loans WHERE status='pending'";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['member_name']}</td>
                                    <td>{$row['amount']}</td>
                                    <td>{$row['purpose']}</td>
                                    <td>
                                        <form method='post' action=''>
                                            <input type='hidden' name='loan_id' value='{$row['id']}'>
                                            <button type='submit' name='approve_loan' class='btn btn-success'>Approve</button>
                                        </form>
                                    </td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>