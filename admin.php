<?php
session_start();

// Database configuration
include 'db_connection.php';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            header("Location: admin_dashboard.php");
        } else {
            echo "<script>document.getElementById('message').innerText = 'Invalid credentials';</script>";
        }
    } else {
        echo "<script>document.getElementById('message').innerText = 'Invalid credentials';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="help.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script src="script.js"></script>
    <title>Admin Dashboard</title>
</head>
<body>
       <div class="dash-container">
        <!-- ----------------------------sidebar-------------------------------- -->
        <div class="side-bar">
        <div class="logo">
                <img src="images/logo (1).png" alt="logo">
                <h3>AGRI</h3><span>PLUS</span>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="dashboard.php" ><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php" ><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="help.php"  class="active"><i class='bx bx-help-circle' ></i></i>Help</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
</body>
</html>
