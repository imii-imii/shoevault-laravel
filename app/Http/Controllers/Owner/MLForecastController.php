<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\MLForecastService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MLForecastController extends Controller
{
    protected $forecastService;

    public function __construct(MLForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * Generate ML-based forecast with automatic fallback
     */
    public function forecast(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'string|in:sales,demand',
            'range' => 'string|in:day,weekly,monthly,quarterly,yearly',
            'periods' => 'integer|min:1|max:365',
            'sale_type' => 'string|in:all,pos,reservation'
        ]);

        $range = $request->get('range', 'weekly');
        $type = $request->get('type', 'sales');
        $saleType = $request->get('sale_type', 'all');
        $periods = $this->getPeriodsForRange($range, $request->get('periods'));

        try {
            // Try ML first, then fallback to statistical
            $forecastResult = $this->generateBestAvailableForecast($range, $type, $periods, $saleType);

            if (!$forecastResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'All forecast methods failed: ' . $forecastResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $forecastResult['data']['labels'],
                    'datasets' => $forecastResult['data']['datasets']
                ],
                'meta' => [
                    'type' => $type,
                    'range' => $range,
                    'periods' => $periods,
                    'sale_type' => $saleType,
                    'method' => $forecastResult['method'] ?? 'unknown',
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Forecast failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate forecast using best available method
     */
    private function generateBestAvailableForecast(string $range, string $type, int $periods, string $saleType = 'all'): array
    {
        // Method 1: Try ML if available
        if ($this->forecastService->isMLAvailable()) {
            try {
                $frequency = $this->getFrequencyForRange($range);
                $forecastResult = $this->forecastService->generateForecast($periods, $frequency);
                
                if ($forecastResult['success']) {
                    $chartData = $this->forecastService->formatForecastForChart(
                        $forecastResult['predictions'], 
                        $range
                    );
                    
                    return [
                        'success' => true,
                        'data' => [
                            'labels' => $chartData['labels'],
                            'datasets' => $chartData['datasets']
                        ],
                        'method' => 'machine_learning'
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('ML forecast failed, trying statistical: ' . $e->getMessage());
            }
        }

        // Method 2: Statistical forecast
        try {
            $simpleForecastService = new \App\Services\SimpleForecastService();
            $statisticalResult = $simpleForecastService->generateStatisticalForecast($range, $type, $periods, $saleType);
            
            if ($statisticalResult['success']) {
                return [
                    'success' => true,
                    'data' => $statisticalResult['data'],
                    'method' => 'statistical'
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Statistical forecast failed, using mock: ' . $e->getMessage());
        }

        // Method 3: Return error if all methods fail
        return [
            'success' => false,
            'error' => 'All forecast methods failed - unable to generate predictions',
            'method' => 'error'
        ];
    }



    /**
     * Train the ML model
     */
    public function trainModel(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:30|max:1095' // 30 days to 3 years
        ]);

        $days = $request->get('days', 365);

        try {
            $result = $this->forecastService->trainModel($days);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Model trained successfully',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Training failed: ' . $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Training failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get model status
     */
    public function modelStatus(): JsonResponse
    {
        try {
            $status = $this->forecastService->getModelStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export historical data for training
     */
    public function exportData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:30|max:1095'
        ]);

        $days = $request->get('days', 365);

        try {
            $result = $this->forecastService->exportHistoricalData($days);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appropriate number of periods for range
     */
    private function getPeriodsForRange(string $range, ?int $requestedPeriods = null): int
    {
        if ($requestedPeriods) {
            return min($requestedPeriods, 365); // Cap at 365
        }

        return match ($range) {
            'day' => 10,      // Business hours: 10 AM - 7 PM (10 hours)
            'weekly' => 7,    // 7 days
            'monthly' => 30,  // 30 days
            'quarterly' => 12, // 12 weeks
            'yearly' => 12,   // 12 months
            default => 30
        };
    }

    /**
     * Get appropriate frequency for range
     */
    private function getFrequencyForRange(string $range): string
    {
        return match ($range) {
            'day' => 'H',      // Hourly
            'weekly' => 'D',   // Daily
            'monthly' => 'D',  // Daily
            'quarterly' => 'W', // Weekly
            'yearly' => 'M',   // Monthly
            default => 'D'
        };
    }

    /**
     * Get date for specific period
     */
    private function getDateForPeriod(string $range, Carbon $startDate, int $index): Carbon
    {
        switch ($range) {
            case 'day':
                // Start from 10 AM and add hours for business hours
                return $startDate->copy()->setHour(10)->addHours($index);
            case 'weekly':
                return $startDate->copy()->addDays($index);
            case 'monthly':
                return $startDate->copy()->addDays($index);
            case 'quarterly':
                return $startDate->copy()->addWeeks($index);
            case 'yearly':
                return $startDate->copy()->addMonths($index);
            default:
                return $startDate->copy()->addDays($index);
        }
    }

    /**
     * Format label for chart
     */
    private function formatLabel(string $range, Carbon $date, int $index): string
    {
        switch ($range) {
            case 'day':
                return $date->format('g:i A'); // 12-hour format with AM/PM (e.g., "10:00 AM", "1:00 PM")
            case 'weekly':
                return $date->format('D M j');
            case 'monthly':
                return $date->format('M j');
            case 'quarterly':
                return 'Week ' . ($index + 1);
            case 'yearly':
                return $date->format('M Y');
            default:
                return $date->format('M j');
        }
    }
}