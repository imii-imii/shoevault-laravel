#!/bin/bash

# ML Model Setup Script for ShoeVault Laravel
# This script sets up the Python environment and dependencies for ML forecasting

echo "🔧 Setting up ML Forecasting Environment..."

# Check if Python is installed
if ! command -v python &> /dev/null && ! command -v python3 &> /dev/null; then
    echo "❌ Python is not installed. Please install Python 3.8+ first."
    echo "   Download from: https://www.python.org/downloads/"
    exit 1
fi

# Use python3 if available, otherwise python
PYTHON_CMD="python"
if command -v python3 &> /dev/null; then
    PYTHON_CMD="python3"
fi

echo "✅ Using Python: $($PYTHON_CMD --version)"

# Create virtual environment if it doesn't exist
if [ ! -d "ml_models/venv" ]; then
    echo "📦 Creating virtual environment..."
    $PYTHON_CMD -m venv ml_models/venv
    
    if [ $? -ne 0 ]; then
        echo "❌ Failed to create virtual environment. Make sure 'venv' module is available."
        echo "   You might need to install it: sudo apt install python3-venv (Ubuntu/Debian)"
        exit 1
    fi
fi

# Activate virtual environment
echo "🔄 Activating virtual environment..."
if [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
    # Windows
    source ml_models/venv/Scripts/activate
else
    # Linux/Mac
    source ml_models/venv/bin/activate
fi

# Upgrade pip
echo "⬆️ Upgrading pip..."
pip install --upgrade pip

# Install requirements
echo "📚 Installing Python dependencies..."
pip install -r ml_models/requirements.txt

if [ $? -ne 0 ]; then
    echo "❌ Failed to install requirements. Check the error messages above."
    exit 1
fi

# Test the ML model script
echo "🧪 Testing ML model script..."
cd ml_models
python forecast_model.py status

if [ $? -eq 0 ]; then
    echo "✅ ML forecasting environment setup complete!"
    echo ""
    echo "📋 Next steps:"
    echo "   1. Add PYTHON_PATH to your .env file:"
    echo "      PYTHON_PATH=$(which python)"
    echo "   2. Train the model using the Laravel API:"
    echo "      POST /owner/api/ml-forecast/train"
    echo "   3. Generate forecasts:"
    echo "      GET /owner/api/ml-forecast?range=weekly&type=sales"
    echo ""
    echo "💡 Tip: The model will automatically train when first accessed if no trained model exists."
else
    echo "❌ Setup completed but model script test failed. Check the error messages above."
    exit 1
fi