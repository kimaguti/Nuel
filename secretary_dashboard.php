<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "chama_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle meeting scheduling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_meeting'])) {
    $meeting_date = $_POST['meeting_date'];
    $agenda = $_POST['agenda'];

    $sql = "INSERT INTO meetings (meeting_date, agenda) VALUES ('$meeting_date', '$agenda')";
    if ($conn->query($sql) === TRUE) {
        echo "Meeting scheduled successfully!";
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
    <title>Secretary Dashboard - Chama App</title>
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
            <a href="#meetings">Meetings</a>
            <a href="#announcements">Announcements</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>Secretary Dashboard</h2>

            <!-- Meetings -->
            <div id="meetings">
                <h3>Schedule Meeting</h3>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="meeting_date" class="form-label">Meeting Date</label>
                        <input type="date" class="form-control" id="meeting_date" name="meeting_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="agenda" class="form-label">Agenda</label>
                        <textarea class="form-control" id="agenda" name="agenda" required></textarea>
                    </div>
                    <button type="submit" name="schedule_meeting" class="btn btn-primary">Schedule Meeting</button>
                </form>

                <h4>Upcoming Meetings</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Agenda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM meetings WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['meeting_date']}</td>
                                    <td>{$row['agenda']}</td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Announcements -->
            <div id="announcements">
                <h3>Send Announcements</h3>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="announcement" class="form-label">Announcement</label>
                        <textarea class="form-control" id="announcement" name="announcement" required></textarea>
                    </div>
                    <button type="submit" name="send_announcement" class="btn btn-primary">Send Announcement</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>