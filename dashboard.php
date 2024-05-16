<?php
// Start session
session_start();

// Check if user is logged in (i.e., if the user ID session variable is set)
if (!isset($_SESSION["user_id"])) {
    // User is not logged in, redirect to the sign-in page or display an error message
    header("Location: signin.html"); // Redirect to the sign-in page
    exit();
}
$userID = $_SESSION["user_id"]; // Get the user ID from the session
// Connect to the database
include 'db_connection.php';



// Get the current date
$current_date = date('l, j F Y');

// OpenWeatherMap API key
$api_key = '24e1513960412ddb40d604372e879cf9';

// Location coordinates (latitude and longitude) based on the selected location
$coordinates = array(
    "Kiambu" => array("lat" => 1.1714, "lon" => 36.8356),
    "Nairobi" => array("lat" => -1.286389, "lon" => 36.817223),
    "Mombasa" => array("lat" => -4.0435, "lon" => 39.6682),
    "Kisumu" => array("lat" => -0.1022, "lon" => 34.7617),
    "Trans Nzoia" => array("lat" => 1.0414, "lon" => 34.9444)
);

//echo user ID to javascript
echo "<script>const userId = " . $userID . ";</script>";



// Prepare SQL statement to fetch FarmID for the user
$sql = "SELECT FarmID FROM farms WHERE UserID = ?";
$stmt = $conn->prepare($sql);

