#!/usr/bin/env python3
"""
Sales Forecasting Model for ShoeVault
Uses Facebook Prophet for time series forecasting with seasonal patterns
"""

import sys
import json
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import warnings
warnings.filterwarnings('ignore')

try:
    from prophet import Prophet
    from sklearn.metrics import mean_absolute_error, mean_squared_error
    import joblib
    import os
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing required packages: {str(e)}. Please install: pip install prophet scikit-learn joblib pandas numpy"
    }))
    sys.exit(1)

class SalesForecastModel:
    def __init__(self, model_path='ml_models/sales_forecast_model.pkl'):
        self.model = None
        self.model_path = model_path
        self.is_trained = False
        
    def prepare_data(self, sales_data):
        """
        Prepare sales data for Prophet model
        Expected format: [{'date': 'YYYY-MM-DD', 'sales': float, 'reservations': float}, ...]
        """
        if not sales_data:
            raise ValueError("No sales data provided")
            
        df = pd.DataFrame(sales_data)
        
        # Convert date column to datetime
        df['ds'] = pd.to_datetime(df['date'])
        df['y'] = df['sales'].astype(float)
        
        # Add additional regressors if available
        additional_columns = []
        if 'reservations' in df.columns:
            df['reservations'] = df['reservations'].astype(float)
            additional_columns.append('reservations')
            
        if 'day_of_week' not in df.columns:
            df['day_of_week'] = df['ds'].dt.dayofweek
            
        if 'month' not in df.columns:
            df['month'] = df['ds'].dt.month
            
        return df[['ds', 'y'] + additional_columns + ['day_of_week', 'month']], additional_columns
    
    def train(self, sales_data, validation_split=0.2):
        """Train the Prophet model on historical sales data"""
        try:
            df, additional_columns = self.prepare_data(sales_data)
            
            if len(df) < 30:  # Need at least 30 days of data
                raise ValueError("Insufficient data for training. Need at least 30 days of historical data.")
            
            # Split data for validation
            split_index = int(len(df) * (1 - validation_split))
            train_df = df[:split_index]
            test_df = df[split_index:]
            
            # Initialize Prophet model with parameters optimized for daily sales data
            self.model = Prophet(
                daily_seasonality=True,
                weekly_seasonality=True,
                yearly_seasonality=True,
                seasonality_mode='multiplicative',
                changepoint_prior_scale=0.05,  # Controls flexibility of trend changes
                seasonality_prior_scale=10.0,   # Controls flexibility of seasonality
                holidays_prior_scale=10.0,      # Controls flexibility around holidays
                interval_width=0.80             # 80% confidence intervals
            )
            
            # Add additional regressors
            for col in additional_columns:
                self.model.add_regressor(col)
                
            # Add custom seasonalities for business patterns
            self.model.add_seasonality(name='monthly', period=30.5, fourier_order=5)
            
            # Fit the model
            self.model.fit(train_df)
            
            # Validate model performance
            validation_results = None
            if len(test_df) > 0:
                future_test = self.model.make_future_dataframe(periods=len(test_df))
                
                # Add regressors for prediction period
                for col in additional_columns:
                    if col in df.columns:
                        future_test[col] = df[col].iloc[:len(future_test)]
                
                # Add day_of_week and month for future dates
                future_test['day_of_week'] = future_test['ds'].dt.dayofweek
                future_test['month'] = future_test['ds'].dt.month
                
                forecast_test = self.model.predict(future_test)
                
                # Calculate validation metrics on test set
                actual_test = test_df['y'].values
                predicted_test = forecast_test['yhat'].iloc[split_index:].values
                
                mae = mean_absolute_error(actual_test, predicted_test)
                rmse = np.sqrt(mean_squared_error(actual_test, predicted_test))
                mape = np.mean(np.abs((actual_test - predicted_test) / actual_test)) * 100
                
                validation_results = {
                    'mae': float(mae),
                    'rmse': float(rmse),
                    'mape': float(mape),
                    'test_samples': len(actual_test)
                }
            
            self.is_trained = True
            self.save_model()
            
            return {
                'success': True,
                'message': 'Model trained successfully',
                'training_samples': len(train_df),
                'validation': validation_results
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': f'Training failed: {str(e)}'
            }
    
    def predict(self, periods, frequency='D', include_history=False):
        """
        Generate forecasts for the specified number of periods
        
        Args:
            periods (int): Number of periods to forecast
            frequency (str): Frequency of predictions ('D' for daily, 'H' for hourly)
            include_history (bool): Whether to include historical data in output
        """
        if not self.is_trained or self.model is None:
            if not self.load_model():
                return {
                    'success': False,
                    'error': 'Model not trained. Please train the model first.'
                }
        
        try:
            # Create future dataframe
            future = self.model.make_future_dataframe(periods=periods, freq=frequency, include_history=include_history)
            
            # Add additional features for future dates
            future['day_of_week'] = future['ds'].dt.dayofweek
            future['month'] = future['ds'].dt.month
            
            # Add regressor values for future (use recent averages or patterns)
            if 'reservations' in self.model.extra_regressors:
                # Use a simple pattern based on day of week for reservations
                weekend_multiplier = future['day_of_week'].apply(lambda x: 1.5 if x >= 5 else 1.0)
                base_reservations = 20  # Base daily reservations
                future['reservations'] = base_reservations * weekend_multiplier
            
            # Generate forecast
            forecast = self.model.predict(future)
            
            # Prepare output
            if include_history:
                results = forecast[['ds', 'yhat', 'yhat_lower', 'yhat_upper']].to_dict('records')
            else:
                results = forecast[['ds', 'yhat', 'yhat_lower', 'yhat_upper']].tail(periods).to_dict('records')
            
            # Format results
            formatted_results = []
            for row in results:
                formatted_results.append({
                    'date': row['ds'].strftime('%Y-%m-%d'),
                    'datetime': row['ds'].strftime('%Y-%m-%d %H:%M:%S'),
                    'forecast': max(0, float(row['yhat'])),  # Ensure non-negative
                    'lower_bound': max(0, float(row['yhat_lower'])),
                    'upper_bound': max(0, float(row['yhat_upper']))
                })
            
            return {
                'success': True,
                'predictions': formatted_results,
                'periods': periods,
                'frequency': frequency
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': f'Prediction failed: {str(e)}'
            }
    
    def save_model(self):
        """Save the trained model to disk"""
        try:
            os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            joblib.dump({
                'model': self.model,
                'is_trained': self.is_trained,
                'timestamp': datetime.now().isoformat()
            }, self.model_path)
            return True
        except Exception as e:
            print(f"Warning: Could not save model: {str(e)}")
            return False
    
    def load_model(self):
        """Load a trained model from disk"""
        try:
            if os.path.exists(self.model_path):
                data = joblib.load(self.model_path)
                self.model = data['model']
                self.is_trained = data['is_trained']
                return True
            return False
        except Exception as e:
            print(f"Warning: Could not load model: {str(e)}")
            return False

