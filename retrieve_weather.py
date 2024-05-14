import requests
from datetime import datetime, timedelta
from collections import defaultdict

def get_climate_forecast(api_key, latitude, longitude):
    url = f"https://pro.openweathermap.org/data/2.5/forecast/climate?lat={latitude}&lon={longitude}&units=metric&appid={api_key}"
    response = requests.get(url)
    if response.status_code == 200:
        data = response.json()
        return data
    else:
        print("Failed to retrieve climate forecast data.")
        return None

def aggregate_weather_data(climate_forecast_data):
    daily_data = defaultdict(list)
    for day in climate_forecast_data['list']:
        date = datetime.utcfromtimestamp(day['dt']).strftime('%Y-%m-%d')
        if 'main' in day and 'temp' in day['main']:
            temperature = day['main']['temp']
        else:
            temperature = None  # Set temperature to None if data is missing
        if 'rain' in day:
            rainfall = day['rain'].get('1h', 0)  # Check for rainfall data, use 0 if not available
        else:
            rainfall = 0
        daily_data[date].append((temperature, rainfall))
    return daily_data




def main():
    api_key = '24e1513960412ddb40d604372e879cf9'
    latitude = 40.7128  # Example latitude (New York City)
    longitude = -74.0060  # Example longitude (New York City)
    climate_forecast_data = get_climate_forecast(api_key, latitude, longitude)
    if climate_forecast_data:
        daily_data = aggregate_weather_data(climate_forecast_data)
        # Print aggregated temperature and rainfall data for the whole month
        for date, data in daily_data.items():
            avg_temperature = sum(temp for temp, _ in data) / len(data)
            total_rainfall = sum(rain for _, rain in data)
            print(f"On {date}, the average temperature is {avg_temperature}°C and total rainfall is {total_rainfall} mm.")
    else:
        print("Exiting...")

if __name__ == "__main__":
    main()