// Check if prepare() succeeded
if ($stmt === false) {
    die("Error preparing SQL statement: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("i", $userID);

// Execute SQL statement
$stmt->execute();

// Get result
$result = $stmt->get_result();


    //get username
    $username = "SELECT Username FROM users WHERE UserID = ?";
    $stmt_username = $conn->prepare($username);
    $stmt_username->bind_param("i", $userID);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $row_username = $result_username->fetch_assoc();
    $username = $row_username["Username"];

// Close the statement
$stmt_username->close();



// Check if any rows are returned
if ($result->num_rows == 1) {
    // Fetch FarmID
    $row = $result->fetch_assoc();
    $farm_id = $row["FarmID"];


    // Prepare SQL statement to fetch farm details
    $sql_farm = "SELECT * FROM farms WHERE FarmID = ?";
    $stmt_farm = $conn->prepare($sql_farm);

    // Check if prepare() succeeded
    if ($stmt_farm === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Bind parameters
    $stmt_farm->bind_param("i", $farm_id);

    // Execute SQL statement
    $stmt_farm->execute();

    // Get result
    $result_farm = $stmt_farm->get_result();


    // Check if any rows are returned
    if ($result_farm->num_rows > 0) {
    // Output data of each row
     while ($row_farm = $result_farm->fetch_assoc()) {

        
 // Getting location from farm details
$location = $row_farm['Location'];

// Function to fetch weather data from API and store it in the database
function fetchAndStoreWeatherData($latitude, $longitude, $farm_id, $location) {
    try {
        // API key and URL
        $apiKey = '24e1513960412ddb40d604372e879cf9';
        $apiUrl = "https://pro.openweathermap.org/data/2.5/forecast/climate?lat={$latitude}&lon={$longitude}&units=metric&appid={$apiKey}";

        // Make the API request
        $response = file_get_contents($apiUrl);
        $climateData = json_decode($response, true);

        // Check if data was successfully retrieved
        if ($climateData && isset($climateData['list'])) {
            // Extract weather parameters for each day
            $temperatureData = array_map(function ($day) {
                return $day['temp']['day'];
            }, $climateData['list']);
            $precipitationData = array_map(function ($day) {
                return isset($day['rain']) ? $day['rain'] : 0;
            }, $climateData['list']);

            // Aggregate data to calculate summary statistics
            $temperatureMean = array_sum($temperatureData) / count($temperatureData);
            $precipitationTotal = array_sum($precipitationData);

            // Get the name of the current month
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $currentMonth = $months[date('n') - 1];

            // Connect to the database
            $conn = new mysqli("localhost", "root", "", "agri");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Check if weather data for the current month already exists
            $sql_check_month = "SELECT COUNT(*) AS count FROM weather_data WHERE farmID = ? AND MONTH(month) = MONTH(CURRENT_DATE)";
            $stmt_check_month = $conn->prepare($sql_check_month);

            // Check if prepare() succeeded
            if ($stmt_check_month === false) {
                throw new Exception("Error preparing SQL statement: " . $conn->error);
            }

            // Bind parameters
            $stmt_check_month->bind_param("i", $farm_id);

            // Execute SQL statement
            $stmt_check_month->execute();

            // Get result
            $result_check_month = $stmt_check_month->get_result();

            // Check if any rows are returned
            if ($result_check_month->num_rows > 0) {
                // Output data of each row
                while ($row_check_month = $result_check_month->fetch_assoc()) {
                    $weather_data_exists = $row_check_month['count'] > 0;
                }
            } else {
                $weather_data_exists = false;
            }

            // Close statement
            $stmt_check_month->close();

            if (!$weather_data_exists) {
                // Insert weather data into the database
                insertWeatherDataIntoDatabase($temperatureMean, $precipitationTotal, $currentMonth, $farm_id, $location);
            } else {
                echo "Weather data for the current month already exists in the database.";
            }

            // Close connection
            $conn->close();
        } else {
            echo "Failed to parse climate data.";
        }
    } catch (Exception $error) {
        echo "Error fetching and storing weather data: {$error->getMessage()}";
    }
}
// Fetch soil data from the database
$sql = "SELECT temperature, ph, moisture FROM soil_data WHERE farmID = ? ORDER BY date DESC LIMIT 1"; // Assuming you want the latest soil data
$stmt_soil = $conn->prepare($sql);
$stmt_soil->bind_param("i", $farm_id); // Assuming 'farm_id' is set in the session
$stmt_soil->execute();
$result = $stmt_soil->get_result();

// Check if there is soil data available
if ($result->num_rows > 0) {
    // Fetch the first row of soil data
    $row = $result->fetch_assoc();
    // Store soil data in variables
    $temperature = $row['temperature'];
    $ph = $row['ph'];
    $moisture = $row['moisture'];



} else {
    // Default values if no soil data found
    $temperature = 0;
    $ph = 0;
    $moisture = 0;
}
    //send them to javascript
    echo "<script>";
    echo "const soil_temp = " . $temperature . ";";
    echo "const ph = " . $ph . ";";
    echo "const moisture = " . $moisture . ";";
    echo "</script>";

// Close statement and database connection
$stmt_soil->close();
function insertWeatherDataIntoDatabase($temperatureMean, $precipitationTotal, $currentMonth, $farm_id, $location) {
    try {
        // Connect to the database
        $conn = new mysqli("localhost", "root", "", "agri");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare SQL statement to insert weather data
        $sql = "INSERT INTO weather_data (temperature, rainfall, month, farmID, location, country) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Define country variable
        $country = "Kenya";

        // Bind parameters
        $stmt->bind_param("ddsiss", $temperatureMean, $precipitationTotal, $currentMonth, $farm_id, $location, $country);

        // Execute statement
        $stmt->execute();

        // echo "Weather data inserted successfully.";

        // Close statement
        $stmt->close();
        
        // Close connection
        $conn->close();
    } catch (Exception $error) {
        echo "Error inserting weather data: {$error->getMessage()}";
    }
}



// Check if it's the fifth day of the month
function isFirstDayOfMonth() {
    return date('j') === '1';
}

// Example usage
if (isFirstDayOfMonth()) {
    fetchAndStoreWeatherData($coordinates[$location]["lat"], $coordinates[$location]["lon"], $farm_id, $location);
}


    




// Get coordinates based on the selected location
$lat = $coordinates[$location]["lat"];
$lon = $coordinates[$location]["lon"];



// Construct the URL for the current day
$url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$api_key}";

// Make the API request
$response = file_get_contents($url);
if ($response !== false) {
    // Parse JSON response
    $weather_data = json_decode($response, true);

        // Extract relevant weather information
        $temperature_kelvin = $weather_data['main']['temp'];
        $temperature_celsius = $temperature_kelvin - 273.15; // Conversion from Kelvin to Celsius
        $humidity = $weather_data['main']['humidity'];
        $description = $weather_data['weather'][0]['description'];
        $rainfall_mm = isset($weather_data['rain']['1h']) ? $weather_data['rain']['1h'] : 0; // Get rainfall in the last 1 hour (default to 0 if not available)

        // Estimate sunshine duration (in hours)
        $cloudiness = $weather_data['clouds']['all'] / 100; // Convert cloudiness percentage to fraction
        $sunrise = date('Y-m-d H:i:s', $weather_data['sys']['sunrise']);
        $sunset = date('Y-m-d H:i:s', $weather_data['sys']['sunset']);
        $daylight_hours = (strtotime($sunset) - strtotime($sunrise)) / 3600; // Convert daylight duration to hours
        $sunshine_duration = $daylight_hours * (1 - $cloudiness);

        


} else {
    echo "Error retrieving weather data";
}


// Function to fetch weather forecast data
function fetchWeatherForecast($latitude, $longitude) {
    $apiKey = '24e1513960412ddb40d604372e879cf9'; // Replace with your OpenWeatherMap API key
    $apiUrl = "https://api.openweathermap.org/data/2.5/forecast/daily?lat={$latitude}&lon={$longitude}&cnt=7&appid={$apiKey}";

    try {
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        // Extract precipitation forecast data for the next 7 days
        $precipitationData = array_map(function ($item) {
            return [
                'date' => date('Y-m-d', $item['dt']), // Extract date
                'precipitation' => isset($item['rain']) ? $item['rain'] : 0 // Extract precipitation (assuming rain data is provided)
            ];
        }, $data['list']);

        return $precipitationData;
    } catch (Exception $error) {
        error_log('Error fetching weather forecast: ' . $error->getMessage());
        return null;
    }
}

// Fetch the weather forecast for a location
$rainfallForecastData = fetchWeatherForecast($coordinates[$location]["lat"], $coordinates[$location]["lon"]);

// Send location data to JavaScript via JSON
echo "<script>";
echo "const rainfallForecastData = " . json_encode($rainfallForecastData) . ";";
echo "</script>";




?>
        

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/icons/boxicons-master/css/boxicons.css">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- <script src="script.js"></script> -->
    <title>AgriPlus | Dashboard</title>
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
                    <li><a href="dashboard.php" class="active"><i class='bx bxs-dashboard'></i>Overview</a></li>
                    <li><a href="crops.php"><i class='fa-solid fa-seedling'></i>Crops</a></li>
                    <li><a href="tasks.php"><i class='bx bx-task'></i>Tasks</a></li>
                    <li><a href="analytics.php"><i class='bx bxs-report' ></i>Analytics</a></li>
                    <li><a href="help.php"><i class='bx bx-help-circle' ></i></i>Help</a></li>
                    <li><a href="logout.php"><i class='bx bx-exit'></i>Logout</a></li>
                </ul>
        </div>
       </div> 
       
<!-- ----------------------------main content-------------------------------- -->
<div class="main-content">
            <div class="left-main">
                <div class="top">
                    <h2>Welcome back, <span><?php echo $username ?>!</span></h2><br>
                    <!-- <p>Here's what's happening with your farm today</p>                     -->
                </div>
                <div class="date">
                    <i class='bx bx-calendar'></i>
                    <p><?php echo $current_date ?></p>
                </div>             

                <div class="weather-cards">

                        <div class="card-temp">
                            <div class="sun-icon">
                                <i class='bx bxs-sun'></i>
                            </div>
                            <div class="title">
                                <h4 class="lbl"><?php
// Debugging

// Ensure that $daylight_hours is defined and contains a value
if(isset($daylight_hours)) {
    // Convert daylight_hours to integer using intval
    $daylight_hours_int = intval($daylight_hours);
    echo $daylight_hours_int;
} else {
    echo "Variable \$daylight_hours is not set or has no value.";
}
?> hours</h5>
                                <h4>Sunshine Duration</h4>
                            </div>
                        </div>
                        <div class="card">
                            <div class="temp-icon">
                                <i class="fa-solid fa-thermometer-half"></i>
                            </div>
                            <div class="temp">
                                <h4 class="lbl"><?php echo number_format($temperature_celsius, 2);?>&nbsp;°</h5>
                                <h4>Temperature</h4>
                            </div>
                        </div>
    
                    <div class="card">
                        <div class="rain-icon">
                            <i class="fa-solid fa-cloud-rain"></i>
                        </div>
                        <div class="title">
                            <h4 class="lbl"><?php echo  number_format($rainfall_mm, 2); ?> mm (last hour)</h5>
                            <h4>Rainfall</h4>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="humid-icon">
                            <i class="fa-solid fa-wind"></i>
                        </div>
                        <div class="humid">
                            <h4 class="lbl"><?php echo $humidity; ?>%</h5>
                            <h4>Humidity</h4>
                        </div>
                    </div>
    
                </div>

       
                <div class="down-space">
    
                    <div class="card">
                    <div class="soil">
    <h4>Soil Conditions</h4>
    <div class="soil-cards">
        <div class="property">
            <div class="moisture-icon">
                <i class="fa-solid fa-droplet"></i>
            </div>
            <div class="title">
                <h5>Moisture level</h5>
                <h3 id="moisture"><?php echo $moisture; ?>%</h3>
                <h6><?php echo ($moisture > 50) ? 'Too Wet' : 'Optimal'; ?></h6>
            </div>
        </div>

        <div class="property">
            <div class="ph-icon">
                <i class="fa-solid fa-water"></i>
            </div>
            <div class="title">
                <h5>pH level</h5>
                <h3 id="ph"><?php echo $ph; ?></h3>
                <h6><?php echo ($ph == 7) ? 'Neutral' : (($ph > 7) ? 'Alkaline' : 'Acidic'); ?></h6>

            </div>
        </div>

        <div class="property">
            <div class="temp-icon">
                <i class="fa-solid fa-thermometer-half"></i>
            </div>
            <div class="title">
                <h5>Temperature</h5>
                <h3 id="soil-temp"><?php echo $temperature; ?>°</h3>
                <h6><?php echo ($temperature > 30) ? 'Too Hot' : 'Optimal'; ?></h6>
            </div>
        </div>
    </div>
</div>

  
                    </div>
   
                    <div class="card">
                        <div class="right-down">

                            <div class="weather">
                                <h4>Rainfall Forecast</h4>
                                <div class="chart" id="forecast_chart"></div>
                            </div>
                        </div>

                          
                    </div>
                       
                </div>
            </div>

        <!-- // ---------------------------------------------------end of main content--------------------------------------------------------------- -->


            <?php
        }
    } 
    // Close farm statement
    $stmt_farm->close();
} else {
    echo "Farm ID not found for the user.";
}

// Close user statement and connection
$stmt->close();
?>

<?php

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

// Close connection
$stmt->close();

//fetch farm details and store it in array
$sql_farm = "SELECT FarmName, Location, FarmSize, IrrigationSystem, SoilType FROM farms WHERE FarmID = ?";
$stmt_farm = $conn->prepare($sql_farm);
$stmt_farm->bind_param("i", $farm_id);
$stmt_farm->execute();
$result_farm = $stmt_farm->get_result();

if ($result_farm->num_rows > 0) {
    $farmData = $result_farm->fetch_assoc();
} else {
    echo "Farm not found.";
}

$stmt_farm->close();

   


$sql_select = "SELECT taskName, dueDate FROM tasks WHERE farmID = ? AND dueDate < CURRENT_DATE() AND status IN ('due')";
$result = $conn->prepare($sql_select);
$result->bind_param("i", $farm_id);
$result->execute();
$result = $result->get_result();


if ($result->num_rows > 0) {
    // Initialize an array to store task alerts
    $taskAlerts = [];
    
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Construct the task alert message
        $taskAlert = " \"" . $row["taskName"] . "\" was due on " . $row["dueDate"];
        // Add task alert to the array
        $taskAlerts[] = $taskAlert;
    }

    // Output the task alerts as JSON
    echo "<script>var taskAlerts = " . json_encode($taskAlerts) . ";</script>";
} else {
    echo "<script>var taskAlerts = [];</script>"; // Initialize empty array if no tasks found
}



// Prepare SQL statement to fetch crop names from the database
$sql = "SELECT CropName FROM crops WHERE FarmID = $farm_id";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching crops: " . $conn->error);
}

