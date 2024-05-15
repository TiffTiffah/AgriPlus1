<?php
include 'WeatherForecast.php'; // Include the file containing the fetchWeatherForecast function

use PHPUnit\Framework\TestCase;

class WeatherForecastTest extends TestCase {
    public function testFetchWeatherForecast() {
        // Mock the API response
        $mockApiResponse = '{
            "list": [
                {"dt": 1620972000, "rain": 5},
                {"dt": 1621058400, "rain": 0},
                {"dt": 1621144800, "rain": 2},
                {"dt": 1621231200} // No rain data for this day
            ]
        }';

        // Mock the file_get_contents function to return the mock API response
        $mockResponse = $this->getMockBuilder('stdClass')
                             ->setMethods(['file_get_contents'])
                             ->getMock();
        $mockResponse->expects($this->once())
                     ->method('file_get_contents')
                     ->willReturn($mockApiResponse);

        // Inject the mock into the fetchWeatherForecast function
        $GLOBALS['mockResponse'] = $mockResponse;

        // Debug output to verify mock injection
        var_dump($GLOBALS['mockResponse']);

        // Call the fetchWeatherForecast function with mock latitude and longitude
        $latitude = -1.286389; 
        $longitude = 36.817223; 
        $weatherData = fetchWeatherForecast($latitude, $longitude);

        // Debug output to verify function call
        var_dump($weatherData);

        // Assert that the returned data follows expected patterns
        $this->assertIsArray($weatherData);
        $this->assertNotEmpty($weatherData);
        $this->assertCount(7, $weatherData); // Ensure data for 4 days
        $this->assertArrayHasKey('date', $weatherData[0]);
        $this->assertArrayHasKey('precipitation', $weatherData[0]);
        $this->assertIsNumeric($weatherData[0]['precipitation']); // Ensure precipitation is numeric
        $this->assertTrue($weatherData[0]['precipitation'] >= 0); // Ensure precipitation is non-negative
    }
}
?>
