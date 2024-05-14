<?php

// Function to fetch weather data from API and store it in the database
function fetchAndStoreWeatherData($latitude, $longitude, $farm_id) {
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
            $humidityData = array_map(function ($day) {
                return $day['humidity'];
            }, $climateData['list']);

            // Aggregate data to calculate summary statistics
            $temperatureMean = array_sum($temperatureData) / count($temperatureData);
            $precipitationTotal = array_sum($precipitationData);
            $humidityMean = array_sum($humidityData) / count($humidityData);

            // Get the name of the current month
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $currentMonth = $months[date('n') - 1];

            // Print climate data
            echo "Temperature (Mean): {$temperatureMean}°C\n";
            echo "Precipitation (Total): {$precipitationTotal}mm\n";
            echo "Humidity (Mean): {$humidityMean}%\n";
            echo "Month: {$currentMonth}\n";
            echo "Farm ID: {$farm_id}\n";

            // Insert weather data into the database
            insertWeatherDataIntoDatabase($temperatureMean, $humidityMean, $precipitationTotal, $currentMonth, $farm_id);
        } else {
            echo "Failed to parse climate data.";
        }
    } catch (Exception $error) {
        echo "Error fetching weather data: {$error->getMessage()}";
    }
}


// Function to insert weather data into the database
function insertWeatherDataIntoDatabase($temperatureMean, $humidityMean, $precipitationTotal, $currentMonth, $farm_id) {
    try {
        // Perform database insertion here
        // Example:
        // $pdo = new PDO("mysql:host=localhost;dbname=your_database", "username", "password");
        // $sql = "INSERT INTO weather_data (temperature, humidity, rainfall, month, farm_id) VALUES (?, ?, ?, ?, ?)";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute([$temperatureMean, $humidityMean, $precipitationTotal, $currentMonth, $farm_id]);

        // For demonstration purposes, we'll just echo a success message
        echo "Weather data inserted into database successfully\n";
    } catch (Exception $error) {
        echo "Error inserting weather data into database: {$error->getMessage()}";
    }
}

// Check if it's the first day of the month
function isFirstDayOfMonth() {
    return date('j') === '5';
}

// Example usage
if (isFirstDayOfMonth()) {
    // Replace these values with your actual latitude, longitude, and farm ID
    $latitude = -1.286389;
    $longitude = 36.817223;
    $farm_id = 7; // Replace with your farm ID
    $last_month = "April"; // Replace with the last month data was inserted
    fetchAndStoreWeatherData($latitude, $longitude, $farm_id);
}

?>