// Check if the request is a POST request and if 'crop-type' parameter is set
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crop-type'])) {
    // Retrieve crop name from POST data
    $selectedCrop = test_input($_POST['crop-type']);

    // Connect to the database (adjust database credentials as needed)
    $conn = new mysqli("localhost", "root", "", "agri");

    // Check connection
    if ($conn->connect_error) {
        // Handle database connection error
        echo json_encode(array('error' => 'Database connection failed'));
        exit();
    }

    // Prepare and execute SQL query to fetch crop details
    $stmt = $conn->prepare("SELECT * FROM crops WHERE CropName = ? AND FarmID = $farm_id");
    $stmt->bind_param("s", $selectedCrop);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();

        // Store the fetched data in an array
        $cropDetails = array(
            'cropID' => $row['CropID'], // Add the cropID to the response
            'crop_name' => $row['CropName'],
            'cultivated_area' => $row['CultivatedArea'],
            'last_harvest_yield' => $row['LastHarvestYield'] * 90,
            'grow_stage' => $row['GrowthStage'],
            'water_needs' => $row['WateringNeeds'],
            'health_status' => $row['HealthStatus'],
            'last_harvest' => $row['LastHarvestYield']

        );

        

        // Send JSON response with the fetched data
        echo json_encode($cropDetails);
    } else {
        // No data found for the selected crop
        echo json_encode(array('error' => 'No data found for the selected crop'));
    }


    // Close statement and database connection
    $stmt->close();
}


