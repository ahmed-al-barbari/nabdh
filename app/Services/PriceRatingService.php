<?php

namespace App\Services;

class PriceRatingService {
    /**
    * حساب تقييم السعر
    *
    * @param float $merchantPrice سعر التاجر
    * @param array $recentPrices أسعار السوق الحديثة
    * @param string $comparison 'less' أو 'more' حسب اختيار المستخدم
    * @return array
    */

    public function calculatePriceRating( float $merchantPrice, array $recentPrices, string $comparison = 'less' ): array {
        if ( count( $recentPrices ) < 5 ) {
            return [
                'rating' => 'بلا تقييم',
                'message' => 'بيانات السوق غير كافية لتحديد السعر العادل.',
            ];
        }

        // فرز الأسعار وحساب الـ percentiles
        sort( $recentPrices );
        $q1 = $this->calculatePercentile( $recentPrices, 25 );
        $medianPrice = $this->calculatePercentile( $recentPrices, 50 );
        $q3 = $this->calculatePercentile( $recentPrices, 75 );
        $iqr = $q3 - $q1;

        // تحديد الحدود
        $lowerBound = max( 0, $q1 - 1.0 * $iqr );
        $upperBound = $q3 + 1.0 * $iqr;

        // تقييم السعر حسب الاختيار
        if ( $comparison === 'less' ) {
            if ( $merchantPrice <= $medianPrice ) {
                $rating = 'عادل';
            } elseif ( $merchantPrice <= $upperBound ) {
                $rating = 'جيد';
            } else {
                $rating = 'مرتفع';
            }
        } else {
            if ( $merchantPrice >= $medianPrice ) {
                $rating = 'عادل';
            } elseif ( $merchantPrice >= $lowerBound ) {
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
            'dataPoints' => count( $recentPrices ),
        ];
    }

    /**
    * حساب percentile (robust implementation with bounds checking)
    */

    private function calculatePercentile( array $sortedArray, int $percentile ): float {
        $count = count( $sortedArray );
        if ( $count === 0 ) {
            return 0;
        }
        
        if ( $count === 1 ) {
            return $sortedArray[0];
        }

        $index = ( $percentile / 100 ) * ( $count - 1 );
        $lowerIndex = (int)floor( $index );
        $upperIndex = (int)ceil( $index );
        
        // Ensure indices are within bounds (critical fix)
        $lowerIndex = max( 0, min( $lowerIndex, $count - 1 ) );
        $upperIndex = max( 0, min( $upperIndex, $count - 1 ) );
        
        $lower = $sortedArray[ $lowerIndex ];
        $upper = $sortedArray[ $upperIndex ];
        
        if ( $lowerIndex === $upperIndex ) {
            return $lower;
        }
        
        // Linear interpolation
        $weight = $index - $lowerIndex;
        return $lower + $weight * ( $upper - $lower );
    }
}
