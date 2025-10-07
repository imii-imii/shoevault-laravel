<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoe Conversion - Shoe Store</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .conversion-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .conversion-header {
            background: linear-gradient(to top right, #112c70, #2a6aff);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .conversion-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .conversion-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .conversion-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .converter-section {
            background: #f8f9fa;
            padding: 40px;
            border-right: 2px solid #e9ecef;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #667eea;
        }

        .converter-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .conversion-result {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-top: 20px;
            display: none;
        }

        .result-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .result-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .result-details {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .chart-section {
            padding: 40px;
        }

        .sizing-chart {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-height: 600px;
            overflow-y: auto;
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .chart-table th,
        .chart-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }

        .chart-table th {
            background: #667eea;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .chart-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .chart-table tr:hover {
            background: #e3f2fd;
        }

        .conversion-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            padding: 30px 40px;
            background: #f8f9fa;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            min-width: 150px;
            justify-content: center;
        }

        .btn-back {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-convert {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-convert:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .conversion-content {
                grid-template-columns: 1fr;
            }

            .converter-section {
                border-right: none;
                border-bottom: 2px solid #e9ecef;
            }

            .conversion-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .conversion-header h1 {
                font-size: 2rem;
            }
        }

        /* Tighter phone layout */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .conversion-container {
                border-radius: 16px;
            }
            .conversion-header {
                padding: 22px 18px;
            }
            .conversion-header h1 {
                font-size: 1.65rem;
            }
            .conversion-header p {
                font-size: 0.9rem;
            }
            .converter-section,
            .chart-section {
                padding: 26px 20px;
            }
            .converter-form {
                padding: 20px 18px;
            }
            .section-title {
                font-size: 1.25rem;
                gap: 6px;
            }
            .form-group label {
                font-size: 0.85rem;
            }
            .form-group select,
            .form-group input {
                padding: 12px 14px;
                font-size: 0.9rem;
            }
            .result-value {
                font-size: 2rem;
            }
            .conversion-actions {
                padding: 20px 20px 26px;
                gap: 10px;
            }
            .btn {
                padding: 14px 20px;
                min-width: 0;
                font-size: 0.9rem;
            }
            .sizing-chart {
                padding: 20px 18px;
                max-height: 420px;
            }
            .chart-table th,
            .chart-table td {
                padding: 8px 6px;
                font-size: 0.75rem;
            }
        }

        /* Custom scrollbar */
        .sizing-chart::-webkit-scrollbar {
            width: 6px;
        }

        .sizing-chart::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .sizing-chart::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 3px;
        }

        .sizing-chart::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }
    </style>