?>



        <!-- ---------------------------------right side------------------------------------- -->

        

    <div class="right-side">

    <div class="settings">
              <div class="notification-panel">
                    <a href="#" class="notification" id="notificationIcon">
                        <i class='bx bx-bell'></i>
                        <span id="alertBadge"class="badge">&nbsp;</span>
                    </a>
                    <div class="dropdown" id="notificationDropdown">
                        <ul class="alerts">
                            
                        </ul>
                    </div>
               </div>
    
                 <!-- Profile section -->
                <div class="profile">
                 <a href="#" id="openModalBtn"><i class='bx bx-cog'></i></a>
                </div>
            </div>




<!-- Modal -->
<div class="modal" id="profileModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <!-- Tab links -->
        <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'UserProfile')">User Profile</button>
            <button class="tablinks" onclick="openTab(event, 'FarmDetails')">Farm Details</button>
            <button class="tablinks" onclick="openTab(event, 'SoilDetails')">Soil Details</button>
            <button class="tablinks" onclick="openTab(event, 'CropDetails')">Crop Details</button>
            <button class="tablinks" onclick="openTab(event, 'Activity')">Activity Log</button>
            <button class="tablinks" onclick="openTab(event, 'Reports')">Reports</button>
        </div>

        <!-- Tab content -->
        <div id="UserProfile" class="tabcontent">
    <h3>User Profile</h3>
    <form method="post" action="edit_profile.php">
        <label for="fullname">Fullname:</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo isset($userData['FullName']) ? $userData['FullName'] : 'user not found'; ?>" ><br>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo isset($userData['Username']) ? htmlspecialchars($userData['Username']) : ''; ?>"><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo isset($userData['Email']) ? htmlspecialchars($userData['Email']) : ''; ?>"><br>

        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" ><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" ><br>

        <button class="edit-button" type="submit" name="edit">Edit</button>
        <button class="delete-button" type="submit" name="delete">Delete</button>
    </form>
