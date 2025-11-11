@echo off
REM ML Model Setup Script for ShoeVault Laravel (Windows)
REM This script sets up the Python environment and dependencies for ML forecasting

echo 🔧 Setting up ML Forecasting Environment...

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Python is not installed. Please install Python 3.8+ first.
    echo    Download from: https://www.python.org/downloads/
    pause
    exit /b 1
)

echo ✅ Using Python:
python --version

REM Create virtual environment if it doesn't exist
if not exist "ml_models\venv" (
    echo 📦 Creating virtual environment...
    python -m venv ml_models\venv
    
    if %errorlevel% neq 0 (
        echo ❌ Failed to create virtual environment. Make sure 'venv' module is available.
        pause
        exit /b 1
    )
)

REM Activate virtual environment
echo 🔄 Activating virtual environment...
call ml_models\venv\Scripts\activate.bat

REM Upgrade pip
echo ⬆️ Upgrading pip...
python -m pip install --upgrade pip

REM Install requirements
echo 📚 Installing Python dependencies...
pip install -r ml_models\requirements.txt

if %errorlevel% neq 0 (
    echo ❌ Failed to install requirements. Check the error messages above.
    pause
    exit /b 1
)

REM Test the ML model script
echo 🧪 Testing ML model script...
cd ml_models
python forecast_model.py status

if %errorlevel% equ 0 (
    echo ✅ ML forecasting environment setup complete!
    echo.
    echo 📋 Next steps:
    echo    1. Add PYTHON_PATH to your .env file:
    echo       PYTHON_PATH=python
    echo    2. Train the model using the Laravel API:
    echo       POST /owner/api/ml-forecast/train
    echo    3. Generate forecasts:
    echo       GET /owner/api/ml-forecast?range=weekly^&type=sales
    echo.
    echo 💡 Tip: The model will automatically train when first accessed if no trained model exists.
) else (
    echo ❌ Setup completed but model script test failed. Check the error messages above.
)

cd ..
pause