<?php
// Location coordinates (latitude and longitude) based on the selected location
$coordinates = array(
    "Kiambu" => array("lat" => 1.1714, "lon" => 36.8356),
    "Nairobi" => array("lat" => -1.286389, "lon" => 36.817223),
    "Mombasa" => array("lat" => -4.0435, "lon" => 39.6682),
    "Kisumu" => array("lat" => -0.1022, "lon" => 34.7617),
    "Trans Nzoia" => array("lat" => 1.0414, "lon" => 34.9444)
);
$location = 'Nairobi'; 
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