<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "chama_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle loan request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_loan'])) {
    $amount = $_POST['amount'];
    $purpose = $_POST['purpose'];
    $member_id = $_SESSION['user_id'];

    $sql = "INSERT INTO loans (member_id, amount, purpose, status) VALUES ('$member_id', '$amount', '$purpose', 'pending')";
    if ($conn->query($sql) === TRUE) {
        echo "Loan request submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Chama App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        .navbar {
            background-color: #007bff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
        }
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
    <!-- Navbar -->
    <div class="navbar">
        <div>
            Welcome, <?php echo $_SESSION['user_name']; ?> (<?php echo ucfirst($_SESSION['role']); ?>)
        </div>
        <div>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Chama App</h3>
            <a href="#">Dashboard</a>
            <a href="#contributions">Contributions</a>
            <a href="#loans">Loans</a>
            <a href="#statements">Statements</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Member Dashboard</h2>

            <!-- Contributions -->
            <div id="contributions">
                <h3>Contributions</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $sql = "SELECT * FROM contributions WHERE member_id='$user_id'";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['date']}</td>
                                    <td>{$row['amount']}</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Loans -->
            <div id="loans">
                <h3>Loans</h3>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Loan Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input type="text" class="form-control" id="purpose" name="purpose" required>
                    </div>
                    <button type="submit" name="request_loan" class="btn btn-primary">Request Loan</button>
                </form>

                <h4>Your Loans</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Purpose</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM loans WHERE member_id='$user_id'";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['amount']}</td>
                                    <td>{$row['purpose']}</td>
                                    <td>{$row['status']}</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Statements -->
            <div id="statements">
                <h3>Statements</h3>
                <p>Your financial statements will be available here.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>