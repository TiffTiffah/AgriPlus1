import unittest
from unittest.mock import MagicMock
from prediction import predict_crop_yield


class TestPrediction(unittest.TestCase):
    def test_predict_crop_yield(self):
        # Mock input row data
        row_data = {
            "CropName": "Maize",
            "country": "Kenya",
            "temperature": 25,
            "rainfall": 100
        }

        # Mock the loaded_model and le_item and le_area objects
        loaded_model = MagicMock()
        le_item = MagicMock()
        le_area = MagicMock()

        # Set up the return values for the LabelEncoder mocks
        le_item.transform.return_value = [0]  # Mocked encoded CropName
        le_area.transform.return_value = [0]  # Mocked encoded country

        # Mock the prediction result
        loaded_model.predict.return_value = 25987  # Mocked predicted yield

        # Call the function being tested
        prediction = predict_crop_yield(row_data)

        # Assert that the prediction is correct
        self.assertEqual(prediction, 25987)

if __name__ == '__main__':
    unittest.main()
