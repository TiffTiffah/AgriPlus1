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
// Prepare statement to fetch user information
$userQuery = "SELECT  userID, fullname, email, RegistrationDate, status  FROM users";
$userStatement = $conn->prepare($userQuery);


    // Execute the statement
    $userStatement->execute();

    // Get the result set
    $userResult = $userStatement->get_result();
// Query to count active users
$activeUsersQuery = "SELECT COUNT(*) AS activeUsersCount FROM users WHERE status = 'Active'";
$activeUsersResult = $conn->query($activeUsersQuery);

// Check if query executed successfully
if ($activeUsersResult !== false) {
    // Fetch the count of active users
    $activeUsersRow = $activeUsersResult->fetch_assoc();
    $activeUsersCount = $activeUsersRow['activeUsersCount'];
} else {
    // If query execution failed, set count to 0
    $activeUsersCount = 0;
    // Display error message
    echo "Error: " . $conn->error;
}

// Fetch farm information
$farmQuery = "SELECT * FROM farms";
$farmResult = $conn->query($farmQuery);

// Query to count active farms
$activeFarmsQuery = "SELECT COUNT(*) AS activeFarmsCount FROM farms WHERE status = 'Active'";
$activeFarmsResult = $conn->query($activeFarmsQuery);

// Check if query executed successfully
if ($activeFarmsResult !== false) {
    // Fetch the count of active farms
    $activeFarmsRow = $activeFarmsResult->fetch_assoc();
    $activeFarmsCount = $activeFarmsRow['activeFarmsCount'];
} else {
    // If query execution failed, set count to 0
    $activeFarmsCount = 0;
    // Display error message
    echo "Error: " . $conn->error;
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="users.css">
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

    <h1>User Information</h1>
            <div class="card-body">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php


    // Check if query executed successfully
    if ($userResult !== false) {
        // Fetch the user information
        while ($userRow = $userResult->fetch_assoc()) {
            // Process user data
            echo "<tr>
                    <td>{$userRow['userID']}</td>
                    <td>{$userRow['fullname']}</td>
                    <td>{$userRow['email']}</td>
                    <td>{$userRow['RegistrationDate']}</td>
                    <td>{$userRow['status']}</td>
                    
                    
                    <td class='actions'>
                    <button class='edit-btn' onclick=\"openEditModal({$userRow['userID']}, '{$userRow['fullname']}', '{$userRow['email']}', '{$userRow['status']}')\">Edit</button>
                    <form action='delete_prof.php' method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this user?');\">
                    <input type='hidden' name='user_id' value='{$userRow['userID']}'>
                    <button type='submit' class='delete-btn'>Delete</button>
                </form>
                    </td>
                </tr>";
        }
    } else {
        // If query execution failed
        echo "Error: " . $conn->error;
    }

    // Close the statement
    $userStatement->close();

                        ?>
                    </tbody>
                </table>

                <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <form id="editForm" action="edit_user.php" method="post">
                <input type="hidden" name="user_id" id="user_id">
                <label for="fullname">Full Name:</label>
                <input type="text" name="fullname" id="fullname" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <button class="save-btn" type="submit">Save</button>
            </form>
        </div>
    </div>

    </div>

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
function openEditModal(userID, fullname, email, status) {
            document.getElementById('user_id').value = userID;
            document.getElementById('fullname').value = fullname;
            document.getElementById('email').value = email;
            document.getElementById('status').value = status;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
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