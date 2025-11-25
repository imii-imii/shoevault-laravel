<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Transaction;

class ForecastController extends Controller
{


    /**
     * Unified forecast endpoint
     * Query params:
     * - type: 'sales' | 'demand'
     * - range: 'day' | 'weekly' | 'monthly'
     * - anchor_date: 'YYYY-MM-DD' (used to determine the day/week/month window)
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'sales');
        $range = $request->query('range', 'day');
        $anchor = $request->query('anchor_date');
        $brand = $request->query('brand', 'all');
        $predictive = $request->query('predictive', 'false') === 'true';
        
        try {
            $anchorDate = $anchor ? Carbon::parse($anchor) : Carbon::today();
        } catch (\Exception $e) {
            $anchorDate = Carbon::today();
        }

        [$start, $end, $labels, $buckets] = $this->makeWindow($range, $anchorDate, $brand);

        if ($type === 'demand') {
            $data = $this->buildDemandData($start, $end, $range, $buckets, $brand, $predictive);
        } else { // default to sales
            $data = $this->buildSalesData($start, $end, $range, $buckets, $brand, $predictive);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => $data,
                'range' => $range,
            ],
        ]);
    }

    /**
     * Build time window and labels.
     * Returns [start, end, labels[], buckets[]]
     */
    private function makeWindow(string $range, Carbon $anchor, string $brand = 'all'): array
    {
        $labels = [];
        $buckets = [];
        if ($range === 'weekly') {
            // Week starts on Sunday to match UI
            $start = (clone $anchor)->startOfWeek(Carbon::SUNDAY);
            $end = (clone $start)->endOfWeek(Carbon::SATURDAY);
            
            // Generate actual month/day labels instead of Sun-Sat
            $labels = [];
            $current = clone $start;
            for ($i = 0; $i < 7; $i++) {
                // Format as "Mon 12/15" (DayName Month/Day)
                $labels[] = $current->format('M j'); // e.g., "Dec 15"
                $current->addDay();
            }
            
            // Buckets 0..6 map to Sun..Sat
            $buckets = range(0, 6);
            return [$start, $end, $labels, $buckets];
        }
        if ($range === 'quarterly') {
            // Quarter: 3 months starting at quarter boundary; show month names
            $qStartMonth = intdiv($anchor->month - 1, 3) * 3 + 1; // 1,4,7,10
            $start = Carbon::create($anchor->year, $qStartMonth, 1)->startOfMonth();
            $end = (clone $start)->addMonths(2)->endOfMonth();
            // Labels: each day number across entire quarter (flatten) or months?
            // For consistency with monthly/day granularity, use day-of-month across total days
            // But that becomes large; prefer month abbrev + week number style.
            // Simplify: label each month: e.g., ['Jan','Feb','Mar'] and bucket per month index 0..2
            $labels = [
                Carbon::create($anchor->year, $qStartMonth, 1)->format('M'),
                Carbon::create($anchor->year, $qStartMonth + 1, 1)->format('M'),
                Carbon::create($anchor->year, $qStartMonth + 2, 1)->format('M'),
            ];
            $buckets = [0,1,2]; // month offset inside quarter
            return [$start, $end, $labels, $buckets];
        }
        if ($range === 'yearly') {
            $start = Carbon::create($anchor->year, 1, 1)->startOfYear();
            $end = (clone $start)->endOfYear();
            $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $buckets = range(1,12); // month number
            return [$start, $end, $labels, $buckets];
        }
        if ($range === 'monthly') {
            $start = (clone $anchor)->startOfMonth();
            $end = (clone $anchor)->endOfMonth();
            $days = (int) $start->daysInMonth;
            $labels = array_map(fn($d) => (string)$d, range(1, $days));
            $buckets = range(1, $days); // day of month
            return [$start, $end, $labels, $buckets];
        }
        // day (hourly) - Dynamic hours based on actual transaction data
        $start = (clone $anchor)->startOfDay();
        $end = (clone $anchor)->endOfDay();
        
        // Get actual hour range from transactions on this day, default to 10am-7pm
        [$hourStart, $hourEnd] = $this->getDynamicHourRange($start, $end, $brand);
        
        $labels = array_map(function ($h) {
            // Format 12-hour like "10 AM", "1 PM"
            $ampm = $h < 12 ? 'AM' : 'PM';
            $hour12 = $h % 12; if ($hour12 === 0) $hour12 = 12;
            return $hour12 . ' ' . $ampm;
        }, range($hourStart, $hourEnd));
        $buckets = range($hourStart, $hourEnd);
        return [$start, $end, $labels, $buckets];
    }

