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

// Total Users
$totalUsersQuery = "SELECT COUNT(*) AS total_users FROM users";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Active Users
$activeUsersQuery = "SELECT COUNT(*) AS active_users FROM users WHERE status = 'active'";
$activeUsersResult = $conn->query($activeUsersQuery);
$activeUsers = $activeUsersResult->fetch_assoc()['active_users'];

// New Users Today
$newUsersQuery = "SELECT COUNT(*) AS new_users_today FROM users WHERE DATE(RegistrationDate) = CURDATE()";
$newUsersResult = $conn->query($newUsersQuery);
$newUsersToday = $newUsersResult->fetch_assoc()['new_users_today'];

// Total Farms
$totalFarmsQuery = "SELECT COUNT(*) AS total_farms FROM farms";
$totalFarmsResult = $conn->query($totalFarmsQuery);
$totalFarms = $totalFarmsResult->fetch_assoc()['total_farms'];



// Total Feedback
$totalFeedbackQuery = "SELECT COUNT(*) AS total_feedback FROM feedback";
$totalFeedbackResult = $conn->query($totalFeedbackQuery);
$totalFeedback = $totalFeedbackResult->fetch_assoc()['total_feedback'];

// Unread Feedback
$unreadFeedbackQuery = "SELECT COUNT(*) AS unread_feedback FROM feedback WHERE status = 'unread'";
$unreadFeedbackResult = $conn->query($unreadFeedbackQuery);
$unreadFeedback = $unreadFeedbackResult->fetch_assoc()['unread_feedback'];

// Recent User Registrations
$recentUsersQuery = "SELECT fullname, email, RegistrationDate FROM users ORDER BY RegistrationDate DESC LIMIT 5";
$recentUsersResult = $conn->query($recentUsersQuery);

// Recent Farm Additions
$recentFarmsQuery = "SELECT FarmName, Location, owner, DateAdded FROM farms ORDER BY DateAdded DESC LIMIT 5";
$recentFarmsResult = $conn->query($recentFarmsQuery);

// Recent Feedback
$recentFeedbackQuery = "SELECT name, email, feedback_text, submission_date FROM feedback ORDER BY submission_date DESC LIMIT 5";
$recentFeedbackResult = $conn->query($recentFeedbackQuery);


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

?>

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
        <h2>Welcome, Admin</h2>
        
        <div class="overview">
            <div class="overview-item">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="overview-item">
                <h3>Active Users</h3>
                <p><?php echo $activeUsers; ?></p>
            </div>
            <div class="overview-item">
                <h3>New Users Today</h3>
                <p><?php echo $newUsersToday; ?></p>
            </div>
            <div class="overview-item">
                <h3>Total Farms</h3>
                <p><?php echo $totalFarms; ?></p>
            </div>

           
            <div class="overview-item">
                <h3>Unread Feedback</h3>
                <p><?php echo $unreadFeedback; ?></p>
            </div>
        </div>

        <div class="recent-activity">
            <h3>Recent Activity</h3>
            
            <div class="recent-users">
                <h4>Recent User Registrations</h4>
                <table class="user-table">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                    <?php while ($user = $recentUsersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['RegistrationDate']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            
            <div class="recent-farms">
                <h4>Recent Farm Additions</h4>
                <table class="user-table">
                    <tr>
                        <th>Farm Name</th>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Date Added</th>
                    </tr>
                    <?php while ($farm = $recentFarmsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($farm['FarmName']); ?></td>
                        <td><?php echo htmlspecialchars($farm['Location']); ?></td>
                        <td><?php echo htmlspecialchars($farm['owner']); ?></td>
                        <td><?php echo htmlspecialchars($farm['DateAdded']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            
            <div class="recent-feedback">
                <h4>Recent Feedback</h4>
                <table class="user-table">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Feedback</th>
                        <th>Date Submitted</th>
                    </tr>
                    <?php while ($feedback = $recentFeedbackResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['feedback_text']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['submission_date']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <!-- <div class="dash-actions">
            <button onclick="location.href='add_user.php'" class="edit-btn">Add New User</button>
            <button onclick="location.href='add_farm.php'" class="edit-btn">Add New Farm</button>
            <button onclick="location.href='admin_feedback.php'" class="edit-btn">View All Feedback</button>
        </div> -->
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