</head>
<body>
    <div class="conversion-container">
        <!-- Header -->
        <div class="conversion-header">
            <h1><i class="bx bx-ruler"></i> Shoe Size Converter</h1>
            <p>Convert between EU and US shoe sizes with our comprehensive sizing chart</p>
        </div>

        <!-- Content -->
        <div class="conversion-content">
            <!-- Converter Section -->
            <div class="converter-section">
                <h2 class="section-title">
                    <i class="bx bx-calculator"></i>
                    Size Converter
                </h2>
                
                <div class="converter-form">
                    <div class="form-group">
                        <label for="conversion-type">Conversion Type</label>
                        <select id="conversion-type">
                            <option value="eu-to-us">EU to US</option>
                            <option value="us-to-eu">US to EU</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="input-size">Enter Size</label>
                        <input type="number" id="input-size" placeholder="Enter your shoe size" step="0.5" min="0">
                    </div>

                    <button class="btn btn-convert" id="convert-btn">
                        <i class="bx bx-transfer"></i>
                        Convert Size
                    </button>

                    <div class="conversion-result" id="conversion-result">
                        <div class="result-label">Converted Size</div>
                        <div class="result-value" id="result-value">US 8.5</div>
                        <div class="result-details" id="result-details">EU 42 → US 8.5</div>
                    </div>
                </div>
            </div>

            <!-- Sizing Chart Section -->
            <div class="chart-section">
                <h2 class="section-title">
                    <i class="bx bx-table"></i>
                    Sizing Chart
                </h2>
                
                <div class="sizing-chart">
                    <table class="chart-table">
                        <thead>
                            <tr>
                                <th>EU Size</th>
                                <th>US Size (Men)</th>
                                <th>US Size (Women)</th>
                                <th>UK Size</th>
                                <th>CM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>35</td><td>3.5</td><td>5</td><td>2.5</td><td>22.5</td></tr>
                            <tr><td>35.5</td><td>4</td><td>5.5</td><td>3</td><td>23</td></tr>
                            <tr><td>36</td><td>4.5</td><td>6</td><td>3.5</td><td>23.5</td></tr>
                            <tr><td>36.5</td><td>5</td><td>6.5</td><td>4</td><td>24</td></tr>
                            <tr><td>37</td><td>5.5</td><td>7</td><td>4.5</td><td>24.5</td></tr>
                            <tr><td>37.5</td><td>6</td><td>7.5</td><td>5</td><td>25</td></tr>
                            <tr><td>38</td><td>6.5</td><td>8</td><td>5.5</td><td>25.5</td></tr>
                            <tr><td>38.5</td><td>7</td><td>8.5</td><td>6</td><td>26</td></tr>
                            <tr><td>39</td><td>7.5</td><td>9</td><td>6.5</td><td>26.5</td></tr>
                            <tr><td>39.5</td><td>8</td><td>9.5</td><td>7</td><td>27</td></tr>
                            <tr><td>40</td><td>8.5</td><td>10</td><td>7.5</td><td>27.5</td></tr>
                            <tr><td>40.5</td><td>9</td><td>10.5</td><td>8</td><td>28</td></tr>
                            <tr><td>41</td><td>9.5</td><td>11</td><td>8.5</td><td>28.5</td></tr>
                            <tr><td>41.5</td><td>10</td><td>11.5</td><td>9</td><td>29</td></tr>
                            <tr><td>42</td><td>10.5</td><td>12</td><td>9.5</td><td>29.5</td></tr>
                            <tr><td>42.5</td><td>11</td><td>12.5</td><td>10</td><td>30</td></tr>
                            <tr><td>43</td><td>11.5</td><td>13</td><td>10.5</td><td>30.5</td></tr>
                            <tr><td>43.5</td><td>12</td><td>13.5</td><td>11</td><td>31</td></tr>
                            <tr><td>44</td><td>12.5</td><td>14</td><td>11.5</td><td>31.5</td></tr>
                            <tr><td>44.5</td><td>13</td><td>14.5</td><td>12</td><td>32</td></tr>
                            <tr><td>45</td><td>13.5</td><td>15</td><td>12.5</td><td>32.5</td></tr>
                            <tr><td>45.5</td><td>14</td><td>15.5</td><td>13</td><td>33</td></tr>
                            <tr><td>46</td><td>14.5</td><td>16</td><td>13.5</td><td>33.5</td></tr>
                            <tr><td>46.5</td><td>15</td><td>16.5</td><td>14</td><td>34</td></tr>
                            <tr><td>47</td><td>15.5</td><td>17</td><td>14.5</td><td>34.5</td></tr>
                            <tr><td>47.5</td><td>16</td><td>17.5</td><td>15</td><td>35</td></tr>
                            <tr><td>48</td><td>16.5</td><td>18</td><td>15.5</td><td>35.5</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="conversion-actions">
            <button class="btn btn-back" id="back-btn">
                <i class="bx bx-arrow-back"></i>
                Back to Reservation
            </button>
        </div>
    </div>

    <script>
        // Sizing conversion data
        const sizingData = {
            'eu-to-us': {
                35: 3.5, 35.5: 4, 36: 4.5, 36.5: 5, 37: 5.5, 37.5: 6, 38: 6.5, 38.5: 7, 39: 7.5, 39.5: 8,
                40: 8.5, 40.5: 9, 41: 9.5, 41.5: 10, 42: 10.5, 42.5: 11, 43: 11.5, 43.5: 12, 44: 12.5, 44.5: 13,
                45: 13.5, 45.5: 14, 46: 14.5, 46.5: 15, 47: 15.5, 47.5: 16, 48: 16.5
            },
            'us-to-eu': {
                3.5: 35, 4: 35.5, 4.5: 36, 5: 36.5, 5.5: 37, 6: 37.5, 6.5: 38, 7: 38.5, 7.5: 39, 8: 39.5,
                8.5: 40, 9: 40.5, 9.5: 41, 10: 41.5, 10.5: 42, 11: 42.5, 11.5: 43, 12: 43.5, 12.5: 44, 13: 44.5,
                13.5: 45, 14: 45.5, 14.5: 46, 15: 46.5, 15.5: 47, 16: 47.5, 16.5: 48
            }
        };

        // DOM Elements
        const conversionType = document.getElementById('conversion-type');
        const inputSize = document.getElementById('input-size');
        const convertBtn = document.getElementById('convert-btn');
        const conversionResult = document.getElementById('conversion-result');
        const resultValue = document.getElementById('result-value');
        const resultDetails = document.getElementById('result-details');
        const backBtn = document.getElementById('back-btn');

        // Initialize the page
        function initializeConverter() {
            setupEventListeners();
        }

        // Setup event listeners
        function setupEventListeners() {
            convertBtn.addEventListener('click', performConversion);
            inputSize.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performConversion();
                }
            });
            backBtn.addEventListener('click', goBackToReservation);
        }

        // Perform size conversion
        function performConversion() {
            const type = conversionType.value;
            const input = parseFloat(inputSize.value);

            if (!input || isNaN(input)) {
                alert('Please enter a valid size number');
                return;
            }

            const conversionMap = sizingData[type];
            let convertedSize = null;

            // Find exact match
            if (conversionMap[input]) {
                convertedSize = conversionMap[input];
            } else {
                // Find closest match
                const sizes = Object.keys(conversionMap).map(Number).sort((a, b) => a - b);
                let closest = sizes[0];
                let minDiff = Math.abs(input - closest);

                for (let size of sizes) {
                    const diff = Math.abs(input - size);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closest = size;
                    }
                }
                convertedSize = conversionMap[closest];
            }

            // Display result
            if (type === 'eu-to-us') {
                resultValue.textContent = `US ${convertedSize}`;
                resultDetails.textContent = `EU ${input} → US ${convertedSize}`;
            } else {
                resultValue.textContent = `EU ${convertedSize}`;
                resultDetails.textContent = `US ${input} → EU ${convertedSize}`;
            }

            conversionResult.style.display = 'block';
        }

        // Go back to reservation system
        function goBackToReservation() {
            // Redirect back to the reservation portal page
            window.location.href = "{{ route('reservation.portal') }}";
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', initializeConverter);
    </script>
</body>
</html>