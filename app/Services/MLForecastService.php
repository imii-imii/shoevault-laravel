<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MLForecastService
{
    protected $pythonPath;
    protected $scriptPath;
    protected $modelPath;
    protected $simpleForecastService;

    public function __construct(SimpleForecastService $simpleForecastService = null)
    {
        $this->pythonPath = config('app.python_path', 'python');
        $this->scriptPath = base_path('ml_models/forecast_model.py');
        $this->modelPath = base_path('ml_models/sales_forecast_model.pkl');
        $this->simpleForecastService = $simpleForecastService ?? new SimpleForecastService();
    }

    /**
     * Export historical transaction data for model training
     */
    public function exportHistoricalData(int $days = 365, string $saleType = 'all')
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays($days);

            // Get daily aggregated sales data
            $salesQuery = DB::table('transactions')
                ->selectRaw('
                    DATE(created_at) as date,
                    SUM(total_amount) as sales,
                    COUNT(*) as transaction_count
                ')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('total_amount')
                ->where('total_amount', '>', 0);

            // Filter by sale_type if specified
            if ($saleType !== 'all') {
                $salesQuery->where('sale_type', $saleType);
            }

            $salesData = $salesQuery->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get daily reservation data
            $reservationData = DB::table('reservations')
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as reservations,
                    COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_reservations
                ')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Combine sales and reservation data
            $combinedData = [];
            foreach ($salesData as $sale) {
                $reservation = $reservationData->get($sale->date);
                
                $combinedData[] = [
                    'date' => $sale->date,
                    'sales' => (float) $sale->sales,
                    'reservations' => $reservation ? (float) $reservation->reservations : 0,
                    'transaction_count' => (int) $sale->transaction_count,
                    'completed_reservations' => $reservation ? (int) $reservation->completed_reservations : 0
                ];
            }

            // Fill missing dates with zero values
            $filledData = $this->fillMissingDates($combinedData, $startDate, $endDate);

            return [
                'success' => true,
                'data' => $filledData,
                'total_days' => count($filledData),
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to export historical data: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Train the ML model with historical data
     */
    public function trainModel(int $historicalDays = 365)
    {
        try {
            // Export historical data
            $dataResult = $this->exportHistoricalData($historicalDays);
            
            if (!$dataResult['success']) {
                return $dataResult;
            }

            $trainingData = json_encode($dataResult['data']);

            // Call Python script to train model
            $command = sprintf(
                '%s %s train',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath)
            );

            $descriptorspec = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w']  // stderr
            ];

            $process = proc_open($command, $descriptorspec, $pipes);

            if (!is_resource($process)) {
                throw new \Exception('Failed to start Python process');
            }

            // Send training data to stdin
            fwrite($pipes[0], $trainingData);
            fclose($pipes[0]);

            // Get output
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            if ($returnCode !== 0) {
                throw new \Exception('Python script failed: ' . $error);
            }

            $result = json_decode($output, true);
            
            if (!$result) {
                throw new \Exception('Invalid JSON response from Python script');
            }

            // Log training results
            if ($result['success']) {
                Log::info('ML model trained successfully', $result);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Model training failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate forecasts using the trained model
     */
    public function generateForecast(int $periods = 30, string $frequency = 'D')
    {
        // First try ML model
        if ($this->isMLAvailable()) {
            try {
                $command = sprintf(
                    '%s %s predict %d %s',
                    escapeshellcmd($this->pythonPath),
                    escapeshellarg($this->scriptPath),
                    $periods,
                    escapeshellarg($frequency)
                );

                $output = shell_exec($command);
                
                if (!$output) {
                    throw new \Exception('No output from Python script');
                }

                $result = json_decode($output, true);
                
                if (!$result) {
                    throw new \Exception('Invalid JSON response from Python script');
                }

                return $result;

            } catch (\Exception $e) {
                Log::warning('ML forecast failed, falling back to statistical: ' . $e->getMessage());
            }
        }

        // Fallback to statistical forecast
        return $this->generateStatisticalFallback($periods, $frequency);
    }

    /**
     * Check if ML environment is available
     */
    public function isMLAvailable(): bool
    {
        // Check if setup marker exists
        if (!file_exists(base_path('.ml_setup_complete'))) {
            return false;
        }

        // Check if Python script exists
        if (!file_exists($this->scriptPath)) {
            return false;
        }

        // Check if Python is available
        $output = shell_exec($this->pythonPath . ' --version 2>&1');
        if (!$output || strpos(strtolower($output), 'python') === false) {
            return false;
        }

        return true;
    }

    /**
     * Generate statistical forecast as fallback
     */
    private function generateStatisticalFallback(int $periods, string $frequency): array
    {
        // Convert frequency to range
        $range = match($frequency) {
            'H' => 'day',
            'D' => 'weekly',
            'W' => 'quarterly', 
            'M' => 'yearly',
            default => 'weekly'
        };

        $result = $this->simpleForecastService->generateStatisticalForecast($range, 'sales', $periods);
        
        if ($result['success']) {
            // Convert to ML format
            $predictions = [];
            $labels = $result['data']['labels'];
            $posData = $result['data']['datasets']['pos'];
            
            for ($i = 0; $i < count($labels); $i++) {
                $date = now()->addDays($i + 1);
                $predictions[] = [
                    'date' => $date->toDateString(),
                    'datetime' => $date->toDateTimeString(),
                    'forecast' => $posData[$i] ?? 0,
                    'lower_bound' => ($posData[$i] ?? 0) * 0.8,
                    'upper_bound' => ($posData[$i] ?? 0) * 1.2
                ];
            }
            
            return [
                'success' => true,
                'predictions' => $predictions,
                'periods' => $periods,
                'frequency' => $frequency,
                'method' => 'statistical_fallback'
            ];
        }

        return $result;
    }

    /**
     * Check if the model is trained and ready
     */
    public function getModelStatus()
    {
        try {
            $command = sprintf(
                '%s %s status',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath)
            );

            $output = shell_exec($command);
            
            if (!$output) {
                return [
                    'success' => false,
                    'error' => 'No response from Python script'
                ];
            }

            $result = json_decode($output, true);
            
            return $result ?: [
                'success' => false,
                'error' => 'Invalid response from Python script'
            ];

        } catch (\Exception $e) {
            Log::error('Model status check failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convert forecast data to chart format
     */
    public function formatForecastForChart(array $predictions, string $range = 'daily')
    {
        if (empty($predictions)) {
            return [
                'labels' => [],
                'datasets' => [
                    'pos' => [],
                    'reservations' => [],
                    'trend' => [],
                    'peaks' => []
                ]
            ];
        }

        $labels = [];
        $posData = [];
        $reservationData = [];
        $trendData = [];
        $peaks = [];

        foreach ($predictions as $index => $prediction) {
            $date = Carbon::parse($prediction['date']);
            
            // Format labels based on range
            switch ($range) {
                case 'day':
                    $labels[] = $date->format('g:i A'); // 12-hour format with AM/PM
                    break;
                case 'weekly':
                    $labels[] = $date->format('D M j');
                    break;
                case 'monthly':
                    $labels[] = $date->format('M j');
                    break;
                case 'quarterly':
                    $labels[] = 'Week ' . ($index + 1);
                    break;
                case 'yearly':
                    $labels[] = $date->format('M Y');
                    break;
                default:
                    $labels[] = $date->format('M j');
            }

            $forecast = $prediction['forecast'];
            $posData[] = round($forecast);
            
            // Estimate reservations as a percentage of sales
            $reservationData[] = round($forecast * 0.15); // 15% of sales as reservations
            
            // Trend line (upper bound)
            $trendData[] = round($prediction['upper_bound']);
            
            // Identify peaks (values significantly above average)
            $average = array_sum($posData) / count($posData);
            if ($forecast > $average * 1.3) {
                $peaks[] = ['x' => $index, 'y' => round($forecast)];
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                'pos' => $posData,
                'reservations' => $reservationData,  
                'trend' => $trendData,
                'peaks' => $peaks
            ]
        ];
    }

    /**
     * Fill missing dates in the dataset with zero values
     */
    private function fillMissingDates(array $data, Carbon $startDate, Carbon $endDate)
    {
        $dataByDate = collect($data)->keyBy('date');
        $filledData = [];
        
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();
            
            if ($dataByDate->has($dateString)) {
                $filledData[] = $dataByDate->get($dateString);
            } else {
                $filledData[] = [
                    'date' => $dateString,
                    'sales' => 0.0,
                    'reservations' => 0.0,
                    'transaction_count' => 0,
                    'completed_reservations' => 0
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $filledData;
    }
}