<?php
session_start();

// Redirect if not logged in or not a treasurer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'treasurer') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "rowi");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions for loan rates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add or Update Loan Rate
    if (isset($_POST['submit_loan_rate'])) {
        $start_date = $_POST['start_date']; // Loan start date
        $end_date = $_POST['end_date']; // Loan end date
        $rate = floatval($_POST['rate']); // Loan interest rate

        // Validate inputs
        if (empty($start_date) || empty($end_date) || $rate < 0) {
            echo "Invalid input data!";
        } else {
            if (isset($_POST['loan_rate_id']) && $_POST['loan_rate_id'] != "") {
                // Update loan rate
                $loan_rate_id = intval($_POST['loan_rate_id']);
                $stmt = $conn->prepare("UPDATE loan_rates SET start_date=?, end_date=?, rate=? WHERE id=?");
                $stmt->bind_param("ssdi", $start_date, $end_date, $rate, $loan_rate_id);
            } else {
                // Insert new loan rate
                $stmt = $conn->prepare("INSERT INTO loan_rates (start_date, end_date, rate) VALUES (?, ?, ?)");
                $stmt->bind_param("ssd", $start_date, $end_date, $rate);
            }

            if ($stmt->execute()) {
                echo "Loan rate submitted successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Delete Loan Rate
    if (isset($_GET['delete_loan_rate'])) {
        $loan_rate_id = intval($_GET['delete_loan_rate']); // Ensure loan_rate_id is an integer

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM loan_rates WHERE id=?");
        $stmt->bind_param("i", $loan_rate_id);

        if ($stmt->execute()) {
            echo "Loan rate deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all loan rates
$loan_rates = [];
$sql = "SELECT * FROM loan_rates";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $loan_rates[] = $row;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer Dashboard - Chama App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            position: fixed;
            top: 30px;
            bottom: 0;
            overflow-y: auto;
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
            margin-top: 56px;
            height: 100vh;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar (Fixed to the top) -->
    <?php include 'navbar.php'; ?>

    <div class="d-flex">
        <!-- Sidebar (Fixed below the navbar) -->
        <div class="sidebar">
            <h3>Chama App</h3>
            <a href="#">Dashboard</a>
            <a href="#shares">Member Shares</a>
            <a href="#rates" data-bs-toggle="collapse" data-bs-target="#rates-collapse">Loan Rates</a>
            <a href="#apply-loan">Apply for Loan</a>
            <a href="#approve-loans">Approve Loans</a>
            <a href="#reports">Reports</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Welcome, Treasurer!</h2>

            <!-- Loan Rates Section -->
            <div id="rates" class="collapse show">
                <h3>Loan Rates</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loanRateModal">Add Loan Rate</button>

                <!-- Loan Rates Table -->
                <table class="table mt-4">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Rate (%)</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loan_rates as $rate): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rate['id']); ?></td>
                                <td><?php echo htmlspecialchars($rate['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($rate['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($rate['rate']); ?></td>
                                <td><?php echo htmlspecialchars($rate['date_created']); ?></td>
                                <td>
                                    <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#loanRateModal" 
                                       onclick="editLoanRate(<?php echo $rate['id']; ?>, '<?php echo $rate['start_date']; ?>', '<?php echo $rate['end_date']; ?>', <?php echo $rate['rate']; ?>)">Edit</a>
                                    <a href="?delete_loan_rate=<?php echo $rate['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal for adding/editing loan rate -->
            <div class="modal fade" id="loanRateModal" tabindex="-1" aria-labelledby="loanRateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="loanRateModalLabel">Add or Edit Loan Rate</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post">
                                <input type="hidden" id="loan_rate_id" name="loan_rate_id">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="rate" class="form-label">Rate (%)</label>
                                    <input type="number" class="form-control" id="rate" name="rate" required min="0" step="0.01">
                                </div>
                                <button type="submit" name="submit_loan_rate" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editLoanRate(id, start_date, end_date, rate) {
            document.getElementById('loan_rate_id').value = id;
            document.getElementById('start_date').value = start_date;
            document.getElementById('end_date').value = end_date;
            document.getElementById('rate').value = rate;
        }
    </script>
</body>
</html>