</div>


        <div id="FarmDetails" class="tabcontent">
            <h3>Farm Details</h3>
            <form method="post" action="">
            <div class="form-group">
                <label for="farm_name">Farm Name:</label>
                <input type="text" id="farm_name" name="farm_name" value="<?php echo isset($farmData['FarmName']) ? $farmData['FarmName'] : ''; ?>" >
            </div>

            <div class="form-group">
    <label for="location">Location:</label>
    <select id="location" name="location">
        <option value="">Select Location</option>
        <option value="Nairobi" <?php echo isset($farmData['Location']) && $farmData['Location'] == 'Nairobi' ? 'selected' : ''; ?>>Nairobi</option>
        <option value="Mombasa" <?php echo isset($farmData['Location']) && $farmData['Location'] == 'Mombasa' ? 'selected' : ''; ?>>Mombasa</option>
        <option value="Kisumu" <?php echo isset($farmData['Location']) && $farmData['Location'] == 'Kisumu' ? 'selected' : ''; ?>>Kisumu</option>
        <option value="Kiambu" <?php echo isset($farmData['Location']) && $farmData['Location'] == 'Kiambu' ? 'selected' : ''; ?>>Kiambu</option>
        <option value="Trans Nzoia" <?php echo isset($farmData['Location']) && $farmData['Location'] == 'Trans Nzoia' ? 'selected' : ''; ?>>Trans Nzoia</option>
    </select>
</div>


            <div class="form-group">
                <label for="farm_name">Farm Size(in Acres):</label>
                <input type="text" id="farm_size" name="farm_size" value="<?php echo isset($farmData['FarmSize']) ? $farmData['FarmSize'] : ''; ?>">
            </div>

            <div class="form-group">
    <label for="irrigation_system">Irrigation System:</label>
    <select id="irrigation_system" name="irrigation_system" required>
        <option value="">Select Irrigation System</option>
        <option value="Drip" <?php echo isset($farmData['IrrigationSystem']) && $farmData['IrrigationSystem'] == 'Drip' ? 'selected' : ''; ?>>Drip</option>
        <option value="Sprinkler" <?php echo isset($farmData['IrrigationSystem']) && $farmData['IrrigationSystem'] == 'Sprinkler' ? 'selected' : ''; ?>>Sprinkler</option>
        <option value="Flood" <?php echo isset($farmData['IrrigationSystem']) && $farmData['IrrigationSystem'] == 'Flood' ? 'selected' : ''; ?>>Flood</option>
    </select>
</div>

<div class="form-group">
    <label for="soil_type">Soil Type:</label>
    <select id="soil_type" name="soil_type" required>
        <option value="">Select Soil Type</option>
        <option value="Loam" <?php echo isset($farmData['SoilType']) && $farmData['SoilType'] == 'Loam' ? 'selected' : ''; ?>>Loam</option>
        <option value="Clay" <?php echo isset($farmData['SoilType']) && $farmData['SoilType'] == 'Clay' ? 'selected' : ''; ?>>Clay</option>
        <option value="Sand" <?php echo isset($farmData['SoilType']) && $farmData['SoilType'] == 'Sand' ? 'selected' : ''; ?>>Sand</option>
    </select>
</div>


           

            <div class="form-group">
            <button class="edit-button" type="submit" name="edit">Edit</button>
            <button class="delete-button" type="submit" name="delete">Delete</button>
        </div>
        </form>
        </div>

        <div id="SoilDetails" class="tabcontent">
    <h3>Soil Details</h3>
    <form id="soil-form" method="POST" action="add_soil.php">
        <label for="soil-temperature">Soil Temperature:</label>
        <input type="text" id="soil-temperature" name="soil-temperature" required><br>

        <label for="ph-level">pH Level:</label>
        <input type="number" id="ph-level" name="ph-level" required><br>

        <label for="soil-moisture">Soil Moisture:</label>
        <input type="text" id="soil-moisture" name="soil-moisture" required><br>

        <div class="form-group">
            <button class="edit-button" type="submit" name="add">Add Data</button>
        </div>
    </form>
</div>


        <div id="CropDetails" class="tabcontent">
            <h3>Crop Details</h3>
            <form id="crop-form" method="POST" action="edit_crops.php">


        <label for="crop-name">Crop Name:</label>
        <?php

            