def main():
    """Main function to handle command line arguments"""
    if len(sys.argv) < 2:
        print(json.dumps({
            "success": False,
            "error": "Usage: python forecast_model.py <command> [args]"
        }))
        sys.exit(1)
    
    command = sys.argv[1]
    model = SalesForecastModel()
    
    if command == 'train':
        # Expect training data as JSON input
        try:
            if len(sys.argv) > 2:
                # Data provided as file path
                with open(sys.argv[2], 'r') as f:
                    training_data = json.load(f)
            else:
                # Data provided via stdin
                training_data = json.load(sys.stdin)
            
            result = model.train(training_data)
            print(json.dumps(result))
            
        except Exception as e:
            print(json.dumps({
                "success": False,
                "error": f"Training error: {str(e)}"
            }))
    
    elif command == 'predict':
        try:
            periods = int(sys.argv[2]) if len(sys.argv) > 2 else 30
            frequency = sys.argv[3] if len(sys.argv) > 3 else 'D'
            
            result = model.predict(periods=periods, frequency=frequency)
            print(json.dumps(result))
            
        except Exception as e:
            print(json.dumps({
                "success": False,
                "error": f"Prediction error: {str(e)}"
            }))
    
    elif command == 'status':
        # Check if model is trained and available
        model_exists = model.load_model()
        print(json.dumps({
            "success": True,
            "model_trained": model_exists,
            "model_path": model.model_path
        }))
    
    else:
        print(json.dumps({
            "success": False,
            "error": f"Unknown command: {command}. Available commands: train, predict, status"
        }))

if __name__ == '__main__':
    main()