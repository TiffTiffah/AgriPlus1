import sys
import joblib
import mysql.connector
import pandas as pd
import numpy as np
from sklearn.preprocessing import LabelEncoder

# Loading the pre-trained model
loaded_model = joblib.load('model.joblib')

# Loading LabelEncoders
le_item = LabelEncoder()
le_area = LabelEncoder()

# Loadong the fitted LabelEncoders (you need to save them during training)
le_item.classes_ = joblib.load('le_item_classes.joblib')
le_area.classes_ = joblib.load('le_area_classes.joblib')


def fetch_data_from_mysql(farm_id):
    # Connecting to MySQL
    connection = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='agri'
    )
    cursor = connection.cursor()

    # Executing a query to retrieve data
    query = f"SELECT w.temperature, w.rainfall, c.CropName, c.CropID, w.country, w.month FROM weather_data w JOIN crops c ON w.farmID = c.farmID WHERE w.farmID = {farm_id}"
    cursor.execute(query)

    # Fetching data into a DataFrame
    columns = [col[0] for col in cursor.description]
    data = cursor.fetchall()
    df = pd.DataFrame(data, columns=columns)

    cursor.close()
    connection.close()

    return df


def predict_crop_yield(row):
    # Transforming categorical features using Label Encoders
    item_encoded = le_item.transform([row['CropName']])[0]
    area_encoded = le_area.transform([row['country']])[0]

    # Preparing input data for prediction
    input_data = pd.DataFrame({
        'Area': [area_encoded],
        'Item': [item_encoded],
        'average_rain_fall_mm_per_year': [row['rainfall']],
        'avg_temp': [row['temperature']]
    })

    # Ensuring that columns are in the same order as during training
    input_data = input_data[['Area', 'Item', 'average_rain_fall_mm_per_year', 'avg_temp']]

    # Performing prediction
    prediction = loaded_model.predict(input_data)

    return prediction.item()  # Converting prediction to a single value if it's a numpy array


def convert_yield_to_kg_per_hectare(yields):
    return yields / 10


def convert_yield_to_kg_per_acre(yield_kg_per_ha):
    return yield_kg_per_ha * 2.47105

def convert_into_bags(yield_kg_per_acre):
    return yield_kg_per_acre/90


if __name__ == "__main__":
    # Getting the farm ID from command-line argument
    if len(sys.argv) != 2:
        print("Usage: python prediction.py <farm_id>")
        sys.exit(1)

    farm_id = sys.argv[1]

    # Fetching data from MySQL for the specified farm ID
    data_from_mysql = fetch_data_from_mysql(farm_id)

    # Setting the index of the DataFrame to the 'month' column
    data_from_mysql.set_index('month', inplace=True)

    # Connectting to MySQL
    connection = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='agri'
    )
    cursor = connection.cursor()

    # Iterating over each row in the DataFrame and make predictions
    for month, row in data_from_mysql.iterrows():
        # Check if prediction data already exists for the given month and cropID
        check_query = "SELECT COUNT(*) FROM yieldprediction WHERE month = %s AND CropID = %s"
        cursor.execute(check_query, (month, row['CropID']))
        existing_entries = cursor.fetchone()[0]

        if existing_entries > 0:
            # print(f"Prediction for {month} and CropID {row['CropID']} already exists. Skipping insertion.")
            continue  # Skip insertion if prediction data already exists

        # Making prediction
        prediction = predict_crop_yield(row)
        # print(f"Prediction for {month}: {prediction}")

        # Convert yield prediction from hg/ha to kg/ha
        prediction_kg_per_ha = convert_yield_to_kg_per_hectare(prediction)

        # Convert yield prediction from kg/ha to kg/acre
        prediction_kg_per_acre = convert_yield_to_kg_per_acre(prediction_kg_per_ha)
        # print(f"Prediction for {month} in kg/acre: {prediction_kg_per_acre}")

        # Convert yield prediction from kg/acre to bags
        prediction_bags = convert_into_bags(prediction_kg_per_acre)
        # print(f"Prediction for {month} in bags: {prediction_bags}")

        # Inseringt prediction data into the database
        try:
            insert_query = "INSERT INTO yieldprediction (month, yield_predicted, CropID, farmID) VALUES (%s, %s, %s, %s)"
            cursor.execute(insert_query, (month, prediction_kg_per_acre, row['CropID'], farm_id))
            connection.commit()  # Commit the transaction
            # print("Data inserted successfully")



        except Exception as e:
            print("Error inserting data:", e)

    # Close cursor and connection
    cursor.close()
    connection.close()