// Check if query is successful and if there are any rows returned
if ($result && $result->num_rows > 0) {
    echo "<select id='crop-type' name='crop-type'>";
    echo "<option value=''>Select Crop</option>"; // Option for default selection
    // Fetch and display each crop name as an option in the dropdown
    while ($row = $result->fetch_assoc()) {
        $crop_name = $row["CropName"];
        echo "<option value='$crop_name'>$crop_name</option>";
    }
    echo "</select>";
} else {
    // If no crops found, display a message
    echo "<select id='crop-type' name='crop-type'>";
    echo "<option value=''>No crops found</option>";
    echo "</select>";
}


            ?>

        <input type="hidden" id="crop-id" name="crop-id"><br>


        <label for="cultivated_area">Cultivated Area:</label>
        <input type="number" id="cultivated_area" name="cultivated_area"><br>
        
        <label for="growth-stage">Growth Stage:</label>
        <input type="text" id="growth-stage" name="growth-stage"><br>
              
        <label for="watering-needs">Watering Needs:</label>
        <input type="text" id="watering-needs" name="watering-needs"><br>
        
        <label for="health-status">Health Status:</label>
        <input type="text" id="health-status" name="health-status"><br>

        <div class="form-group">
            <button class="edit-button" type="submit" name="edit">Edit</button>
            <button class="delete-button" type="submit" name="delete">Delete</button>
        </div>
        
        
      </form>
        </div>

        <div id="Activity" class="tabcontent">
            <h3>Activity Log</h3>
        <?php
        // Retrieve activity log records from the database for the current user

$sql = "SELECT user_id, activity_message, timestamp FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Check if the prepare() method failed
if ($stmt === false) {
    echo "Error preparing SQL statement: " . $conn->error;
    $conn->close();
    exit; // Stop execution in case of an error
}

// Bind the user ID parameter
$stmt->bind_param("i", $userID);

// Execute the SQL statement

$result = $stmt->execute();

// Get the result set

$result = $stmt->get_result();

// Display activity log content
if ($result->num_rows > 0) {

    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        $user_id = $row["user_id"];
        $activity_message = $row["activity_message"];
        $timestamp = $row["timestamp"];
        echo "<li>  $activity_message at $timestamp</li>";
    }
    echo '</ul>';
} else {
    echo "No activity log records found.";
}
// Check if the query was successful
if (!$result) {
    echo "Error executing query: " . $conn->error;
    $conn->close();
    exit; // Stop execution in case of an error
}

?>
        </div>
<!-- ------------------------reports---------------- -->
        <div id="Reports" class="tabcontent">
            <h3>Reports</h3>
            <?php

// Fetch data from the yields table and order by month
$sql = "SELECT cropName, lastHarvestYield, yield_predicted, month 
        FROM yields WHERE farmID = ? ORDER BY month";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Check if the prepare() method failed
if ($stmt === false) {
    echo "Error preparing SQL statement: " . $conn->error;
    $conn->close();
    exit; // Stop execution in case of an error
}

// Bind the farm ID parameter
$stmt->bind_param("i", $farm_id);

// Execute the SQL statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

echo '<div class="table-responsive">';
echo '<h4>Yield Reports</h4>';

// Generate HTML table
$table = '<table border="1">
            <tr>
                <th>Crop Name</th>
                <th>Last Harvest Yield</th>
                <th>Yield Predicted</th>
                <th>Month</th>
            </tr>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expected_yield = intval($row['yield_predicted'] / 90);
        $table .= '<tr>';
        $table .= '<td>' . $row['cropName'] . '</td>';
        $table .= '<td>' . $row['lastHarvestYield'] . '</td>';
        $table .= '<td>' . $expected_yield . '</td>';
        $table .= '<td>' . $row['month'] . '</td>';
        $table .= '</tr>';
    }
} else {
    $table .= '<tr><td colspan="4">No data available</td></tr>';
}

$table .= '</table>';
echo $table;

echo '</div>'; 

// Close the prepared statement
$stmt->close();

// Fetch weather data from the database based on farmID
$sql = "SELECT location, month, temperature, rainfall FROM weather_data WHERE farmID = ? ORDER BY month DESC"; // Modify the query to select the desired fields
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farm_id); // Assuming 'farm_id' is set in the session
$stmt->execute();
$result = $stmt->get_result();

// Check if there is weather data available
if ($result->num_rows > 0) {
    // Generate HTML table
    echo '<div class="table-responsive">';
    echo '<h4>Weather Report</h4>';

    $table = '<table border="1">
                <tr>
                    <th>Location</th>
                    <th>Month</th>
                    <th>Temperature (°C)</th>
                    <th>Rainfall (mm)</th>
                </tr>';

    while ($row = $result->fetch_assoc()) {
        $table .= '<tr>';
        $table .= '<td>' . $row['location'] . '</td>';
        $table .= '<td>' . $row['month'] . '</td>';
        $table .= '<td>' . $row['temperature'] . '</td>';
        $table .= '<td>' . $row['rainfall'] . '</td>';
        $table .= '</tr>';
    }

    $table .= '</table>';
    echo $table;

    echo '</div>';
} else {
    // No weather data found
    echo "<p>No weather data available.</p>";
}

