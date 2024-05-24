import sys
import requests
from datetime import datetime, timedelta
import calendar
import mysql.connector

def retrieve_daily_weather_data(location):
    # OpenWeatherMap API key
    api_key = '24e1513960412ddb40d604372e879cf9'

    # Location coordinates (latitude and longitude) based on the selected location
    coordinates = {
        "Kiambu": {"lat": 1.1714, "lon": 36.8356},
        "Nairobi": {"lat": -1.286389, "lon": 36.817223},
        "Mombasa": {"lat": -4.0435, "lon": 39.6682},
        "Kisumu": {"lat": -0.1022, "lon": 34.7617},
        "Trans Nzoia": {"lat": 1.0414, "lon": 34.9444},
        "Machakos": {"lat": -1.5221, "lon": 37.0637},
    }

    # Get coordinates based on the selected location
    lat = coordinates[location]["lat"]
    lon = coordinates[location]["lon"]

    # Get the current date
    current_date = datetime.now()

    # Get the current month and year
    current_month = current_date.month
    current_year = current_date.year

    # List to store converted data
    weather_data_list = []

    # Loop backward through the last 'num_months' months
    for month_num in range(current_month - 1, 0, -1):
        # Decrement month by 1 in each iteration
        current_date -= timedelta(days=current_date.day)
        
        # Extract the month from the current date
        current_month = current_date.strftime('%m')
        month_name = calendar.month_name[month_num]

        # Construct the URL for the current month
        url = f'https://history.openweathermap.org/data/2.5/aggregated/month?lat={lat}&lon={lon}&month={current_month}&appid={api_key}'

        # Make the API request
        response = requests.get(url)

        if response.status_code == 200:
            # Parse JSON response
            weather_data = response.json()

            # Convert data and store in the list
            converted_data = convert_data(weather_data, month_name)
            weather_data_list.append(converted_data)

    # Return the list of converted data
    return weather_data_list

def convert_data(weather_data, month_name):
    # Extract relevant information and convert it into desired format
    temperature_celsius = weather_data['result']['temp']['average_max'] - 273.15  # Conversion from Kelvin to Celsius
    rainfall_mm = weather_data['result']['precipitation']['mean']

    # Converting precipitation rate from mm/h to mm for the whole month
    # Assuming average number of days in a month and average number of hours in a day
    average_days_in_month = 30.44  # Approximate value for a non-leap year
    average_hours_in_day = 24

    # Calculate total number of hours in the month
    total_hours_in_month = average_days_in_month * average_hours_in_day

    # Convert precipitation rate from mm/h to mm
    total_precipitation = rainfall_mm * total_hours_in_month

    converted_data = {
        'temperature': temperature_celsius,
        'rainfall': total_precipitation,
        'month': month_name
    }

    return converted_data

# Send data to the database
def send_data_to_the_db(weather_data_list, location, farmID):
    try:
        # Connect to MySQL
        connection = mysql.connector.connect(
            host='localhost',
            user='root',
            password='',
            database='agri'
        )
        cursor = connection.cursor()

        # Loop through weather data list and insert into the database
        for data in weather_data_list:
            query = "INSERT INTO weather_data (location, temperature, rainfall, month, country, farmID) VALUES (%s, %s, %s, %s, %s, %s)"
            cursor.execute(query, (location, data['temperature'], data['rainfall'], data['month'], 'Kenya', farmID))

        # Commit the transaction
        connection.commit()

    except mysql.connector.Error as error:
        print("Error inserting data into MySQL:", error)

    finally:
        # Close the cursor and connection
        cursor.close()
        connection.close()



# Check if a location and farmID are provided as command-line arguments
if len(sys.argv) != 3:
    print("Usage: python script_name.py <location> <farmID>")
    sys.exit(1)


# Get the location and farmID from command-line arguments
location = sys.argv[1]
farmID = sys.argv[2]

# Retrieve weather data
weather_data_list = retrieve_daily_weather_data(location)

# Send data to the database, passing the location
send_data_to_the_db(weather_data_list, location, farmID)



