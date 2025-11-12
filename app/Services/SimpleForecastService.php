<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimpleForecastService
{
    /**
     * Simple forecasting service that doesn't require Python/ML dependencies
     * Uses statistical methods and historical patterns for predictions
     * Fallback when ML models are not available (like on shared hosting)
     */

    /**
     * Generate forecast using simple statistical methods
     */
    public function generateStatisticalForecast(string $range = 'weekly', string $type = 'sales', int $periods = null, string $saleType = 'all')
    {
        try {
            // Day view doesn't support predictions - return empty structure
            if ($range === 'day') {
                return [
                    'labels' => [],
                    'datasets' => [
                        'pos' => [],
                        'reservation' => [],
                        'trend' => [],
                        'peaks' => []
                    ]
                ];
            }

            // Get appropriate historical data period based on forecast range
            $historicalDays = $this->getHistoricalDaysForRange($range);
            // Always get ALL historical data for proper base metrics calculation
            $historicalData = $this->getHistoricalData($historicalDays, 'all');
            
            Log::info("Forecast Data Check", [
                'type' => $type,
                'range' => $range,
                'sale_type' => $saleType,
                'historical_days' => $historicalDays,
                'historical_records_found' => count($historicalData),
                'using_real_data' => !empty($historicalData)
            ]);
            
            if (empty($historicalData)) {
                Log::warning("No historical data found - forecast will fail", [
                    'type' => $type,
                    'sale_type' => $saleType,
                    'historical_days' => $historicalDays
                ]);
                return [
                    'success' => false,
                    'error' => 'No historical data available for forecast generation' . ($saleType !== 'all' ? " for sale type: {$saleType}" : ''),
                    'method' => 'error'
                ];
            }

            // Calculate base metrics
            $metrics = $this->calculateHistoricalMetrics($historicalData);
            
            // Generate forecast based on statistical patterns
            $forecast = $this->generateStatisticalPredictions($range, $type, $periods, $metrics, $saleType);
            
            $response = [
                'success' => true,
                'data' => [
                    'labels' => $forecast['labels'],
                    'datasets' => $forecast['datasets']
                ],
                'method' => 'statistical',
                'meta' => [
                    'base_daily_avg' => $metrics['daily_avg'],
                    'growth_rate' => $metrics['growth_rate'],
                    'seasonal_factor' => $metrics['seasonal_factor'],
                    'sale_type' => $saleType,
                    'historical_records' => count($historicalData),
                    'using_real_data' => true
                ]
            ];

            // Add demand-specific metadata if available
            if (isset($forecast['meta'])) {
                $response['meta'] = array_merge($response['meta'], $forecast['meta']);
            }

            Log::info("Forecast generated successfully", [
                'type' => $type,
                'method' => 'statistical',
                'using_real_data' => $response['meta']['using_real_data'] ?? true,
                'data_source' => $response['meta']['data_source'] ?? 'historical_transactions'
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Statistical forecast failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Unable to load forecast data: ' . $e->getMessage(),
                'method' => 'error'
            ];
        }
    }

    /**
     * Get historical transaction data
     */
    private function getHistoricalData(int $days = 90, string $saleType = 'all'): array
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays($days);

            $query = DB::table('transactions')
                ->selectRaw('
                    DATE(created_at) as date,
                    SUM(total_amount) as sales,
                    COUNT(*) as transaction_count,
                    DAYOFWEEK(created_at) as day_of_week
                ')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('total_amount')
                ->where('total_amount', '>', 0);

            // Filter by sale_type if specified
            if ($saleType !== 'all') {
                $query->where('sale_type', $saleType);
            }

            $data = $query->groupBy('date', 'day_of_week')
                ->orderBy('date')
                ->get()
                ->toArray();

            return array_map(function($item) {
                return (array) $item;
            }, $data);

        } catch (\Exception $e) {
            Log::error('Failed to get historical data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate statistical metrics from historical data
     */
    private function calculateHistoricalMetrics(array $data): array
    {
        if (empty($data)) {
            // No fallback data - let it fail to see what's wrong
            throw new \Exception('No historical data found for forecast calculation');
        }

        // Calculate daily average
        $totalSales = array_sum(array_column($data, 'sales'));
        $dailyAvg = $totalSales / count($data);

        // Calculate growth rate (simple linear trend)
        $growthRate = $this->calculateGrowthRate($data);

        // Calculate weekday patterns
        $weekdayPatterns = $this->calculateWeekdayPatterns($data);

        // Calculate POS vs Reservation ratios from historical data
        $saleTypeRatios = $this->calculateSaleTypeRatios();

        // Simple seasonal factor (could be enhanced with more data)
        $seasonalFactor = 1.0;

        return [
            'daily_avg' => $dailyAvg,
            'growth_rate' => $growthRate,
            'seasonal_factor' => $seasonalFactor,
            'weekday_patterns' => $weekdayPatterns,
            'sale_type_ratios' => $saleTypeRatios
        ];
    }

    /**
     * Calculate growth rate from historical data
     */
    private function calculateGrowthRate(array $data): float
    {
        if (count($data) < 7) {
            return 0.02; // Default 2% growth
        }

        // Compare first week with last week
        $firstWeek = array_slice($data, 0, 7);
        $lastWeek = array_slice($data, -7);

        $firstWeekAvg = array_sum(array_column($firstWeek, 'sales')) / 7;
        $lastWeekAvg = array_sum(array_column($lastWeek, 'sales')) / 7;

        if ($firstWeekAvg == 0) {
            return 0.02;
        }

        // Calculate daily growth rate
        $growthRate = ($lastWeekAvg - $firstWeekAvg) / $firstWeekAvg / count($data) * 7;

        // Cap growth rate between -5% and +20% per period (more optimistic for yearly forecasts)
        return max(-0.05, min(0.20, $growthRate));
    }

    /**
     * Calculate weekday patterns from historical data
     */
    private function calculateWeekdayPatterns(array $data): array
    {
        $weekdayTotals = array_fill(0, 7, 0);
        $weekdayCounts = array_fill(0, 7, 0);

        foreach ($data as $record) {
            $dayOfWeek = (int)$record['day_of_week'] - 1; // Convert to 0-6 (Monday-Sunday)
            if ($dayOfWeek == -1) $dayOfWeek = 6; // Sunday

            $weekdayTotals[$dayOfWeek] += (float)$record['sales'];
            $weekdayCounts[$dayOfWeek]++;
        }

        // Calculate averages
        $weekdayAverages = [];
        for ($i = 0; $i < 7; $i++) {
            $weekdayAverages[$i] = $weekdayCounts[$i] > 0 ? 
                $weekdayTotals[$i] / $weekdayCounts[$i] : 0;
        }

        // Calculate overall average
        $overallAvg = array_sum($weekdayAverages) / 7;

        // Calculate patterns as multipliers
        $patterns = [];
        for ($i = 0; $i < 7; $i++) {
            $patterns[$i] = $overallAvg > 0 ? $weekdayAverages[$i] / $overallAvg : 1.0;
            $patterns[$i] = max(0.5, min(2.0, $patterns[$i])); // Cap between 0.5x and 2.0x
        }

        return $patterns;
    }

    /**
     * Calculate actual POS vs Reservation ratios from historical data
     */
    private function calculateSaleTypeRatios(): array
    {
        try {
            $endDate = Carbon::now();
            // Use a full year of data for more accurate ratios, especially for yearly predictions
            $startDate = $endDate->copy()->subDays(365);

            // Get total sales by sale type
            $salesByType = DB::table('transactions')
                ->selectRaw('
                    CASE 
                        WHEN sale_type = "pos" OR reservation_id IS NULL THEN "pos"
                        WHEN sale_type = "reservation" OR reservation_id IS NOT NULL THEN "reservation"
                        ELSE "pos"
                    END as type,
                    SUM(total_amount) as total_sales,
                    COUNT(*) as transaction_count
                ')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('total_amount')
                ->where('total_amount', '>', 0)
                ->groupBy('type')
                ->get()
                ->keyBy('type')
                ->toArray();

            $posSales = isset($salesByType['pos']) ? (float)$salesByType['pos']->total_sales : 0;
            $reservationSales = isset($salesByType['reservation']) ? (float)$salesByType['reservation']->total_sales : 0;
            $totalSales = $posSales + $reservationSales;

            if ($totalSales == 0) {
                // Fallback ratios if no data
                return ['pos' => 0.85, 'reservation' => 0.15];
            }

            $posRatio = $posSales / $totalSales;
            $reservationRatio = $reservationSales / $totalSales;

            Log::info("Historical sale type ratios calculated (365 days)", [
                'pos_sales' => $posSales,
                'reservation_sales' => $reservationSales,
                'pos_ratio' => $posRatio,
                'reservation_ratio' => $reservationRatio,
                'days_analyzed' => 365
            ]);

            return [
                'pos' => $posRatio,
                'reservation' => $reservationRatio
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate sale type ratios: ' . $e->getMessage());
            // Fallback ratios
            return ['pos' => 0.85, 'reservation' => 0.15];
        }
    }

    /**
     * Generate statistical predictions
     */
    private function generateStatisticalPredictions(string $range, string $type, ?int $periods, array $metrics, string $saleType = 'all'): array
    {
        $periods = $periods ?? $this->getDefaultPeriods($range);
        
        // Ensure quarterly mode never exceeds 3 periods (3 months)
        if ($range === 'quarterly' && $periods > 3) {
            Log::info("Quarterly forecast periods capped to 3 months", ['original_periods' => $periods]);
            $periods = 3;
        }
        
        // Handle demand forecasting differently than sales
        if ($type === 'demand') {
            $historicalDays = $this->getHistoricalDaysForRange($range);
            return $this->generateDemandPredictions($periods, $metrics, $historicalDays);
        }
        
        $labels = [];
        $posData = [];
        $reservationData = [];
        $trendData = [];
        $peaks = [];

        $dailyAvg = $metrics['daily_avg'];
        $growthRate = $metrics['growth_rate'];
        $seasonalFactor = $metrics['seasonal_factor'];
        $weekdayPatterns = $metrics['weekday_patterns'];
        $saleTypeRatios = $metrics['sale_type_ratios'];

        // Adjust base value according to time range aggregation level
        $baseValue = $this->getBaseValueForRange($range, $dailyAvg);

        $startDate = Carbon::now()->addDay(); // Start from tomorrow

        for ($i = 0; $i < $periods; $i++) {
            // Calculate date for this period
            $currentDate = $this->getDateForPeriod($range, $startDate, $i);
            
            // Generate label
            $labels[] = $this->formatLabel($range, $currentDate, $i);

            // Calculate base prediction
            $prediction = $baseValue;

            // Apply growth - ensure growth factor doesn't go below 0.5 (50% of base)
            // For yearly forecasts, allow more optimistic growth compounding
            $growthFactor = max(0.5, (1 + ($growthRate * $i)));
            $prediction *= $growthFactor;

            // Apply seasonal factor
            $prediction *= $seasonalFactor;

            // Apply patterns based on time range  
            if (in_array($range, ['weekly', 'monthly'])) {
                // Apply weekday pattern for weekly/monthly ranges
                $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
                $patternIndex = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1; // Convert to our array index
                $prediction *= $weekdayPatterns[$patternIndex] ?? 1.0;
            }

            // Add some realistic randomness
            $randomFactor = 0.85 + (mt_rand() / mt_getrandmax()) * 0.3; // 0.85 to 1.15
            $prediction *= $randomFactor;

            // Ensure non-negative
            $prediction = max(0, $prediction);

            // Apply sale type filtering for specific requests
            if ($saleType === 'pos') {
                // For POS-only requests, apply POS ratio to the total prediction
                $posData[] = round($prediction * $saleTypeRatios['pos']);
                $reservationData[] = 0; // No reservation data
            } else if ($saleType === 'reservation') {
                // For Reservation-only requests, apply reservation ratio to the total prediction
                $posData[] = 0; // No POS data
                $reservationData[] = round($prediction * $saleTypeRatios['reservation']);
            } else {
                // For 'all' requests, split the prediction using historical ratios
                $posData[] = round($prediction * $saleTypeRatios['pos']);
                $reservationData[] = round($prediction * $saleTypeRatios['reservation']);
            }
            $trendData[] = round($prediction * 1.1); // Trend line slightly higher

            // Identify peaks
            if ($i > 0 && $prediction > $baseValue * 1.3) {
                $peaks[] = ['x' => $i, 'y' => round($prediction)];
            }
        }

        // Adjust return data based on sale type request
        if ($saleType === 'pos') {
            return [
                'labels' => $labels,
                'datasets' => [
                    'pos' => $posData,
                    'reservation' => $reservationData, // Keep structure consistent
                    'trend' => $trendData,
                    'peaks' => $peaks
                ]
            ];
        } else if ($saleType === 'reservation') {
            return [
                'labels' => $labels,
                'datasets' => [
                    'pos' => $reservationData, // Put reservation data in 'pos' field for frontend compatibility
                    'reservation' => $reservationData, // Also in reservation field for consistency
                    'trend' => $trendData,
                    'peaks' => $peaks
                ]
            ];
        } else {
            // For 'all' requests, return both datasets normally
            return [
                'labels' => $labels,
                'datasets' => [
                    'pos' => $posData,
                    'reservation' => $reservationData,
                    'trend' => $trendData,
                    'peaks' => $peaks
                ]
            ];
        }
    }

    /**
     * Get appropriate base value for different time ranges
     */
    private function getBaseValueForRange(string $range, float $dailyAvg): float
    {
        switch ($range) {
            case 'day':
                // Day view doesn't support predictions
                return 0;
            case 'weekly':
                // Daily data for weekly view - use daily average as is
                return $dailyAvg;
            case 'monthly':
                // Daily data for monthly view - use daily average (same as weekly)
                return $dailyAvg;
            case 'quarterly':
                // Monthly data for quarterly view - use actual historical monthly average
                return $this->getHistoricalMonthlyAverage();
            case 'yearly':
                // Monthly data for yearly view - use actual historical monthly average
                return $this->getHistoricalMonthlyAverage();
            default:
                return $dailyAvg;
        }
    }



    /**
     * Get actual historical monthly average for more accurate yearly/quarterly predictions
     */
    private function getHistoricalMonthlyAverage(): float
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(12);

            $monthlyAvg = DB::table('transactions')
                ->selectRaw('AVG(monthly_total) as avg_monthly')
                ->fromSub(function ($query) use ($startDate, $endDate) {
                    $query->from('transactions')
                        ->selectRaw('
                            YEAR(created_at) as year,
                            MONTH(created_at) as month,
                            SUM(total_amount) as monthly_total
                        ')
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotNull('total_amount')
                        ->where('total_amount', '>', 0)
                        ->groupBy('year', 'month');
                }, 'monthly_totals')
                ->value('avg_monthly');

            return $monthlyAvg ?? 500000; // Fallback if no data
        } catch (\Exception $e) {
            Log::error("Error calculating historical monthly average", ['error' => $e->getMessage()]);
            return 500000; // Fallback value
        }
    }

    /**
     * Get date for a specific period index
     */
    private function getDateForPeriod(string $range, Carbon $startDate, int $index): Carbon
    {
        switch ($range) {
            case 'day':
                return $startDate->copy()->setHour(10)->addHours($index);
            case 'weekly':
                return $startDate->copy()->addDays($index);
            case 'monthly':
                return $startDate->copy()->addDays($index);
            case 'quarterly':
                return $startDate->copy()->addMonths($index);
            case 'yearly':
                return $startDate->copy()->addMonths($index);
            default:
                return $startDate->copy()->addDays($index);
        }
    }

    /**
     * Format label for display
     */
    private function formatLabel(string $range, Carbon $date, int $index): string
    {
        switch ($range) {
            case 'day':
                return $date->format('g A'); // 12-hour format without minutes (e.g., "10 AM", "1 PM")
            case 'weekly':
                return $date->format('D M j');
            case 'monthly':
                return $date->format('M j');
            case 'quarterly':
                return $date->format('M Y'); // Show month and year (e.g., "Dec 2025", "Jan 2026")
            case 'yearly':
                return $date->format('M Y');
            default:
                return $date->format('M j');
        }
    }

    /**
     * Get default number of periods for range
     */
    private function getDefaultPeriods(string $range): int
    {
        return match ($range) {
            'day' => 10,        // Business hours: 10 AM - 7 PM
            'weekly' => 7,      // 7 days
            'monthly' => 30,    // 30 days
            'quarterly' => 3,   // 3 months
            'yearly' => 12,     // 12 months
            default => 30
        };
    }

    /**
     * Get appropriate historical data period based on forecast range
     */
    private function getHistoricalDaysForRange(string $range): int
    {
        return match ($range) {
            'day' => 30,        // 30 days for hourly predictions (1 month of hourly patterns)
            'weekly' => 90,     // 90 days for weekly predictions (3 months of daily patterns)
            'monthly' => 365,   // 1 year for monthly predictions (12 months of seasonal patterns)
            'quarterly' => 730, // 2 years for quarterly predictions (8 quarters of business cycles)
            'yearly' => 1095,   // 3 years for yearly predictions (long-term trends)
            default => 90
        };
    }

    /**
     * Generate demand predictions (quantities by brand)
     */
    private function generateDemandPredictions(?int $periods, array $metrics, int $historicalDays = 90): array
    {
        // Get brand data from historical transactions
        $brandData = $this->getHistoricalBrandData($historicalDays);
        
        $usingRealData = !empty($brandData);
        
        if (empty($brandData)) {
            Log::warning("Demand prediction using FALLBACK data - no real brand data found", [
                'historical_days' => $historicalDays,
                'fallback_brands' => ['Demand Mode', 'Works Fine']
            ]);
            // Simple fallback for demand predictions only
            $brandData = [
                'Demand Mode' => 100,
                'Works Fine' => 80
            ];
        } else {
            Log::info("Demand prediction using REAL brand data", [
                'historical_days' => $historicalDays,
                'brands_found' => array_keys($brandData),
                'total_quantities' => array_sum($brandData)
            ]);
        }
        
        // Apply growth factor to brand quantities
        $growthFactor = 1 + ($metrics['growth_rate'] * 30); // Project 30 days ahead
        $growthFactor = max(0.8, min(1.3, $growthFactor)); // Cap between 0.8x and 1.3x
        
        $brands = [];
        $quantities = [];
        
        foreach ($brandData as $brand => $baseQuantity) {
            // Apply growth and some randomness
            $randomFactor = 0.9 + (mt_rand() / mt_getrandmax()) * 0.2; // 0.9 to 1.1
            $predictedQuantity = round($baseQuantity * $growthFactor * $randomFactor);
            
            $brands[] = $brand;
            $quantities[] = max(1, $predictedQuantity); // Ensure at least 1
        }
        
        return [
            'labels' => [], // Not used for demand mode
            'datasets' => [
                'brands' => $brands,
                'quantities' => $quantities
            ],
            'meta' => [
                'using_real_data' => $usingRealData,
                'data_source' => $usingRealData ? 'historical_transactions' : 'fallback_mock_data',
                'historical_days' => $historicalDays
            ]
        ];
    }

    /**
     * Get historical brand demand data
     */
    private function getHistoricalBrandData(int $days = 90): array
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays($days);

            Log::info("Querying historical brand data", [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $days
            ]);

            $brandData = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->selectRaw("COALESCE(ti.product_brand, 'Unknown') as brand, SUM(ti.quantity) as total_qty")
                ->whereBetween('t.created_at', [$startDate, $endDate])
                ->whereNotNull('ti.quantity')
                ->where('ti.quantity', '>', 0)
                ->groupBy('ti.product_brand')
                ->orderBy('total_qty', 'desc')
                ->limit(8) // Top 8 brands
                ->pluck('total_qty', 'brand')
                ->toArray();

            Log::info("Historical brand data query result", [
                'brands_found' => count($brandData),
                'brand_details' => $brandData
            ]);

            return $brandData;

        } catch (\Exception $e) {
            Log::error('Failed to get historical brand data: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}