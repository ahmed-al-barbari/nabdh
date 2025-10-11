<?php

namespace App\Services;

class PriceRatingService
{
    /**
     * حساب تقييم السعر
     *
     * @param float $merchantPrice سعر التاجر
     * @param array $recentPrices أسعار السوق الحديثة
     * @param string $comparison 'less' أو 'more' حسب اختيار المستخدم
     * @return array
     */
    public function calculatePriceRating(float $merchantPrice, array $recentPrices, string $comparison = 'less'): array
    {
        if (count($recentPrices) < 5) {
            return [
                'rating' => 'بلا تقييم',
                'message' => 'بيانات السوق غير كافية لتحديد السعر العادل.',
            ];
        }

        // فرز الأسعار وحساب الـ percentiles
        sort($recentPrices);
        $q1 = $this->calculatePercentile($recentPrices, 25);
        $medianPrice = $this->calculatePercentile($recentPrices, 50);
        $q3 = $this->calculatePercentile($recentPrices, 75);
        $iqr = $q3 - $q1;

        // تحديد الحدود
        $lowerBound = max(0, $q1 - 1.0 * $iqr);
        $upperBound = $q3 + 1.0 * $iqr;

        // تقييم السعر حسب الاختيار
        if ($comparison === 'less') {
            if ($merchantPrice <= $medianPrice) {
                $rating = 'عادل';
            } elseif ($merchantPrice <= $upperBound) {
                $rating = 'جيد';
            } else {
                $rating = 'مرتفع';
            }
        } else { // 'more'
            if ($merchantPrice >= $medianPrice) {
                $rating = 'عادل';
            } elseif ($merchantPrice >= $lowerBound) {
                $rating = 'جيد';
            } else {
                $rating = 'منخفض';
            }
        }

        return [
            'rating' => $rating,
            'merchantPrice' => $merchantPrice,
            'marketMedian' => $medianPrice,
            'marketLowerBound' => $lowerBound,
            'marketUpperBound' => $upperBound,
            'dataPoints' => count($recentPrices),
        ];
    }

    /**
     * حساب percentile
     */
    private function calculatePercentile(array $sortedArray, int $percentile): float
    {
        $index = ($percentile / 100) * (count($sortedArray) - 1);
        $lower = $sortedArray[floor($index)];
        $upper = $sortedArray[ceil($index)];
        return $lower + ($upper - $lower) * ($index - floor($index));
    }
}