// Close statement and database connection
$stmt->close();
$conn->close();
?>

        </div>


    </div>
</div>


   

<div class="facts">
    <header>Did you know!</header>
    <p id="factOfTheDay"></p>
</div>

        <div class="task-progress">
                      <h4>Tasks Progress</h4>
                      <div class="chart">
                          <div id="chart"></div>
                      </div>
        
        </div>
        
    </div>

        

    </div>

    </div>
    


<script>

document.getElementById('crop-type').addEventListener('change', function(){
    var selectedCrop = this.value;
    var farmID = <?php echo $farm_id; ?>;
    var cropID;
    var cropType = this.value;
    console.log('Selected crop:', selectedCrop);
    if (selectedCrop) {
        // Now, make AJAX request to fetch crop details and cropID
        var xhr = new XMLHttpRequest();
xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
            // Parse the JSON response
            var cropDetails = JSON.parse(xhr.responseText);

            console.log('Crop details:', cropDetails);

            // Check if cropDetails is not null or undefined
            if (cropDetails) {
                // Extract cropID if it exists
                var cropID = cropDetails.cropID;

                document.getElementById('crop-id').value = cropID;

                // Display crop details if they exist
                if (cropDetails.cultivatedArea !== null) {
                    document.getElementById('cultivated_area').value = cropDetails.cultivated_area;
                }
                if (cropDetails.growthStage !== null) {
                    document.getElementById('growth-stage').value = cropDetails.grow_stage;
                }
                if (cropDetails.wateringNeeds !== null) {
                    document.getElementById('watering-needs').value = cropDetails.water_needs;
                }
                if (cropDetails.healthStatus !== null) {
                    document.getElementById('health-status').value = cropDetails.health_status;
                }
            } else {
                console.error('Crop details are null or undefined.');
            }
        } else {
            console.error('Error fetching crop details:', xhr.status);
        }
    }
};

        // Initialize the request for the crop details
        xhr.open('POST', 'crops.php'); // Specify the correct PHP script URL here
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // Send the selected crop as the value of 'crop-type' parameter
        xhr.send('crop-type=' + encodeURIComponent(selectedCrop)); // Encode the selectedCrop to handle special characters
    }
});


    // Array of facts
var facts = [
    "Kenya is a leading exporter of tea, particularly black tea, grown in regions like Kericho and Nandi Hills.",
    "Coffee production is an important part of Kenya's agriculture sector, with high-quality Arabica coffee beans grown in areas like Nyeri and Kirinyaga.",
    "Horticulture is a growing industry in Kenya, with flowers such as roses, carnations, and lilies being exported to international markets.",
    "Agriculture accounts for about 75% of employment in Kenya.",
    "The Kenyan government has implemented initiatives to promote sustainable agriculture practices, including soil conservation and water management.",
    "Small-scale farmers play a significant role in Kenya's agriculture sector, producing crops such as maize, beans, and vegetables for local consumption and markets.",
    "Agribusiness is a growing sector in Kenya, with investments in processing, packaging, and value addition for agricultural products.",
    "Kenya's agricultural sector faces challenges such as climate change, pests, and diseases, but initiatives are underway to improve resilience and productivity.",
    "Soybean farming is gaining popularity among farmers in Kenya, with the crop being used for both human consumption and animal feed.",
    "Greenhouse farming is becoming increasingly common in Kenya, allowing for year-round cultivation of high-value crops like tomatoes and capsicum."
];


// Get today's date
var today = new Date();
// Use the day of the year as an index to select a fact
var factIndex = today.getDate() % facts.length;
// Display the fact of the day
document.getElementById("factOfTheDay").textContent = facts[factIndex];

// Function to display task alerts
function displayTaskAlerts() {
    // Loop through the taskAlerts array
    for (var i = 0; i < taskAlerts.length; i++) {
        // Display each task alert
        addAlertToList(taskAlerts[i]);
    }
}



// Function to check soil data and display alerts
function addSoilData(soil_temp, ph, moisture) {
    // Check soil temperature
    var tempAlert = "";
    if (soil_temp > 30) {
        tempAlert = "Soil is too hot!";
    } else if (soil_temp < 15) {
        tempAlert = "Soil is too cold!";
    }

    // Check pH level
    var phAlert = "";
    if (ph < 6) {
        phAlert = "Soil is too acidic!";
    } else if (ph > 9) {
        phAlert = "Soil is too alkaline!";
    }

    // Check soil moisture
    var moistureAlert = "";
    if (moisture < 30) {
        moistureAlert = "Low soil moisture - Irrigate the farm!";
    } else if (moisture > 80) {
        moistureAlert = "High soil moisture - Consider reducing watering!";
    }

    // Display alerts if needed
    if (phAlert !== "") {
        addAlertToList(phAlert);
    }
    if (moistureAlert !== "") {
        addAlertToList(moistureAlert);
    }
    if (tempAlert !== "") {
        addAlertToList(tempAlert);
    }
}

