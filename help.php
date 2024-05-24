<?php
session_start();

// Check if user is logged in (assuming you have a user authentication system)
if (!isset($_SESSION["user_id"])) {
    // Redirect to the sign-in page or display an error message
    header("Location: signin.html");
    exit();
}

    // Connect to your database (modify database credentials as needed)
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


// Fetch user data from the database (assuming you have a users table)
$sql = "SELECT FullName, Username, Email FROM users WHERE UserID = ?"; // Replace user_id with the appropriate column name for identifying users
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Error handling if query preparation fails
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("i", $_SESSION["user_id"]);

if (!$stmt->execute()) {
    // Error handling if query execution fails
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch user data
    // Initialize $userData variable
    $userData = array();
    $userData = $result->fetch_assoc();
} else {
    echo "User not found.";
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $feedback = $_POST["feedback"];
    $name = $_POST["name"];
    $email = $_POST["email"];


    // Set SMTP server and port
    ini_set("SMTP", "smtp.gmail.com");
    ini_set("smtp_port", "587");


    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Send email
        $to = "tiffahnick@email.com"; // Change this to your email address
        $subject = "Feedback from " . $name;
        $message = "Feedback: " . $feedback . "\n";
        $message .= "From: " . $name . " (" . $email . ")";
        $headers = "From: " . $email;

        if (mail($to, $subject, $message, $headers)) {
            $success_message = "Thank you for your feedback!";
        } else {
            $error_message = "Failed to send feedback. Please try again later.";
        }
    }
}
?>

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
    <title>AgriPlus | Help</title>
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
        <!-- ----------------------------main content-------------------------------- -->
        <div class="main-content">
        <div class="tab-container">
        <!-- Tab links -->
        <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'instructions')">Detailed Instructions</button>
            <button class="tablinks" onclick="openTab(event, 'troubleshooting')">Troubleshooting</button>
            <button class="tablinks" onclick="openTab(event, 'contact')">Contact Information</button>
            <button class="tablinks" onclick="openTab(event, 'feedback')">Feedback Form</button>
        </div>
        <!-- Tab content -->
        <main>
            <!-- Detailed Instructions Tab -->
            <div id="instructions" class="tabcontent">
                <h2>Detailed Instructions:</h2>
                <h4>Adding a crop:</h4>
                <ul>
                    <li>Step 1: Navigate to the <a href="crops.php">"Crops"</a> page.</li>
                    <li>Step 2: Click on the "Add Crop" button.</li>
                    <li>Step 3: Fill in the required information such as crop name, cultivated, planting date, etc.</li>
                    <li>Step 4: Click on the "Save" button to add the crop to your system.</li>
                    <li>Repeat the above steps for adding soil data, managing farm details, tasks, etc.</li>
                </ul>
                <h4>Adding Soil data</h4>
                <ul>
                    <li>Step 1: Navigate to the <a href="dashboard.php"><i class='bx bx-cog'></i></a> in the dashboard.</li>
                    <li>Step 2: Click on the "Soil Details" tab.</li>
                    <li>Step 3: Fill in the required information such as soil temperature, pH level and soil moisture</li>
                    <li>Step 4: Click on the "Save" button to add the soil data to your system.</li>
                    <li>Repeat the above steps for managing farm details and crops</li>
                </ul>
                <h4>Editing User Profile</h4>
                <ul>
                    <li>Step 1: Navigate to the <a href="dashboard.php"><i class='bx bx-cog'></i></a> in the dashboard.</li>
                    <li>Step 2: Click on the "Edit Profile" tab.</li>
                    <li>Step 3: Fill in the required information such as name, email</li>
                    <li>Step 4: Click on the "Update" button to update your profile.</li>
                    <li>Repeat the above steps for managing farm details and crops</li>
            </div>

            <!-- Troubleshooting Tab -->
            <div id="troubleshooting" class="tabcontent">
                <h2>Troubleshooting:</h2>
    <p>If you encounter any issues while using the system, here are some common problems and their solutions:</p><br>
    <ul>
        <li><strong>Problem:</strong> Unable to add a new crop.</li>
        <p><strong>Solution:</strong> Make sure all required fields are filled out correctly. Check for any error messages or validation alerts that may indicate missing or incorrect information.</p><br>

        <li><strong>Problem:</strong> Error message "Failed to retrieve weather data."</li>
        <p><strong>Solution:</strong> Check your internet connection and try again. If the problem persists, contact support for further assistance.</p><br>

        <p><strong>Problem:</strong> If you're experiencing issues with charts not displaying, follow these troubleshooting steps:</p>
    <ol>
        <li><strong>Check Internet Connection:</strong> Ensure that your device is connected to the internet. Charts may fail to load if there is no internet connection.</li>
        <li><strong>Refresh the Page:</strong> Sometimes, a simple page refresh can resolve the issue. Try refreshing the page to see if the charts load correctly.</li>
        <li><strong>Browser Compatibility:</strong> Make sure you are using a modern web browser that supports the charting library being used. Some older browsers may not display charts properly.</li>
        <li><strong>Clear Browser Cache:</strong> Clear your browser's cache and cookies. Cached data may sometimes interfere with the loading of charts.</li>
        <li><strong>Contact Support:</strong> If the issue persists, contact our support team for further assistance. Provide them with details about the problem and any error messages you're encountering.</li>
    </ol>
            </div>

            <!-- Contact Information Tab -->
            <div id="contact" class="tabcontent">
                <h2>Contact Information:</h2>

    <p>For any inquiries or assistance, please feel free to contact us:</p>
    <ul>
        <li>Email: <a href="#">support@example.com</a></li>
        <li>Phone:<a href="#">+1234567890</a></li>
        <li>Address:<a href="#"> 123 Street, City, Country</a></li>
    </ul>
</div>

            

            <!-- Feedback Form Tab -->
            <div id="feedback" class="tabcontent">
                <h2>Feedback Form:</h2>
                <p>We value your feedback! Please use the form below to share any comments, suggestions, or concerns you have about the system.</p>
    <form class="feedback-form" action="feedback.php" method="post">
        <label for="name">Your Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo isset($userData['FullName']) ? $userData['FullName'] : 'user not found'; ?>"><br>
        <label for="email">Your Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo isset($userData['Email']) ? htmlspecialchars($userData['Email']) : ''; ?>"><br><br>
        <label for="feedback">Feedback:</label><br>
        <textarea id="feedback" name="feedback" rows="4" cols="50" required></textarea><br>
        
        <input type="submit" value="Submit">
    </form>


            </div>
        </main>
    </div>
        </div>
        <script>
    // Function to open the default tab when the page loads
    window.onload = function() {
        // Get the first tab button and tab content
        var defaultTabButton = document.querySelector('.tab button:first-child');
        var defaultTabContent = document.querySelector('.tabcontent:first-child');

        // Add 'active' class to the default tab button and show the default tab content
        defaultTabButton.classList.add('active');
        defaultTabContent.style.display = 'block';
    };

    // Function to switch between tabs
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;

        // Hide all tab contents
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Remove 'active' class from all tab buttons
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }

        // Show the clicked tab content and set the clicked tab as active
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("active");
    }
</script>
</body>
</html>
