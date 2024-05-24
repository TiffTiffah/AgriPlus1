<?php
// Start PHP session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: admin-login.php");
    exit(); // Stop further execution
}
// Include database connection
include 'db_connection.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch recent activities
$activityQuery = "SELECT * FROM activities ORDER BY activity_date DESC LIMIT 3";
$activityResult = $conn->query($activityQuery);


$sql = "SELECT COUNT(*) AS unreadCount FROM feedback WHERE status = 'unread'";
$result = $conn->query($sql);
$unreadCount = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $unreadCount = $row['unreadCount'];
}


$sql = "SELECT * FROM feedback ORDER BY submission_date DESC";
$result = $conn->query($sql);

// Mark all feedback as read
$updateSql = "UPDATE feedback SET status = 'read' WHERE status = 'unread'";
$conn->query($updateSql);

$conn->close();
?>

    
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dash.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<body>
    <div class="grid-container">

    <header class="header">
        <div class="menu-icon" onclick="openSidebar()">
            <span><i class="fa-solid fa-bars"></i></span>
        </div>

        <div class="header-left">

        </div>
        <div class="header-right">
        <div class="header-item">
        <a href="admin_feedback.php">
            <span><i class="fa-solid fa-bell"></i></span>
            <?php if ($unreadCount > 0): ?>
                <span class="badge"><?php echo $unreadCount; ?></span>
            <?php endif; ?>
        </a>
    </div>
            
    <div class="header-item">
    <a href="#" id="user-dropdown-btn">
        <span><i class="fa-solid fa-user"></i></span>
    </a>
</div>

<!-- Hidden dropdown menu or modal -->
<div id="user-dropdown" style="display: none;">
    <ul>
        <li><a href="#" id="edit-profile-btn">Edit Profile</a></li>
        <li><a href="#" id="logout-btn">Logout</a></li>
    </ul>
</div>
        </div>

    </header>

    <aside id="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">
            <h3>AGRI</h3><span>PLUS</span>
            </div>
            <span onclick="closeSidebar"><i class="fa-solid fa-x"></i></span>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-list-item">
                <a href="admin_dashboard.php" class="active">
                    
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="users.php">
                    
                    <span>Users</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="farms.php">
                  <span>Farms</span>
                </a>
            </li>

            <li class="sidebar-list-item">
                <a href="admin_feedback.php">
                    <span>Feedbacks</span>
                </a>
            </li>
    </aside>

    <main class="main-container">
    <h1>Feedback</h1>
    <table>
        

            
            <div class="card-body">
                <table class="user-table">
                    <thead>
                    <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Feedback</th>
            <th>Submission Date</th>
            <th>Actions</th>
        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['feedback_text']); ?></td>
                    <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                    <td class='actions'>
                        <button class='delete-btn'>Delete</a></button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No feedback found.</td>
            </tr>
        <?php endif; ?>
                    </tbody>
                </table>


    </main>

<script>
var sidebarOpen =false;
var sidebar = document.getElementById("sidebar");

function openSidebar(){
    if(!sidebarOpen){
        sidebar.classList.add("sidebar-responsive")
        sidebarOpen = true;
    }
}
function closeSidebar(){
    if(sidebarOpen){
        sidebar.classList.remove("sidebar-responsive")
        sidebarOpen = false;
    }
}
document.getElementById('user-dropdown-btn').addEventListener('click', function() {
        // Show or hide the dropdown menu
        var dropdown = document.getElementById('user-dropdown');
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    });

    // Add event listeners for edit profile and logout buttons
    document.getElementById('edit-profile-btn').addEventListener('click', function() {
        // Redirect to the edit profile page or open a modal for editing profile
        // Replace '#' with the appropriate URL or function call
        window.location.href = '#';
    });

    document.getElementById('logout-btn').addEventListener('click', function() {
        // Perform logout operation
        // Replace '#' with the appropriate URL or function call
        window.location.href = 'admin-login.php';
    });
</script>
</body>
</html>
