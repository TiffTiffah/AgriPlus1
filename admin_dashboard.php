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
                <a href="#" class="active">
                    <span><i class='bx bxs-dashboard'></i></span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="users.php">
                    <span><i class="fa-solid fa-user"></i></span>
                    <span>Users</span>
                </a>
            </li>
            <li class="sidebar-list-item">
                <a href="#">
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
            <div class="row">
                <!-- User Activities Summary -->
                <div class="card half-width">
                    <div class="card-header">User Activities Summary</div>
                    <div class="card-body">
                        <h5>Total Users: <span id="totalUsers">1500</span></h5>
                        <p>New Users This Week: <span id="newUsersThisWeek">25</span></p>
                        <p>Active Users Today: <span id="activeUsersToday">320</span></p>
                    </div>
                </div>
                <!-- Farm Statistics -->
                <div class="card half-width">
                    <div class="card-header">Farm Statistics</div>
                    <div class="card-body">
                        <h5>Total Farms: <span id="totalFarms">200</span></h5>
                        <p>Active Farms: <span id="activeFarms">180</span></p>
                    </div>
                </div>
            </div>
    
            <div class="row">
                <!-- Graph or Chart Example -->
                <div class="card full-width">
                    <div class="card-header">User's Growth</div>
                    <div class="card-body">
                        <div class="chart">[Graph Placeholder]</div>
                    </div>
                </div>
            </div>
    
            <div class="row">
                <!-- Recent Activities -->
                <div class="card full-width">
                    <div class="card-header">Recent Activities</div>
                    <div class="card-body">
                        <ul>
                            <li>User John added a new farm "Green Acres".</li>
                            <li>User Jane updated the production data for "Sunny Fields".</li>
                            <li>New user Alice registered.</li>
                        </ul>
                    </div>
                </div>
            </div>
    

    
            <div class="row">
                <!-- User Profile -->
                <div class="card full-width">
                    <div class="card-header">User Profile</div>
                    <div class="card-body">
                        <p>Name: <span id="userName">Admin</span></p>
                        <p>Email: <span id="userEmail">admin@example.com</span></p>
                        <p><button onclick="alert('Edit profile action')">Edit Profile</button></p>
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