    /**
     * Get dynamic hour range for day view based on actual transaction data
     * Returns [startHour, endHour] with default fallback to business hours (10-19)
     */
    private function getDynamicHourRange(Carbon $start, Carbon $end, string $brand = 'all'): array
    {
        // Default business hours
        $defaultStart = 10; // 10 AM
        $defaultEnd = 19;   // 7 PM
        
        // Query to find actual transaction hours for this day
        $query = DB::table('transactions as t')
            ->whereBetween('t.sale_date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->selectRaw('MIN(HOUR(t.sale_date)) as min_hour, MAX(HOUR(t.sale_date)) as max_hour');
            
        // Apply brand filter if specified
        if ($brand && $brand !== 'all') {
            $query->join('transaction_items as ti', 't.transaction_id', '=', 'ti.transaction_id')
                  ->where('ti.product_brand', $brand);
        }
        
        $result = $query->first();
        
        // If no transactions found, use default business hours
        if (!$result || ($result->min_hour === null && $result->max_hour === null)) {
            return [$defaultStart, $defaultEnd];
        }
        
        $minHour = (int) $result->min_hour;
        $maxHour = (int) $result->max_hour;
        
        // Expand range to include default business hours if actual data is within them
        $finalStart = min($minHour, $defaultStart);
        $finalEnd = max($maxHour, $defaultEnd);
        
        // Ensure we have at least a few hours span and don't go beyond 24-hour format
        $finalStart = max(0, $finalStart);
        $finalEnd = min(23, $finalEnd);
        
        // Ensure end is after start
        if ($finalEnd <= $finalStart) {
            $finalEnd = $finalStart + 1;
        }
        
        return [$finalStart, $finalEnd];
    }

    /**
     * Sales revenue series by time bucket and sale_type (pos vs reservation)
     */
    private function buildSalesData(Carbon $start, Carbon $end, string $range, array $buckets, string $brand = 'all', bool $predictive = false): array
    {
        // Build base query using transaction_items for accurate brand-specific revenue calculation
        $expr = $this->bucketExpression('t.sale_date', $range);
        
        if ($brand && $brand !== 'all') {
            // For brand-specific queries, sum transaction_items.subtotal instead of full transaction amounts
            $query = DB::table('transaction_items as ti')
                ->join('transactions as t', 'ti.transaction_id', '=', 't.transaction_id')
                ->selectRaw("t.sale_type as sale_type, {$expr} as bucket, SUM(ti.subtotal) as revenue")
                ->whereBetween('t.sale_date', [$start, $end])
                ->where('ti.product_brand', $brand);
        } else {
            // For "all brands", use full transaction amounts
            $query = DB::table('transactions as t')
                ->selectRaw("t.sale_type as sale_type, {$expr} as bucket, SUM(t.total_amount) as revenue")
                ->whereBetween('t.sale_date', [$start, $end]);
        }
        
        $rows = $query->groupBy('sale_type', 'bucket')->get();

        // Initialize with zeros
        $pos = array_fill(0, count($buckets), 0.0);
        $reservation = array_fill(0, count($buckets), 0.0);

        foreach ($rows as $row) {
            $idx = $this->bucketIndex((int)$row->bucket, $range, $buckets);
            if ($idx === null) continue;
            $type = strtolower((string)$row->sale_type);
            if ($type === 'reservation') {
                $reservation[$idx] = (float)$row->revenue;
            } else { // default all else to pos
                $pos[$idx] = (float)$row->revenue;
            }
        }

        // Apply predictive adjustments if in predictive mode
        if ($predictive) {
            $pos = $this->applyPredictiveAdjustments($pos);
            $reservation = $this->applyPredictiveAdjustments($reservation);
        }

        return [
            'pos' => $pos,
            'reservation' => $reservation,
        ];
    }

    /**
     * Demand (units sold) by brand - returns total quantities per brand for horizontal bar chart
     */
    private function buildDemandData(Carbon $start, Carbon $end, string $range, array $buckets, string $brand = 'all', bool $predictive = false): array
    {
        // Get total quantity sold by brand for the entire time period
        $query = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
            ->selectRaw("COALESCE(ti.product_brand, 'Unknown') as brand, SUM(ti.quantity) as total_qty")
            ->whereBetween('t.sale_date', [$start, $end]);

        // Apply brand filter if specified
        if ($brand && $brand !== 'all') {
            $query->where('ti.product_brand', $brand);
        }
        
        $rows = $query->groupBy('ti.product_brand')
            ->orderBy('total_qty', 'desc')
            ->get();

        $brands = [];
        $quantities = [];

        foreach ($rows as $row) {
            $brands[] = $row->brand;
            $quantities[] = (int)$row->total_qty;
        }

        // Apply predictive adjustments if in predictive mode
        if ($predictive) {
            $quantities = $this->applyPredictiveAdjustments($quantities);
        }

        return [
            'brands' => $brands,
            'quantities' => $quantities,
        ];
    }

    /**
     * Normalize raw category text into one of: men, women, accessories
     */
    private function mapCategoryToGroup(string $raw): string
    {
        $c = trim(strtolower($raw));
        if ($c === '' || $c === 'other' || $c === 'others' || $c === 'unisex') {
            return 'accessories';
        }
        // women synonyms/patterns
        $womenPatterns = [
            'women', "women's", 'womens', 'woman', 'female', 'ladies', 'lady', 'girls', 'girl', 'wmn', 'wmns'
        ];
        foreach ($womenPatterns as $p) {
            if (str_contains($c, $p)) return 'women';
        }
        // men synonyms/patterns
        $menPatterns = [
            'men', "men's", 'mens', 'man', 'male', 'gents', 'gent', 'boys', 'boy', 'mns', 'mn'
        ];
        foreach ($menPatterns as $p) {
            if (str_contains($c, $p)) return 'men';
        }
        // accessories patterns
        $accPatterns = [
            'access', 'acc', 'accessory', 'accessories', 'cap', 'sock', 'bag', 'belt', 'lace', 'insoles'
        ];
        foreach ($accPatterns as $p) {
            if (str_contains($c, $p)) return 'accessories';
        }
        // default bucket
        return 'accessories';
    }

    /**
     * SQL expression for bucket depending on range
     */
    private function bucketExpression(string $column, string $range): string
    {
        return match ($range) {
            'weekly' => "(DAYOFWEEK({$column}) - 1)", // 0..6 (Sun..Sat)
            'monthly' => "DAY({$column})",             // 1..31
            'quarterly' => "(MONTH({$column}) - QUARTER({$column})*3 + 3)", // month offset inside quarter 1..3
            'yearly' => "MONTH({$column})",             // 1..12
            default => "HOUR({$column})",              // 0..23
        };
    }

    /**
     * Map bucket number to index in buckets array
     */
    private function bucketIndex(int $bucketValue, string $range, array $buckets): ?int
    {
        if ($range === 'weekly') {
            // expecting 0..6
            $pos = array_search($bucketValue, $buckets, true);
            return $pos === false ? null : $pos;
        }
        if ($range === 'monthly') {
            // 1..n mapped to index 0..n-1
            $pos = $bucketValue - 1;
            return isset($buckets[$pos]) ? $pos : null;
        }
        if ($range === 'quarterly') {
            // offset displayed 1..3 -> index 0..2
            $pos = $bucketValue - 1;
            return isset($buckets[$pos]) ? $pos : null;
        }
        if ($range === 'yearly') {
            // months 1..12 -> index 0..11
            $pos = $bucketValue - 1;
            return isset($buckets[$pos]) ? $pos : null;
        }
        // day: hour 10..19 -> index 0..9
        $pos = array_search($bucketValue, $buckets, true);
        return $pos === false ? null : $pos;
    }

    /**
     * Apply trend-based projections using actual historical patterns
     * This creates realistic forecasts based on real data trends
     */
    private function applyPredictiveAdjustments(array $data): array
    {
        if (count($data) < 2) {
            return $data; // Need at least 2 data points for trend analysis
        }
        
        $adjusted = [];
        
        // Calculate actual trend from the data
        $firstHalf = array_slice($data, 0, floor(count($data) / 2));
        $secondHalf = array_slice($data, floor(count($data) / 2));
        
        $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;
        
        // Calculate trend factor based on actual data progression
        $trendFactor = ($firstAvg > 0) ? ($secondAvg / $firstAvg) : 1.0;
        
        // Apply the calculated trend to project forward
        foreach ($data as $index => $value) {
            // Project based on position in array and calculated trend
            $position = ($index + 1) / count($data);
            $projected = $value * (1 + (($trendFactor - 1) * $position));
            
            // Round to whole numbers for quantities (items can't be fractional)
            $adjusted[] = max(round($projected), 0);
        }
        
        return $adjusted;
    }
}
