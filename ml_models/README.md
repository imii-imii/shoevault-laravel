# ML Forecasting System for ShoeVault

This system provides machine learning-based sales forecasting using Facebook's Prophet model. It analyzes historical transaction data to generate accurate predictions for future sales periods.

## Features

- **Time Series Forecasting**: Uses Facebook Prophet for accurate time series predictions
- **Seasonal Patterns**: Automatically detects and accounts for daily, weekly, and yearly seasonality
- **Multiple Time Ranges**: Supports hourly, daily, weekly, quarterly, and yearly forecasts
- **Confidence Intervals**: Provides upper and lower bounds for predictions
- **Automatic Training**: Model trains automatically on first use if no trained model exists
- **Real-time Integration**: Seamlessly integrates with Laravel dashboard

## Setup

### Prerequisites

- Python 3.8 or higher
- pip package manager
- At least 30 days of historical transaction data

### Installation

1. **Run the setup script:**

   **Windows:**
   ```bash
   setup_ml.bat
   ```

   **Linux/Mac:**
   ```bash
   chmod +x setup_ml.sh
   ./setup_ml.sh
   ```

2. **Configure Laravel:**
   
   Add to your `.env` file:
   ```
   PYTHON_PATH=python
   ```
   
   (On some systems, you might need `PYTHON_PATH=python3`)

3. **Train the model:**
   
   The model will automatically train on first use, or you can manually trigger training:
   ```bash
   curl -X POST http://your-domain/owner/api/ml-forecast/train
   ```

## API Endpoints

### Generate Forecast
```
GET /owner/api/ml-forecast
```

Parameters:
- `type`: `sales` or `demand` (default: `sales`)
- `range`: `day`, `weekly`, `monthly`, `quarterly`, `yearly` (default: `weekly`)
- `periods`: Number of periods to forecast (optional, auto-calculated based on range)

Example:
```bash
curl "http://your-domain/owner/api/ml-forecast?range=weekly&type=sales"
```

### Train Model
```
POST /owner/api/ml-forecast/train
```

Parameters:
- `days`: Number of historical days to use for training (default: 365, min: 30, max: 1095)

### Model Status
```
GET /owner/api/ml-forecast/status
```

Returns information about whether the model is trained and ready.

### Export Historical Data
```
GET /owner/api/ml-forecast/export-data
```

Parameters:
- `days`: Number of days to export (default: 365)

## How It Works

1. **Data Collection**: The system exports historical transaction and reservation data from your Laravel database
2. **Data Preparation**: Sales data is aggregated by day and combined with reservation patterns
3. **Model Training**: Facebook Prophet analyzes the data for trends and seasonal patterns
4. **Prediction**: The trained model generates forecasts with confidence intervals
5. **Integration**: Predictions are formatted for the dashboard charts

## Forecast Periods

- **Day**: 24 hourly forecasts for tomorrow
- **Weekly**: 7 daily forecasts for the next week
- **Monthly**: 30 daily forecasts for the next month
- **Quarterly**: 12 weekly forecasts for the next 3 months
- **Yearly**: 12 monthly forecasts for the next year

## Model Performance

The system automatically validates model performance during training using metrics like:
- **MAE** (Mean Absolute Error): Average prediction error
- **RMSE** (Root Mean Square Error): Penalizes larger errors more heavily
- **MAPE** (Mean Absolute Percentage Error): Error as a percentage of actual values

## Troubleshooting

### Python Dependencies
If installation fails, try:
```bash
pip install --upgrade pip setuptools wheel
pip install -r ml_models/requirements.txt
```

### Model Training Issues
- Ensure you have at least 30 days of transaction data
- Check that transactions have `created_at` timestamps and `total_amount` values
- Verify that the database connection is working

### Permission Issues
On Linux/Mac, you might need to make the setup script executable:
```bash
chmod +x setup_ml.sh
```

### Windows-specific Issues
If you get execution policy errors, run PowerShell as administrator and execute:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

## File Structure

```
ml_models/
├── forecast_model.py          # Main Python ML script
├── requirements.txt           # Python dependencies
├── sales_forecast_model.pkl   # Trained model (generated)
└── venv/                      # Virtual environment (generated)

app/
├── Services/
│   └── MLForecastService.php  # Laravel service class
└── Http/Controllers/Owner/
    └── MLForecastController.php # API controller

setup_ml.sh                    # Linux/Mac setup script
setup_ml.bat                   # Windows setup script
```

## Technical Details

### Prophet Model Configuration
- **Seasonality Mode**: Multiplicative (better for sales data with varying seasonal effects)
- **Changepoint Prior Scale**: 0.05 (moderate flexibility for trend changes)
- **Seasonality Prior Scale**: 10.0 (high flexibility for seasonal patterns)
- **Custom Seasonalities**: Monthly patterns (30.5-day periods)

### Data Processing
- Daily aggregation of transaction amounts
- Integration with reservation data as additional regressor
- Automatic handling of missing dates (filled with zeros)
- Day-of-week and month features for improved accuracy

### Validation
- 20% of historical data reserved for validation
- Cross-validation metrics calculated automatically
- Fallback to mock data if ML prediction fails

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Test the Python script directly: `python ml_models/forecast_model.py status`
3. Verify API endpoints are accessible: `/owner/api/ml-forecast/status`