// Maximum number of alerts to display
var maxAlerts = 5;

// Function to add alert to the list
function addAlertToList(alertMessage) {
    // Get the number of alerts
    var numberOfAlerts = document.querySelectorAll("#notificationDropdown .alerts li").length;

    // Check if the maximum number of alerts has been reached
    if (numberOfAlerts < maxAlerts) {
        // Create new list item
        var newListItem = document.createElement("li");
        newListItem.textContent = alertMessage;

        // Append the new list item to the alerts list
        var alertsList = document.querySelector("#notificationDropdown .alerts");
        alertsList.appendChild(newListItem);

        // Update the alert badge to reflect the number of alerts
        updateAlertBadge();
    } else {
        // Optionally, you can handle what to do if the maximum number of alerts is reached
        console.log("Maximum number of alerts reached.");
    }
}

// Function to update alert badge visibility
function updateAlertBadge() {
    // Get the number of alerts
    var numberOfAlerts = document.querySelectorAll("#notificationDropdown .alerts li").length;

    // Update the alert badge with the number of alerts and toggle its visibility
    var alertBadge = document.getElementById("alertBadge");
    if (numberOfAlerts > 0) {
        alertBadge.textContent = numberOfAlerts; // Update badge content
        alertBadge.style.display = "inline-block"; // Show the badge if there are alerts
    } else {
        alertBadge.style.display = "none"; // Hide the badge if there are no alerts
    }
}

// Call the function to update the alert badge
    addSoilData(soil_temp, ph, moisture);
    displayTaskAlerts()







        // Function to generate line chart using ApexCharts
        async function generateLineChart() {
            // const precipitationData = await fetchWeatherForecast(latitude, longitude);
            if (!rainfallForecastData) return;

            const dates = rainfallForecastData.map(day => day.date);
            const precipitation = rainfallForecastData.map(day => day.precipitation);

            // Define chart options
            const options = {
                chart: {
                    type: 'line',
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Precipitation (mm)',
                    data: precipitation
                }],
                xaxis: {
                    categories: dates
                },
                stroke: {
                    curve: 'smooth',
                    width: 2, // Set the width of the line
                    colors: ['#45a049'] // Set the color of the line to green
                }
            };

            // Create chart
            const chart = new ApexCharts(document.querySelector("#forecast_chart"), options);
            chart.render();
        }

        // Generate line chart when the page loads
        window.onload = generateLineChart;

            // Open the modal when the profile picture is clicked
document.getElementById("openModalBtn").addEventListener("click", function() {
    document.getElementById("profileModal").style.display = "block";
});

// Close the modal when the close button is clicked
document.getElementsByClassName("close")[0].addEventListener("click", function() {
    document.getElementById("profileModal").style.display = "none";
});

// Open the default tab
document.getElementById("UserProfile").style.display = "block";

function openTab(evt, tabName) {
    // Get all elements with class="tabcontent" and hide them
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    var tablinks = document.getElementsByClassName("tablinks");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}


document.getElementById("notificationIcon").addEventListener("click", function(event) {
        event.stopPropagation(); // Prevents the click event from bubbling up to document
        var dropdown = document.getElementById("notificationDropdown");
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    });
    
    // Close the dropdown when clicking outside of it
    document.addEventListener("click", function(event) {
        var dropdown = document.getElementById("notificationDropdown");
        if (!event.target.matches('.notification')) {
            dropdown.style.display = "none";
        }
    });

            
// Function to fetch task completion percentage and update the chart
function updateTaskProgressChart() {
    // Send AJAX request to fetch task completion percentage
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'calculate_task_completion.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
          console.log(xhr.responseText);
            // Parse response JSON data
            const completionPercentage = JSON.parse(xhr.responseText);

            // Draw the chart
            const options = {
                chart: {
                    width: 250,
                    height: 250,
                    type: "radialBar"
                },
                labels: ["Tasks Completed"],
                series: [completionPercentage],
                colors: ["#45a049"],
                plotOptions: {
                    radialBar: {
                        hollow: {
                            margin: 10,
                            size: "75%"
                        },
                        dataLabels: {
                            showOn: "always",
                            name: {
                                offsetY: -10,
                                show: true,
                                color: "#888",
                                fontSize: "15px",
                                fontFamily: 'Montserrat'
                            },
                            value: {
                                offsetY: -2,
                                color: "#111",
                                fontSize: "15px",
                                fontWeight: "bold",
                                fontFamily: 'Montserrat',
                                show: true
                            },
                        }
                    }
                },
                stroke: {
                    lineCap: "round",
                },
            };

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        } else {
            console.error('Error:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send();
}

// Call the function to update the chart
updateTaskProgressChart();




</script>
</body>
</html>