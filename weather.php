<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON data from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract weather data
    $location = $data['location'];
    $temperature = $data['temperature'];
    $humidity = $data['humidity'];
    $rainfall = $data['rainfall'];
    $month = $data['month'];
    $farm_id = $data['farm_id'];

    // Insert weather data into the database
    // Adjust this code to insert data into your database
    // Example code using MySQLi
    $conn = new mysqli("localhost", "root", "", "agri");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO weather_data (month, farm_id, temperature_mean, humidity_mean, precipitation_total,, country) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $country = "Kenya"; // Example country
    $stmt->bind_param("sddddss", $month, $farm_id, $temperature, $humidity,$location, $rainfall, $country);
    if ($stmt->execute()) {
        echo "Weather data inserted into database successfully";
    } else {
        echo "Error inserting weather data into database: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

