
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




//passing location to python script to get weather data
$output = shell_exec("python retrieve_weather.py $location $farm_id");
    
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
                                <h4 class="lbl"><? echo number_format($sunshine_duration, 2); ?> hours</h5>
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
                                        <h3>25%</h3>
                                        <h6>Optimal</h6>
                                    </div>
                                </div>
            
                                <div class="property">
                                    <div class="ph-icon">
                                        <i class="fa-solid fa-water"></i>
                                    </div>
                                    <div class="title">
                                        <h5>pH level</h5>
                                        <h3>8</h4>
                                        <h6>Neutral</h6>
                                    </div>
                                </div>
    
                                <div class="property">
                                    <div class="temp-icon">
                                        <i class="fa-solid fa-thermometer-half"></i>
                                    </div>
                                    <div class="title">
                                        <h5>Temperature</h5>
                                        <h3>25°</h3>
                                        <h6>Optimal</h6>
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



            

            // Check if the request is a POST request and if 'crop-type' parameter is set
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crop_name'])) {
    // Retrieve crop name from POST data
    $selectedCrop = test_input($_POST['crop_name']);

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
    $conn->close();
    exit(); // Exit to prevent further execution
}




document.getElementById('crop_name').addEventListener('change', function() {
    var selectedCrop = this.value;
    var farmID = <?php echo $farm_id; ?>;
    
    if (selectedCrop) {
        // Now, make AJAX request to fetch crop details and cropID
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Parse the JSON response
                    var cropDetails = JSON.parse(xhr.responseText);

                    console.log('Crop details:', cropDetails);

                    // Update specific elements with fetched crop details
                    document.getElementById('crop_size').textContent = cropDetails.cultivated_area + ' acres';
                    document.getElementById('last_production').textContent = cropDetails.last_harvest_yield + ' kgs';
                    document.getElementById('grow-stage').textContent = cropDetails.grow_stage;
                    document.getElementById('plant-health').textContent = cropDetails.health_status;
                    document.getElementById('water-needs').textContent = cropDetails.water_needs;
                    document.getElementById('last_harvest').textContent = cropDetails.last_harvest + ' bags';
                } else {
                    console.error('Failed to fetch crop details');
                }
            }
        };

        // Initialize the request for the crop details
        xhr.open('POST', 'dashboard.php'); // Specify the correct PHP script URL here
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        // Send the selected crop as the value of 'crop-type' parameter
        xhr.send('crop_name=' + encodeURIComponent(selectedCrop)); // Encode the selectedCrop to handle special characters
    } else {
        // Clear crop details if no crop is selected
        document.getElementById('crop_name').textContent = '';
        document.getElementById('crop_size').textContent = '';
        document.getElementById('last_production').textContent = '';
        document.getElementById('grow-stage').textContent = '';
        document.getElementById('plant-health').textContent = '';
        document.getElementById('water-needs').textContent = '';
        document.getElementById('last_harvest').textContent = '';
    }
});

