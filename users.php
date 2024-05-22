<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="users.css">
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
                <a href="#"><span><i class="fa-solid fa-bell"></i></span></a>
            </div>
            <div class="header-item">
                <a href="#"> <span><i class="fa-solid fa-envelope"></i></span></a>
            </div>
            <div class="header-item">
                <a href="#"><span><i class="fa-solid fa-user"></i></span></a>
            </div>
        </div>

    </header>

    <aside id="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">

            </div>
            <span onclick="closeSidebar"><i class="fa-solid fa-x"></i></span>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-list-item">
                <a href="admin_dashboard.php" class="active">
                    <span><i class='bx bxs-dashboard'></i></span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <span><i class="fa-solid fa-user"></i></span>
                    <span>Users</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="farms.php">
                    <span><i class="fa-solid fa-tractor"></i></span>
                    <span>Farms</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
                    <span></span>
                    <span>Reports</span>
                </a>
            <li class="sidebar-list-item">
                <a href="#">
                    <span><i class="fa-solid fa-cog"></i></span>
                    <span>Support</span>
                </a>
            </li>
    </aside>

    <main class="main-container">
    <div class="container">
        <!-- Existing cards and content here -->

        <!-- User Information Table -->
        <div class="card full-width">
            <div class="card-header">User Information</div>
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
                        <tr>
                            <td>001</td>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>2024-01-15</td>
                            <td>Active</td>
                            <td class="actions">
                                <a href="#">Edit</a>
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>Jane Smith</td>
                            <td>jane@example.com</td>
                            <td>2024-02-20</td>
                            <td>Inactive</td>
                            <td class="actions">
                                <a href="#">Edit</a>
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td>003</td>
                            <td>Alice Johnson</td>
                            <td>alice@example.com</td>
                            <td>2024-03-05</td>
                            <td>Active</td>
                            <td class="actions">
                                <a href="#">Edit</a>
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
</script>
</body>
</html>