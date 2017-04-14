<?php
namespace App\Service;

class Helper
{
    /**
     * Calculates the weighted average of two fractions
     */
    public static function getWeightedAverage($volumeA, $averageA, $volumeB, $averageB)
    {
        return ($volumeA * $averageA + $volumeB * $averageB) / ($volumeA + $volumeB);
    }
}
